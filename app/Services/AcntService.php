<?php

namespace App\Services;

use App\Http\Resources\AccountIntCollection;
use App\Http\Resources\AccountStatementCollection;
use App\Http\Resources\CasaCollection;
use App\Http\Resources\CcaCollection;
use App\Http\Resources\LoanCollection;
use App\Http\Resources\RepaymentScheduleCollection;
use App\Http\Resources\TdCollection;
use App\Models\AccountStatement;
use App\Models\AcntIntList;
use App\Models\CasaAcnt;
use App\Models\CcaAcnt;
use App\Models\DicMain;
use App\Models\InstCustConn;
use App\Models\LoanAcnt;
use App\Models\RepaymentSchedule;
use App\Models\TdAcnt;
use Carbon\Carbon;

class AcntService
{
    private $acntTypes = [];

    public function calcDiffDays($date1, $date2)
    {
        $diff = abs(strtotime($date2) - strtotime($date1));
        return floor($diff / (24 * 60 * 60));
    }

    public function getAcntTypes()
    {
        $parent = DicMain::where('maintype', 'NES_ACNT_TYPE')->where('status', 1)->first();
        if ($parent) {
            return DicMain::where('status', 1)->where('parentid', $parent->id)->get();
        } else {
            return [];
        }
    }

    public function getAcntTypeTable($acntType, $acntTypes)
    {
        foreach ($acntTypes as $key => $value) {
            if ($value->value == $acntType) {
                return $value->maintype;
            }
        }
    }

    public function checkDates($date1, $date2, $day = 90)
    {
        $diff = $this->calcDiffDays($date1, $date2);
        if ($diff > $day) {
            return [
                'status' => 500,
                'data' => "Таны сонгосон огноо ${day} хоног дотор байх ёстой. ${day}/${diff}"
            ];
        }
        return [
            'status' => 200,
            'data' => "Success"
        ];
    }

    public function getRepaymentSchedule($acnt, $instid)
    {
        $polaris = new PolarisApiRequestService($instid);
        $respdata = $polaris->sendRequest(13610203, [$acnt], $instid);
        if ($respdata['status'] == 200) {
            $this->createRepaymentSchedule($respdata['data'], $instid, $acnt);
        } else {
            $respdata['data'] = $this->getRepaymentSchedules($acnt, $instid);
        }
        return $respdata;
    }

    public function createRepaymentSchedule($datas, $instid, $acnt_code)
    {
        RepaymentSchedule::where('instid', $instid)->where('acnt_code', $acnt_code)->delete();
        foreach ($datas as $data) {
            $schdl = new RepaymentSchedule();
            $schdl->instid = $instid;
            $schdl->acnt_code = $acnt_code;
            $schdl->schd_date = formatDate($data['schdDate'] ?? null);
            $schdl->amount = $data['amount'] ?? null;
            $schdl->int_amount = $data['intAmount'] ?? null;
            $schdl->total_amount = $data['totalAmount'] ?? null;
            $schdl->theor_bal = $data['theorBal'] ?? null;
            $schdl->save();
        }
    }

    public function getRepaymentSchedules($acnt_code, $instid)
    {
        return new RepaymentScheduleCollection(RepaymentSchedule::where('instid', $instid)->where('acnt_code', $acnt_code)->get());
    }

    public function getAccountStatement($data, $instid)
    {
        $polaris = new PolarisApiRequestService($instid);
        $respdata = $polaris->sendRequest(13610302, [$data], $instid);
        if ($respdata['status'] == 200) {
            $respdata['data'] = $respdata['data']['txns'] ?? [];
            $this->createAcntStatement($respdata['data'], $instid, $data['acntCode']);
        }
        $respdata['data'] = $this->getStatements($data, $instid);
        return $respdata;
    }

    public function createAcntStatement($datas, $instid, $acnt_code)
    {
        foreach ($datas as $data) {
            $stmnt = AccountStatement::where('jrno', $data['jrno'])->where('instid', $instid)->where('acnt_code', $acnt_code)->first();
            if (empty($stmnt)) {
                $stmnt = new AccountStatement();
                $stmnt->instid = $instid;
                $stmnt->acnt_code = $acnt_code;
            }
            $stmnt->cont_cur_rate = $data['contCurRate'] ?? null;
            $stmnt->income = $data['income'] ?? null;
            $stmnt->jrno = $data['jrno'] ?? null;
            $stmnt->begin_bal = $data['beginBal'] ?? null;
            $stmnt->end_bal = $data['endBal'] ?? null;
            $stmnt->txn_date = str_replace('.', '-', $data['txnDate']);
            $stmnt->txn_code = $data['txnCode'] ?? null;
            $stmnt->bal_type_code = $data['balTypeCode'] ?? null;
            $stmnt->outcome = $data['outcome'] ?? null;
            $stmnt->balance = $data['balance'] ?? null;
            $stmnt->txn_desc = $data['txnDesc'] ?? null;
            $stmnt->cont_acnt_code = $data['contAcntCode'] ?? null;
            $stmnt->cont_bank_acnt_code = $data['contBankAcntCode'] ?? null;
            $stmnt->cont_bank_acnt_name = $data['contBankAcntName'] ?? null;
            $stmnt->cont_bank_code = $data['contBankCode'] ?? null;
            $stmnt->cont_bank_name = $data['contBankName'] ?? null;
            $stmnt->post_date = formatDate($data['postDate'] ?? null);
            $stmnt->save();
        }
    }

    public function getStatements($data, $instid)
    {
        $from = date($data['startDate']);
        $to = date($data['endDate']);
        return new AccountStatementCollection(
            AccountStatement::where('acnt_code', $data['acntCode'])
                ->where('instid', $instid)
                ->whereBetween('txn_date', [$from, $to])
                ->skip($data['startPosition'])->take($data['count'])->get()
        );
    }


    /**
     * getAccounts - inst дээр бүртгэлтэй данс авах
     *
     * @param  mixed $user
     * @param  mixed $instid
     * @return void
     */
    public function getAccounts($user, $instid, $isall = false)
    {
        if (empty($user)) {
            $user = auth()->user();
        }
        $acnts = [];
        $tdsql = TdAcnt::with('inst:id,instname,instnameeng,logo,color')->where('instid', $instid)
            ->where('userid', $user->userid)->where('statusid', 1);
        $casasql = CasaAcnt::with('inst:id,instname,instnameeng,logo,color')->where('instid', $instid)
            ->where('userid', $user->userid)->where('statusid', 1);
        $lnsql = LoanAcnt::with('inst:id,instname,instnameeng,logo,color')->where('instid', $instid)
            ->where('userid', $user->userid)->where('statusid', 1);
        $ccasql = CcaAcnt::with('inst:id,instname,instnameeng,logo,color')->where('instid', $instid)
            ->where('userid', $user->userid)->where('statusid', 1);
        if (!$isall) {
            $tdsql = $tdsql->whereIn('status', ['O', 'N']);
            $casasql = $casasql->whereIn('status', ['O', 'N']);
            $lnsql = $lnsql->whereIn('status', ['O', 'N']);
            $ccasql = $ccasql->whereIn('status_sys', ['O', 'N']);
        }
        $casa = new CasaCollection($casasql->get());
        $td = new TdCollection($tdsql->get());
        $ln = new LoanCollection($lnsql->get());
        $cca = new CcaCollection($ccasql->get());
        foreach ($casa as $item) {
            $acnts[] = $item;
        }
        foreach ($td as $item) {
            $acnts[] = $item;
        }
        foreach ($ln as $item) {
            $acnts[] = $item;
        }
        foreach ($cca as $item) {
            $acnts[] = $item;
        }
        return $acnts;
    }

    /**
     * getAllAccounts - хэрэглэгчийн inst үл хамааран бүх данс авах
     *
     * @param  mixed $user
     * @return void
     */

    public function getAllAccounts($user, $isall = false)
    {
        if (empty($user)) {
            $user = auth()->user();
        }
        $acnts = [];
        $tdsql = TdAcnt::with('inst:id,instname,instnameeng,logo,color')
            ->where('userid', $user->userid)->where('statusid', 1);
        $casasql = CasaAcnt::with('inst:id,instname,instnameeng,logo,color')
            ->where('userid', $user->userid)->where('statusid', 1);
        $lnsql = LoanAcnt::with('inst:id,instname,instnameeng,logo,color')
            ->where('userid', $user->userid)->where('statusid', 1);
        $ccasql = CcaAcnt::with('inst:id,instname,instnameeng,logo,color')
            ->where('userid', $user->userid)->where('statusid', 1);
        if (!$isall) {
            $tdsql = $tdsql->whereIn('status', ['O', 'N']);
            $casasql = $casasql->whereIn('status', ['O', 'N']);
            $lnsql = $lnsql->whereIn('status', ['O', 'N']);
            $ccasql = $ccasql->whereIn('status_sys', ['O', 'N']);
        }
        $casa = new CasaCollection($casasql->get());
        $td = new TdCollection($tdsql->get());
        $ln = new LoanCollection($lnsql->get());
        $cca = new CcaCollection($ccasql->get());
        foreach ($casa as $item) {
            $acnts[] = $item;
        }
        foreach ($td as $item) {
            $acnts[] = $item;
        }
        foreach ($ln as $item) {
            $acnts[] = $item;
        }
        foreach ($cca as $item) {
            $acnts[] = $item;
        }
        return $acnts;
    }

    public function createCasaAcntList($datas = [], $user = [], $instid = "")
    {
        if (empty($user)) {
            $user = auth()->user();
        }
        CasaAcnt::where('userid', $user->userid)->where('instid', $instid)->where('statusid', 1)->delete();
        TdAcnt::where('userid', $user->userid)->where('instid', $instid)->where('statusid', 1)->delete();
        LoanAcnt::where('userid', $user->userid)->where('instid', $instid)->where('statusid', 1)->delete();
        CcaAcnt::where('userid', $user->userid)->where('instid', $instid)->where('statusid', 1)->delete();
        $acnts = [];
        $acntTypes = $this->getAcntTypes();
        foreach ($datas as $data) {
            if ($data['sysNo'] != 1312) {
                $type = $this->getAcntTypeTable($data['acntType'], $acntTypes);
                if ($type == 'CASA_ACNT') {
                    $acnts[] = $data;
                    $casaService = new CasaAcntService();
                    $casaService->createAccountNes($data, $instid, $user);
                } else if ($type == 'TD_ACNT') {
                    $acnts[] = $data;
                    $tdService = new TdAcntService();
                    $tdService->createAccountNes($data, $instid, $user);
                } else if (
                    $type == 'LOAN_ACNT'
                ) {
                    $acnts[] = $data;
                    $lnService = new LoanAcntService();
                    $lnService->createAccountNes($data, $instid, $user);
                } else if ($type == 'CCA_ACNT') {
                    $acnts[] = $data;
                    $ccaService = new CcaAcntService();
                    $ccaService->createAccountNes($data, $instid, $user);
                }
            }
        }

        return $acnts;
    }

    public function getCasaAccountDetail($acnt, $instid)
    {
        // $type CA, SA аль нэг нь байхад болно.
        return $this->getAccountDetail($acnt, 'CA', $instid);
    }

    public function getLoanAccountDetail($acnt, $instid)
    {
        return $this->getAccountDetail($acnt, 'LOAN', $instid);
    }

    public function getTdAccountDetail($acnt, $instid)
    {
        return $this->getAccountDetail($acnt, 'TD', $instid);
    }

    public function getCreditAccountDetail($acnt, $instid)
    {
        return $this->getAccountDetail($acnt, 'CCA', $instid);
    }

    /**
     * getAccountDetail - Дансны дэлгэрэнгүй
     *
     * @param  mixed $acnt - Дансны дугаар
     * @param  mixed $type - Дансны төрөл
     * @param  mixed $instid - Байгууллагын дугаар
     * @param  mixed $isBackOff - Backoffice системээс хандаж байгаа эсэх
     * @return void
     */
    public function getAccountDetail($acnt, $type, $instid, $isBackOff = false)
    {
        $user = auth()->user();
        if ($instid == 0) {
            $instid = $user->instid;
        }
        $operation = 0;
        if (!$isBackOff) {
            $connInst = InstCustConn::where('INST_ID', $instid)->where('cust_user_userid', $user->userid)->where('statusid', 1)->first();
            if (!$connInst) {
                return getSystemResp('Тухайн байгууллагад бүртгэлгүй байна.', 500);
            }
        }
        $this->acntTypes = $this->getAcntTypes();
        $acntType = $this->getAcntTypeTable($type, $this->acntTypes);
        switch ($acntType) {
            case 'LOAN_ACNT':
                $account = LoanAcnt::where('acnt_code', $acnt)->where('instid', $instid);
                if (!$isBackOff) {
                    $account = $account->where('userid', $user->userid);
                }
                $account = $account->first();
                if (empty($account)) {
                    return getSystemResp($acnt . ' данс бүртгэлгүй байна.', 500);
                }
                $operation = 13610200;
                break;
            case 'CASA_ACNT':
                $account = CasaAcnt::where('acnt_code', $acnt)->where('instid', $instid);
                if (!$isBackOff) {
                    $account = $account->where('userid', $user->userid);
                }
                $account = $account->first();
                if (empty($account)) {
                    return getSystemResp($acnt . ' данс бүртгэлгүй байна.', 500);
                }
                $operation = 13610000;
                break;
            case 'TD_ACNT':
                $account = TdAcnt::where('acnt_code', $acnt)->where('instid', $instid);
                if (!$isBackOff) {
                    $account = $account->where('userid', $user->userid);
                }
                $account = $account->first();
                if (empty($account)) {
                    return getSystemResp($acnt . ' данс бүртгэлгүй байна.', 500);
                }
                $operation = 13610100;
                break;
            case 'CCA_ACNT':
                $account = CcaAcnt::where('acnt_code', $acnt)->where('instid', $instid);
                if (!$isBackOff) {
                    $account = $account->where('userid', $user->userid);
                }
                $account = $account->first();
                if (empty($account)) {
                    return getSystemResp($acnt . ' данс бүртгэлгүй байна.', 500);
                }
                $operation = 13610400;
                break;

            default:
                # code...
                break;
        }

        $respdata = $this->getAccountDetailPolaris($operation, $acnt, $instid, $type, $isBackOff);
        $respdata['data'] = $this->getInterAccountDetail($acnt, $instid, $type, $isBackOff);
        $respdata['status'] = 200;
        return $respdata;
    }

    /**
     * getAccountDetailPolaris
     *
     * @return void
     */
    public function getAccountDetailPolaris($operation, $acnt, $instid, $type, $isBackOff = false)
    {
        $polaris = new PolarisApiRequestService($instid);
        /**
         *  $acnt - [Дансны дугаар, Нууцлалтай авах эсэх]
         * */
        $respdata = $polaris->sendRequest($operation, [$acnt, 0], $instid);
        if ($respdata['status'] == 200) {
            $this->updateAcntInfo($respdata['data'], $instid, $type);
        }
        return $respdata;
    }

    public function getInterAccountDetail($acnt_code, $instid, $type, $isBackOff)
    {
        $acntType = $this->getAcntTypeTable($type, $this->acntTypes);
        $dtData = [];
        switch ($acntType) {
            case 'LOAN_ACNT':
                $loanAcnt = new LoanAcntService();
                $dtData = $loanAcnt->detailAcntData($acnt_code, $instid, $isBackOff);
                break;
            case 'CASA_ACNT':
                $casaAcnt = new CasaAcntService();
                $dtData = $casaAcnt->detailAcntData($acnt_code, $instid, $isBackOff);
                break;
            case 'TD_ACNT':
                $tdAcnt = new TdAcntService();
                $dtData = $tdAcnt->detailAcntData($acnt_code, $instid, $isBackOff);
                break;
            case 'CCA_ACNT':
                $ccaAcnt = new CcaAcntService();
                $dtData = $ccaAcnt->detailAcntData($acnt_code, $instid, $isBackOff);
                break;

            default:
                # code...
                break;
        }
        return $dtData;
    }

    /**
     * updateAcntInfo - Дансны мэдээлэл засварлах
     *
     * @param  mixed $data - Дансны мэдээлэл
     * @param  mixed $instid - Байгууллагын дугаар
     * @param  mixed $type - Дансны төрөл
     * @return void
     */
    public function updateAcntInfo($data, $instid, $type)
    {
        $acntType = $this->getAcntTypeTable($type, $this->acntTypes);
        switch ($acntType) {
            case 'LOAN_ACNT':
                $loanAcnt = new LoanAcntService();
                $loanAcnt->updateAcntNesData($data, $instid);
                break;
            case 'CASA_ACNT':
                $casaAcnt = new CasaAcntService();
                $casaAcnt->updateAcntNesData($data, $instid);
                break;
            case 'TD_ACNT':
                $tdAcnt = new TdAcntService();
                $tdAcnt->updateAcntNesData($data, $instid);
                break;
            case 'CCA_ACNT':
                $ccaAcnt = new CcaAcntService();
                $ccaAcnt->updateAcntNesData($data, $instid);
                break;

            default:
                # code...
                break;
        }
    }

    /**
     * Системийн дансны зээлийн мэдээллийг шинэчлэх
     *
     * @param  mixed $acntCode
     * @param  mixed $instid
     * @return void
     */
    public function updateAcntIntInfo($data, $acntCode, $instid)
    {
        AcntIntList::where('instid', $instid)->where('acnt_code', $acntCode)->delete();
        if (empty($data)) {
            return;
        }
        $user = auth()->user();
        for ($i = 0; $i < count($data); $i++) {
            $elem = $data[$i];
            $acnt = new AcntIntList();
            $acnt->acnt_code = $acntCode;
            $acnt->instid = $instid;
            $acnt->userid = $user->userid;
            $acnt->statusid = 1;
            $acnt->created_at = Carbon::now();
            $acnt->created_by = $user->userid;
            $acnt->other_info = $elem['otherInfo'] ?? null;
            $acnt->pay_cust_name = $elem['payCustName'] ?? null;
            $acnt->int_rate = $elem['intRate'] ?? null;
            $acnt->source_bal_type = $elem['sourceBalType'] ?? null;
            $acnt->last_acr_info = $elem['lastAcrInfo'] ?? null;
            $acnt->type = $elem['type'] ?? null;
            $acnt->accr_int_amt = $elem['accrIntAmt'] ?? null;
            $acnt->int_type_name = $elem['intTypeName'] ?? null;
            $acnt->int_rate_option = $elem['intRateOption'] ?? null;
            $acnt->daily_int_amt = $elem['dailyIntAmt'] ?? null;
            $acnt->last_acr_txn_seq = $elem['lastAcrTxnSeq'] ?? null;
            $acnt->bal_type_code = $elem['balTypeCode'] ?? null;
            $acnt->int_type_code = $elem['intTypeCode'] ?? null;
            $acnt->last_acr_amt = $elem['lastAcrAmt'] ?? null;
            $acnt->last_accrual_date = formatDate($elem['lastAccrualDate'] ?? null);
            $acnt->save();
        }
    }

    /**
     * Системийн дансны зээлийн мэдээллийг авах
     *
     * @param  mixed $acnt
     * @param  mixed $instid
     * @return void
     */
    public function getInterAccountIntDetail($acntCode, $instid)
    {
        $intlist = AcntIntList::where('instid', $instid)->where('acnt_code', $acntCode)->get();
        return new AccountIntCollection($intlist);
    }

    public function getAccountInt($acnt, $instid)
    {
        $polaris = new PolarisApiRequestService($instid);
        $respdata = $polaris->sendRequest(13619995, [$acnt]);

        if ($respdata['status'] == 200) {
            $this->updateAcntIntInfo($respdata['data'], $acnt, $instid);
        }
        $respdata['data'] = $this->getInterAccountIntDetail($acnt, $instid);
        $respdata['status'] = 200;
        return $respdata;
    }

    /**
     * Барьцаа хөрөнгийн дансны дэлгэрэнгүй (Холбосон дансаар)
     *
     * @param  mixed $acnt Хадгаламжын дансны дугаар
     * @param  mixed $instid
     * @return void
     */
    public function getTdCollInfo($acnt, $instid)
    {
        $tdAcnt = TdAcnt::where('acnt_code', $acnt)->where('userid', auth()->user()->userid)->first();
        if ($tdAcnt) {
            $polaris = new PolarisApiRequestService($instid);
            /**
             *  $acnt - [Дансны дугаар, Нууцлалтай авах эсэх]
             * */
            $respdata = $polaris->sendRequest(13610907, [1306, $acnt], $instid);
            return $respdata;
        } else {
            return getSystemResp('Хадгаламжийн данс олдсонгүй.', 404);
        }
    }
}
