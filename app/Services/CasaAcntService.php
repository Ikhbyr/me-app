<?php

namespace App\Services;

use App\Http\Resources\CasaDetailResource;
use App\Http\Resources\CasaMobileDetailResource;
use App\Models\CasaAcnt;
use Carbon\Carbon;

class CasaAcntService
{
    /**
     * Дансны жагсаалт дээр ирж байгаа датагаар данс үүсгэх
     *
     * @param  mixed $data
     * @return void
     */
    public function createAccountNes($data, $instid, $user)
    {
        $acnt = new CasaAcnt();
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
        $acnt->is_allow_partial_liq = $data['isAllowPartialLiq'] ?? null;
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
    public function updateAcntNesData($data, $isntid)
    {
        $acnt = CasaAcnt::where('acnt_code', $data['acntCode'])->where('instid', $isntid)->first();
        $acnt->name = $data['name'] ?? null;
        $acnt->name2 = $data['name2'] ?? null;
        $acnt->cust_name = $data['custName'] ?? null;
        $acnt->company_code = $data['companyCode'] ?? null;
        $acnt->dormancy_date = formatDate($data['dormancyDate'] ?? null);
        $acnt->prod_code = $data['prodCode'] ?? null;
        $acnt->prod_name = $data['prodName'] ?? null;
        $acnt->brch_code = $data['brchCode'] ?? null;
        $acnt->brch_name = $data['brchName'] ?? null;
        $acnt->cur_code = $data['curCode'] ?? null;
        $acnt->status_custom = $data['statusCustom'] ?? null;
        $acnt->joint_or_single = $data['jointOrSingle'] ?? null;
        $acnt->status_date = formatDate($data['statusDate'] ?? null);
        $acnt->status_sys = $data['statusSys'] ?? null;
        $acnt->status_sys_name = $data['statusSysName'] ?? null;
        $acnt->cust_code = $data['custCode'] ?? null;
        $acnt->seg_code = $data['segCode'] ?? null;
        $acnt->seg_name = $data['segName'] ?? null;
        $acnt->acnt_type = $data['acntType'] ?? null;
        $acnt->acnt_type_name = $data['acntTypeName'] ?? null;
        $acnt->flag_stopped = $data['flagStopped'] ?? null;
        $acnt->flag_dormant = $data['flagDormant'] ?? null;
        $acnt->flag_stopped_int = $data['flagStoppedInt'] ?? null;
        $acnt->flag_stopped_payment = $data['flagStoppedPayment'] ?? null;
        $acnt->flag_frozen = $data['flagFrozen'] ?? null;
        $acnt->flag_no_credit = $data['flagNoCredit'] ?? null;
        $acnt->flag_no_debit = $data['flagNoDebit'] ?? null;
        $acnt->salary_acnt = $data['salaryAcnt'] ?? null;
        $acnt->corporate_acnt = $data['corporateAcnt'] ?? null;
        $acnt->open_date = formatDate($data['openDate'] ?? null);
        $acnt->closed_by = $data['closedBy'] ?? null;
        $acnt->closed_date = formatDate($data['closedDate'] ?? null);
        $acnt->last_dt_date = formatDate($data['lastDtDate'] ?? null);
        $acnt->last_ct_date = formatDate($data['lastCtDate'] ?? null);
        $acnt->last_seq_txn = $data['lastSeqTxn'] ?? null;
        $acnt->monthly_wd_count = $data['monthlyWdCount'] ?? null;
        $acnt->cap_method = $data['capMethod'] ?? null;
        $acnt->cap_method_name = $data['capMethodName'] ?? null;
        $acnt->cap_acnt_code = $data['capAcntCode'] ?? null;
        $acnt->cap_cur_code = $data['capCurCode'] ?? null;
        $acnt->min_amount = $data['minAmount'] ?? null;
        $acnt->max_amount = $data['maxAmount'] ?? null;
        $acnt->paymt_default = $data['paymtDefault'] ?? null;
        $acnt->od_contract_code = $data['odContractCode'] ?? null;
        $acnt->od_class_no = $data['odClassNo'] ?? null;
        $acnt->od_class_name = $data['odClassName'] ?? null;
        $acnt->acnt_manager = $data['acntManager'] ?? null;
        $acnt->od_type = $data['odType'] ?? null;
        $acnt->od_flag_wroff_int = $data['odFlagWroffInt'] ?? null;
        $acnt->od_flag_wroff = $data['odFlagWroff'] ?? null;
        $acnt->acrint_bal = $data['acrintBal'] ?? null;
        $acnt->avail_bal = $data['availBal'] ?? null;
        $acnt->avail_limit = $data['availLimit'] ?? null;
        $acnt->blocked_bal = $data['blockedBal'] ?? null;
        $acnt->current_bal = $data['currentBal'] ?? null;
        $acnt->daily_basis_code = $data['dailyBasisCode'] ?? null;
        $acnt->cust_type = $data['custType'] ?? null;
        $acnt->od_limit = $data['odLimit'] ?? null;
        $acnt->passbook_facility = $data['passbookFacility'] ?? null;
        $acnt->penalty_rcv = $data['penaltyRcv'] ?? null;
        $acnt->total_avail_bal = $data['totalAvailBal'] ?? null;
        $acnt->unex = $data['unex'] ?? null;
        $acnt->unexint_rcv = $data['unexintRcv'] ?? null;
        $acnt->unexint_rcv_bill = $data['unexintRcvBill'] ?? null;
        $acnt->is_secure = $data['isSecure'] ?? null;
        $acnt->read_name = $data['readName'] ?? null;
        $acnt->read_bal = $data['readBal'] ?? null;
        $acnt->read_tran = $data['readTran'] ?? null;
        $acnt->do_tran = $data['doTran'] ?? null;
        $acnt->get_with_secure = $data['getWithSecure'] ?? null;
        $acnt->status = $data['statusSys'] ?? null;
        $acnt->is_allow_partial_liq = $data['isAllowPartialLiq'] ?? null;
        $acnt->save();
    }

    public function detailAcntData($acnt_code, $instid, $isBackOff = false)
    {
        if ($isBackOff) {
            return new CasaDetailResource(CasaAcnt::where('acnt_code', $acnt_code)->where('instid', $instid)->first());
        }
        return new CasaMobileDetailResource(CasaAcnt::where('acnt_code', $acnt_code)->where('instid', $instid)->first());
    }
}
