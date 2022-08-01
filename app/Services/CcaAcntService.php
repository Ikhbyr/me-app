<?php

namespace App\Services;

use App\Http\Resources\CcaDetailResource;
use App\Http\Resources\CcaMobileDetailResource;
use App\Models\AcntIntList;
use App\Models\CcaAcnt;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class CcaAcntService
{
    /**
     * Дансны жагсаалт дээр ирж байгаа датагаар данс үүсгэх
     *
     * @param  mixed $data
     * @return void
     */
    public function createAccountNes($data, $instid, $user)
    {
        $acnt = new CcaAcnt();
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
        $acnt->acnt_type = $data['acntType'] ?? null;
        $acnt->prod_code_name = $data['prodName'] ?? null;
        $acnt->cur_code = $data['curCode'] ?? null;
        $acnt->avail_balance = $data['availBalance'] ?? null;
        $acnt->total_exp_amount = $data['balance'] ?? null;
        $acnt->status_sys = $data['status'] ?? null;
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
        $acnt = CcaAcnt::where('acnt_code', $data['acntCode'])->where('instid', $instid)->first();
        if (empty($acnt)) {
            return;
        }

        $acnt->acnt_code = $data['acntCode'] ?? null;
        $acnt->name = $data['acntCodeName'] ?? null;
        $acnt->name2 = $data['name2'] ?? null;
        $acnt->last_liquidate_date = formatDate($data['lastLiquidateDate'] ?? null);
        $acnt->end_date = formatDate($data['endDate'] ?? null);
        $acnt->grace_days = $data['graceDays'] ?? null;
        $acnt->due_date = formatDate($data['dueDate'] ?? null);
        $acnt->block_amount_purch = $data['blockAmountPurch'] ?? null;
        $acnt->statement_date = formatDate($data['statementDate'] ?? null);
        $acnt->status_name = $data['statusName'] ?? null;
        $acnt->min_pay_amt = $data['minPayAmt'] ?? null;
        $acnt->get_with_secure = $data['getWithSecure'] ?? null;
        $acnt->actual_start_date = formatDate($data['actualStartDate'] ?? null);
        $acnt->avail_balance = $data['availBalance'] ?? null;
        $acnt->block_amount_cash = $data['blockAmountCash'] ?? null;
        $acnt->brch_code = $data['brchCode'] ?? null;
        $acnt->brch_name = $data['brchName'] ?? null;
        $acnt->brch_name2 = $data['brchName2'] ?? null;
        $acnt->cash_limit = $data['cashLimit'] ?? null;
        $acnt->class_name = $data['className'] ?? null;
        $acnt->class_name2 = $data['className2'] ?? null;
        $acnt->cycle_no = $data['cycleNo'] ?? null;
        $acnt->company_code = $data['companyCode'] ?? null;
        $acnt->cur_code = $data['curCode'] ?? null;
        $acnt->cust_code = $data['custCode'] ?? null;
        $acnt->class_no = $data['classNo'] ?? null;
        $acnt->daily_basis_code = $data['dailyBasisCode'] ?? null;
        $acnt->description = $data['description'] ?? null;
        $acnt->exp_cash_amount = $data['expCashAmount'] ?? null;
        $acnt->exp_interest_amount = $data['expInterestAmount'] ?? null;
        $acnt->exp_purchase_amount = $data['expPurchaseAmount'] ?? null;
        $acnt->is_secure = $data['isSecure'] ?? null;
        $acnt->prod_code = $data['prodCode'] ?? null;
        $acnt->exp_transfer_amount = $data['expTransferAmount'] ?? null;
        $acnt->is_not_auto_class = $data['isNotAutoClass'] ?? null;
        $acnt->last_exp_date = formatDate($data['lastExpDate'] ?? null);
        $acnt->last_txn_date = formatDate($data['lastTxnDate'] ?? null);
        $acnt->od_fee = $data['odFee'] ?? null;
        $acnt->ol_fee = $data['olFee'] ?? null;
        $acnt->other_fee = $data['otherFee'] ?? null;
        $acnt->over_limit_amt = $data['overLimitAmt'] ?? null;
        $acnt->over_limit_percent = $data['overLimitPercent'] ?? null;
        $acnt->prod_code_name = $data['prodCodeName'] ?? null;
        $acnt->prod_code_name2 = $data['prodCodeName2'] ?? null;
        $acnt->repayment_acnt = $data['repaymentAcnt'] ?? null;
        $acnt->repayment_mode = $data['repaymentMode'] ?? null;
        $acnt->repayment_mode_name = $data['repaymentModeName'] ?? null;
        $acnt->repayment_mode_name2 = $data['repaymentModeName2'] ?? null;
        $acnt->repayment_type = $data['repaymentType'] ?? null;
        $acnt->repayment_type_name = $data['repaymentTypeName'] ?? null;
        $acnt->repayment_type_name2 = $data['repaymentTypeName2'] ?? null;
        $acnt->seg_code = $data['segCode'] ?? null;
        $acnt->start_date = formatDate($data['startDate'] ?? null);
        $acnt->status_id = $data['statusId'] ?? null;
        $acnt->status_id_name = $data['statusIdName'] ?? null;
        $acnt->status_id_name2 = $data['statusIdName2'] ?? null;
        $acnt->status_name2 = $data['statusName2'] ?? null;
        $acnt->status_sys = $data['statusSys'] ?? null;
        $acnt->total_exp_amount = $data['totalExpAmount'] ?? null;
        $acnt->total_limit = $data['totalLimit'] ?? null;
        $acnt->save();
        if (empty($data['acntIntInfos'])) {
            $intList = [];
        } else {
            $intList = $data['acntIntInfos'];
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
            $acnt->int_rate_option = $elem['intRateOption'] ?? null;
            $acnt->int_rate = $elem['intRate'] ?? null;
            $acnt->int_lvl = $elem['intLvl'] ?? null;
            $acnt->int_lvl_name = $elem['intLvlName'] ?? null;
            $acnt->int_type_code = $elem['intTypeCode'] ?? null;
            $acnt->save();
        }
    }

    public function detailAcntData($acnt_code, $instid, $isBackOff = false)
    {
        if ($isBackOff) {
            return new CcaDetailResource(CcaAcnt::where('acnt_code', $acnt_code)->where('instid', $instid)->first());
        }
        return new CcaMobileDetailResource(CcaAcnt::where('acnt_code', $acnt_code)->where('instid', $instid)->first());
    }
}
