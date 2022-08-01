<?php

namespace App\Services;

use App\Http\Resources\LoanDetailResource;
use App\Http\Resources\LoanMobileDetailResource;
use App\Models\LoanAcnt;
use Carbon\Carbon;

class LoanAcntService
{
    /**
     * Дансны жагсаалт дээр ирж байгаа датагаар данс үүсгэх
     *
     * @param  mixed $data
     * @return void
     */
    public function createAccountNes($data, $instid, $user)
    {
        $acnt = new LoanAcnt();
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
        $acnt->avail_com_bal = $data['availBalance'] ?? null;
        $acnt->princ_bal = $data['balance'] ?? null;
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
        $acnt = LoanAcnt::where('acnt_code', $data['acntCode'])->where('instid', $isntid)->first();
        if(empty($acnt)) {
            return;
        }
        $acnt->name = $data['name'] ?? null;
        $acnt->name2 = $data['name2'] ?? null;
        $acnt->cust_name = $data['custName'] ?? null;
        $acnt->cust_name2 = $data['custName2'] ?? null;
        $acnt->company_code = $data['companyCode'] ?? null;
        $acnt->prod_code = $data['prodCode'] ?? null;
        $acnt->cur_code = $data['curCode'] ?? null;
        $acnt->brch_code = $data['brchCode'] ?? null;
        $acnt->cust_code = $data['custCode'] ?? null;
        $acnt->acnt_manager = $data['acntManager'] ?? null;
        $acnt->first_acnt_manager = $data['firstAcntManager'] ?? null;
        $acnt->seg_code = $data['segCode'] ?? null;
        $acnt->status = $data['status'] ?? null;
        $acnt->slevel = $data['slevel'] ?? null;
        $acnt->class_no = $data['classNo'] ?? null;
        $acnt->start_date = formatDate($data['startDate'] ?? null);
        $acnt->end_date = formatDate($data['endDate'] ?? null);
        $acnt->term_len = $data['termLen'] ?? null;
        $acnt->approv_date = formatDate($data['approvDate'] ?? null);
        $acnt->approv_amount = $data['approvAmount'] ?? null;
        $acnt->purpose = $data['purpose'] ?? null;
        $acnt->sub_purpose = $data['subPurpose'] ?? null;
        $acnt->flag_stopped = $data['flagStopped'] ?? null;
        $acnt->flag_stopped_int = $data['flagStoppedInt'] ?? null;
        $acnt->flag_wroff_princ = $data['flagWroffPrinc'] ?? null;
        $acnt->flag_wroff_int = $data['flagWroffInt'] ?? null;
        $acnt->is_not_auto_class = $data['isNotAutoClass'] ?? null;
        $acnt->last_bill_no = $data['lastBillNo'] ?? null;
        $acnt->repay_acnt_code = $data['repayAcntCode'] ?? null;
        $acnt->repay_priority = $data['repayPriority'] ?? null;
        $acnt->active_nrs_version = $data['activeNrsVersion'] ?? null;
        $acnt->daily_basis_code = $data['dailyBasisCode'] ?? null;
        $acnt->flag_move_sa = $data['flagMoveSa'] ?? null;
        $acnt->sa_date = formatDate($data['saDate'] ?? null);
        $acnt->com_revolving = $data['comRevolving'] ?? null;
        $acnt->last_txn_date = formatDate($data['lastTxnDate'] ?? null);
        $acnt->adv_date = formatDate($data['advDate'] ?? null);
        $acnt->adv_amount = $data['advAmount'] ?? null;
        $acnt->closed_date = formatDate($data['closedDate'] ?? null);
        $acnt->created_by = $data['createdBy'] ?? null;
        $acnt->created_at = $data['createdAt'] ?? null;
        $acnt->los_acnt_code = $data['losAcntCode'] ?? null;
        $acnt->flag_sec = $data['flagSec'] ?? null;
        $acnt->prod_type = $data['prodType'] ?? null;
        $acnt->payment_method = $data['paymentMethod'] ?? null;
        $acnt->sec_type = $data['secType'] ?? null;
        $acnt->sec_from_acnt = $data['secFromAcnt'] ?? null;
        $acnt->sec_to_acnt = $data['secToAcnt'] ?? null;
        $acnt->sec_tmp_acnt = $data['secTmpAcnt'] ?? null;
        $acnt->sec_inc_exp_acnt = $data['secIncExpAcnt'] ?? null;
        $acnt->extend_count = $data['extendCount'] ?? null;
        $acnt->sold_date = formatDate($data['soldDate'] ?? null);
        $acnt->acquired_date = formatDate($data['acquiredDate'] ?? null);
        $acnt->sold_seq_txn = $data['soldSeqTxn'] ?? null;
        $acnt->acquired_seq_txn = $data['acquiredSeqTxn'] ?? null;
        $acnt->last_seq_txn = $data['lastSeqTxn'] ?? null;
        $acnt->class_no_trm = $data['classNoTrm'] ?? null;
        $acnt->class_no_qlt = $data['classNoQlt'] ?? null;
        $acnt->los_multi_acnt = $data['losMultiAcnt'] ?? null;
        $acnt->repay_acnt_code_other_com = $data['repayAcntCodeOtherCom'] ?? null;
        $acnt->is_browse_acnt_other_com = $data['isBrowseAcntOtherCom'] ?? null;
        $acnt->is_linked_secz = $data['isLinkedSecz'] ?? null;
        $acnt->repay_acnt_sys_no = $data['repayAcntSysNo'] ?? null;
        $acnt->last_accrual_date = formatDate($data['lastAccrualDate'] ?? null);
        $acnt->prod_name = $data['prodName'] ?? null;
        $acnt->brch_name = $data['brchName'] ?? null;
        $acnt->class_qlt_name = $data['classQltName'] ?? null;
        $acnt->seg_name = $data['segName'] ?? null;
        $acnt->class_name = $data['className'] ?? null;
        $acnt->class_trm_name = $data['classTrmName'] ?? null;
        $acnt->status_name = $data['statusName'] ?? null;
        $acnt->acnt_manager_name = $data['acntManagerName'] ?? null;
        $acnt->first_acnt_manager_name = $data['firstAcntManagerName'] ?? null;
        $acnt->cust_type = $data['custType'] ?? null;
        $acnt->princ_bal = $data['princBal'] ?? null;
        $acnt->cont_available = $data['contAvailable'] ?? null;
        $acnt->revol_amt = $data['revolAmt'] ?? null;
        $acnt->theor_bal = $data['theorBal'] ?? null;
        $acnt->bill_princ_bal = $data['billPrincBal'] ?? null;
        $acnt->bill_baseint_bal = $data['billBaseintBal'] ?? null;
        $acnt->bill_comint_bal = $data['billComintBal'] ?? null;
        $acnt->fine_min_duebal = $data['fineMinDuebal'] ?? null;
        $acnt->bill_commint_bal_on = $data['billCommintBalOn'] ?? null;
        $acnt->bill_fineb_bal = $data['billFinebBal'] ?? null;
        $acnt->bill_finep_bal = $data['billFinepBal'] ?? null;
        $acnt->acr_baseint_bal = $data['acrBaseintBal'] ?? null;
        $acnt->avail_com_bal = $data['availComBal'] ?? null;
        $acnt->used_com_bal = $data['usedComBal'] ?? null;
        $acnt->prepaid_baseint_bal = $data['prepaidBaseintBal'] ?? null;
        $acnt->total_bal = $data['totalBal'] ?? null;
        $acnt->total_bill = $data['totalBill'] ?? null;
        $acnt->bill_princ_date = formatDate($data['billPrincDate'] ?? null);
        $acnt->bill_baseint_date = formatDate($data['billBaseintDate'] ?? null);
        $acnt->bill_fine_date = formatDate($data['billFineDate'] ?? null);
        $acnt->term_basis = $data['termBasis'] ?? null;
        $acnt->min_term_unit = $data['minTermUnit'] ?? null;
        $acnt->max_term_unit = $data['maxTermUnit'] ?? null;
        $acnt->def_term_unit = $data['defTermUnit'] ?? null;
        $acnt->autooff_int = $data['autooffInt'] ?? null;
        $acnt->autooff_option_int = $data['autooffOptionInt'] ?? null;
        $acnt->autooff_cls_int = $data['autooffClsInt'] ?? null;
        $acnt->autooff_dueopt_int = $data['autooffDueoptInt'] ?? null;
        $acnt->autooff_duedays_int = $data['autooffDuedaysInt'] ?? null;
        $acnt->autochg_cls = $data['autochgCls'] ?? null;
        $acnt->autochg_option_cls = $data['autochgOptionCls'] ?? null;
        $acnt->autochg_due_cls = $data['autochgDueCls'] ?? null;
        $acnt->autochg_formulaid_cls = $data['autochgFormulaidCls'] ?? null;
        $acnt->crt_billbint_topay = $data['crtBillbintTopay'] ?? null;
        $acnt->allowed_cam = $data['allowedCam'] ?? null;
        $acnt->fine_condition = $data['fineCondition'] ?? null;
        $acnt->fine_grace = $data['fineGrace'] ?? null;
        $acnt->flag_stopped_name = $data['flagStoppedName'] ?? null;
        $acnt->flag_stopped_int_name = $data['flagStoppedIntName'] ?? null;
        $acnt->flag_move_sa_name = $data['flagMoveSaName'] ?? null;
        $acnt->repay_acnt_name = $data['repayAcntName'] ?? null;
        $acnt->purpose_name = $data['purposeName'] ?? null;
        $acnt->sub_purpose_name = $data['subPurposeName'] ?? null;
        $acnt->is_secure = $data['isSecure'] ?? null;
        $acnt->next_schd_date = formatDate($data['nextSchdDate'] ?? null);
        $acnt->next_schd_amt = $data['nextSchdAmt'] ?? null;
        $acnt->next_schd_int = $data['nextSchdInt'] ?? null;
        $acnt->cat_code = $data['catCode'] ?? null;
        $acnt->cat_sub_code = $data['catSubCode'] ?? null;
        $acnt->cat_sub_name = $data['catSubName'] ?? null;
        $acnt->cat_name = $data['catName'] ?? null;
        $acnt->sec_acnt_code = $data['secAcntCode'] ?? null;
        $acnt->sec_acnt_name = $data['secAcntName'] ?? null;
        $acnt->limit = $data['limit'] ?? null;
        $acnt->princ_bal_on = $data['princBalOn'] ?? null;
        $acnt->acr_commint_bal = $data['acrCommintBal'] ?? null;
        $acnt->save();
    }

    public function detailAcntData($acnt_code, $instid, $isBackOff = false) {
        if ($isBackOff) {
            return new LoanDetailResource(LoanAcnt::where('acnt_code', $acnt_code)->where('instid', $instid)->first());
        }
        return new LoanMobileDetailResource(LoanAcnt::where('acnt_code', $acnt_code)->where('instid', $instid)->first());
    }
}
