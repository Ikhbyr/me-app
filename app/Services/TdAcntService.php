<?php

namespace App\Services;

use App\Http\Resources\TdDetailResource;
use App\Http\Resources\TdMobileDetailResource;
use App\Models\AcntIntList;
use App\Models\TdAcnt;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class TdAcntService
{
    /**
     * Дансны жагсаалт дээр ирж байгаа датагаар данс үүсгэх
     *
     * @param  mixed $data
     * @return void
     */
    public function createAccountNes($data, $instid, $user)
    {
        $acnt = new TdAcnt();
        $acnt->instid = $instid;
        $acnt->userid = $user->userid;
        $acnt->statusid = 1;
        $acnt->created_at = Carbon::now();
        $acnt->created_by = $user->userid;
        $acnt->sys_no = $data['sysNo'] ?? null;
        $acnt->name = $data['acntName'] ?? null;
        $acnt->acnt_code = $data['acntCode'] ?? null;
        $acnt->is_secure = $data['isSecure'] ?? null;
        $acnt->cust_code = $data['custCode'] ?? null;
        $acnt->prod_code = $data['prodCode'] ?? null;
        $acnt->avail_bal = $data['availBalance'] ?? null;
        $acnt->current_bal = $data['balance'] ?? null;
        $acnt->is_allow_partial_liq = $data['isAllowPartialLiq'] ?? 0;
        $acnt->acnt_type = $data['acntType'] ?? null;
        $acnt->prod_name = $data['prodName'] ?? null;
        $acnt->cur_code = $data['curCode'] ?? null;
        $acnt->status = $data['status'] ?? null;
        $acnt->save();
    }

    /**
     * Дансны дэлгэрэнгүй polaris дата-р шинэчлэх
     *
     * @param  mixed $data
     * @return void
     */
    public function updateAcntNesData($data, $instid)
    {
        $acnt = TdAcnt::where('acnt_code', $data['acntCode'])->where('instid', $instid)->first();
        if (empty($acnt)) {
            return;
        }
        $acnt->name = $data['name'] ?? null;
        $acnt->name2 = $data['name2'] ?? null;
        $acnt->company_code = $data['companyCode'] ?? null;
        $acnt->prod_code = $data['prodCode'] ?? null;
        $acnt->brch_code = $data['brchCode'] ?? null;
        $acnt->cur_code = $data['curCode'] ?? null;
        $acnt->joint_or_single = $data['jointOrSingle'] ?? null;
        $acnt->cust_code = $data['custCode'] ?? null;
        $acnt->status_sys = $data['statusSys'] ?? null;
        $acnt->status_custom = $data['statusCustom'] ?? null;
        $acnt->status_date = formatDate($data['statusDate'] ?? null);
        $acnt->seg_code = $data['segCode'] ?? null;
        $acnt->open_date_org = formatDate($data['openDateOrg'] ?? null);
        $acnt->start_date = formatDate($data['startDate'] ?? null);
        $acnt->maturity_date = formatDate($data['maturityDate'] ?? null);
        $acnt->tenor = $data['tenor'] ?? null;
        $acnt->is_corp_acnt = $data['isCorpAcnt'] ?? null;
        $acnt->last_dt_date = formatDate($data['lastDtDate'] ?? null);
        $acnt->last_ct_date = formatDate($data['lastCtDate'] ?? null);
        $acnt->last_seq_txn = $data['lastSeqTxn'] ?? null;
        $acnt->casa_acnt_code = $data['casaAcntCode'] ?? null;
        // $acnt->acnt_version = $data['acntVersion'] ?? null;
        $acnt->cap_method = $data['capMethod'] ?? null;
        $acnt->rcv_acnt_code = $data['rcvAcntCode'] ?? null;
        $acnt->slevel = $data['slevel'] ?? null;
        $acnt->closed_by = $data['closedBy'] ?? null;
        $acnt->closed_date = formatDate($data['closedDate'] ?? null);
        $acnt->closed_cond = $data['closedCond'] ?? null;
        $acnt->term_len = $data['termLen'] ?? null;
        $acnt->class_no = $data['classNo'] ?? null;
        $acnt->maturity_option = $data['maturityOption'] ?? null;
        $acnt->flag_no_tb = $data['flagNoTb'] ?? null;
        $acnt->daily_basis_code = $data['dailyBasisCode'] ?? null;
        $acnt->prod_name = $data['prodName'] ?? null;
        $acnt->brch_name = $data['brchName'] ?? null;
        $acnt->cust_name = $data['custName'] ?? null;
        $acnt->cust_type = $data['custType'] ?? null;
        $acnt->status_sys_name = $data['statusSysName'] ?? null;
        $acnt->current_bal = $data['currentBal'] ?? null;
        $acnt->avail_bal = $data['availBal'] ?? null;
        $acnt->block_bal = $data['blockBal'] ?? null;
        $acnt->acrint_bal = $data['acrintBal'] ?? null;
        $acnt->cap_int = $data['capInt'] ?? null;
        $acnt->cap_int2 = $data['capInt2'] ?? null;
        $acnt->cap_method_name = $data['capMethodName'] ?? null;
        $acnt->rcv_acnt_name = $data['rcvAcntName'] ?? null;
        $acnt->seg_name = $data['segName'] ?? null;
        $acnt->is_corp_name = $data['isCorpName'] ?? null;
        $acnt->joint_or_single_name = $data['jointOrSingleName'] ?? null;
        $acnt->closed_by_name = $data['closedByName'] ?? null;
        $acnt->term_basis = $data['termBasis'] ?? null;
        $acnt->passbook_facility = $data['passbookFacility'] ?? null;
        $acnt->class_name = $data['className'] ?? null;
        $acnt->maturity_option_name = $data['maturityOptionName'] ?? null;
        $acnt->read_name = $data['readName'] ?? null;
        $acnt->read_bal = $data['readBal'] ?? null;
        $acnt->read_tran = $data['readTran'] ?? null;
        $acnt->do_tran = $data['doTran'] ?? null;
        $acnt->is_secure = $data['isSecure'] ?? null;
        $acnt->last_tb_date = formatDate($data['lastTbDate'] ?? null);
        $acnt->flag_no_tb_name = $data['flagNoTbName'] ?? null;
        $acnt->cat_code = $data['catCode'] ?? null;
        $acnt->cat_sub_code = $data['catSubCode'] ?? null;
        $acnt->cat_sub_name = $data['catSubName'] ?? null;
        $acnt->cat_name = $data['catName'] ?? null;
        $acnt->save();
        if (empty($data['acntIntList'])) {
            $intList = [];
        } else {
            $intList = $data['acntIntList'];
        }
        $this->createAcntIntList($data['acntCode'], $intList, $instid, $acnt->userid);
    }

    public function createAcntIntList($acntCode, $data, $instid, $userid)
    {
        AcntIntList::where('userid', $userid)->where('instid', $instid)->where('acnt_code', $acntCode)->delete();
        if (empty($data)) {
            return;
        }
        for ($i = 0; $i < count($data); $i++) {
            $elem = $data[$i];
            $acnt = new AcntIntList();
            $acnt->acnt_code = $acntCode;
            $acnt->instid = $instid;
            $acnt->userid = $userid;
            $acnt->statusid = 1;
            $acnt->created_at = Carbon::now();
            $acnt->created_by = $userid;
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

    public function detailAcntData($acnt_code, $instid, $isBackOff = false)
    {
        if ($isBackOff) {
            return new TdDetailResource(TdAcnt::where('acnt_code', $acnt_code)->where('instid', $instid)->first());
        }
        return new TdMobileDetailResource(TdAcnt::where('acnt_code', $acnt_code)->where('instid', $instid)->first());
    }
}
