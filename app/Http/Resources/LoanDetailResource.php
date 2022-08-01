<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LoanDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'acntCode' => $this->acnt_code,
            'custName' => $this->cust_name,
            'name' => $this->name,
            // 'name2' => $this->name2,
            // 'companyCode' => $this->company_code,
            // 'prodCode' => $this->prod_code,
            'curCode' => $this->cur_code,
            // 'brchCode' => $this->brch_code,
            'custCode' => $this->cust_code,
            // 'acntManager' => $this->acnt_manager,
            // 'firstAcntManager' => $this->first_acnt_manager,
            // 'segCode' => $this->seg_code,
            // 'statusSys' => $this->status,
            // 'slevel' => $this->slevel,
            // 'classNo' => $this->class_no,
            'startDate' => $this->start_date,
            'endDate' => $this->end_date,
            'termLen' => $this->term_len,
            'approvDate' => $this->approv_date,
            'approvAmount' => $this->approv_amount,
            // 'purpose' => $this->purpose,
            // 'subPurpose' => $this->sub_purpose,
            // 'flagStopped' => $this->flag_stopped,
            // 'flagStoppedInt' => $this->flag_stopped_int,
            // 'flagWroffPrinc' => $this->flag_wroff_princ,
            // 'flagWroffInt' => $this->flag_wroff_int,
            // 'isNotAutoClass' => $this->is_not_auto_class,
            // 'lastBillNo' => $this->last_bill_no,
            // 'repayAcntCode' => $this->repay_acnt_code,
            // 'repayPriority' => $this->repay_priority,
            // // 'activeNrsVersion' => $this->active_nrs_version,
            // 'dailyBasisCode' => $this->daily_basis_code,
            // 'flagMoveSa' => $this->flag_move_sa,
            // 'saDate' => $this->sa_date,
            // 'comRevolving' => $this->com_revolving,
            'lastTxnDate' => $this->last_txn_date,
            'advDate' => $this->adv_date,
            'advAmount' => $this->adv_amount,
            // 'closedDate' => $this->closed_date,
            // 'createdBy' => $this->created_by,
            // 'createdAt' => $this->created_at,
            // 'losAcntCode' => $this->los_acnt_code,
            // 'flagSec' => $this->flag_sec,
            'prodType' => $this->prod_type,
            // 'paymentMethod' => $this->payment_method,
            // 'secType' => $this->sec_type,
            // 'secFromAcnt' => $this->sec_from_acnt,
            // 'secToAcnt' => $this->sec_to_acnt,
            // 'secTmpAcnt' => $this->sec_tmp_acnt,
            // 'secIncExpAcnt' => $this->sec_inc_exp_acnt,
            // 'extendCount' => $this->extend_count,
            // 'soldDate' => $this->sold_date,
            // 'acquiredDate' => $this->acquired_date,
            // 'soldSeqTxn' => $this->sold_seq_txn,
            // 'acquiredSeqTxn' => $this->acquired_seq_txn,
            // 'lastSeqTxn' => $this->last_seq_txn,
            // 'classNoTrm' => $this->class_no_trm,
            // 'classNoQlt' => $this->class_no_qlt,
            // 'losMultiAcnt' => $this->los_multi_acnt,
            // 'repayAcntCodeOtherCom' => $this->repay_acnt_code_other_com,
            // 'isBrowseAcntOtherCom' => $this->is_browse_acnt_other_com,
            // 'isLinkedSecz' => $this->is_linked_secz,
            // 'repayAcntSysNo' => $this->repay_acnt_sys_no,
            // 'lastAccrualDate' => $this->last_accrual_date,
            'prodName' => $this->prod_name,
            // 'brchName' => $this->brch_name,
            'classQltName' => $this->class_qlt_name,
            'segName' => $this->seg_name,
            'className' => $this->class_name,
            'classTrmName' => $this->class_trm_name,
            // 'statusName' => $this->status_name,
            // 'acntManagerName' => $this->acnt_manager_name,
            // 'firstAcntManagerName' => $this->first_acnt_manager_name,
            // 'custType' => $this->cust_type,
            'princBal' => $this->princ_bal,
            // 'contAvailable' => $this->cont_available,
            'revolAmt' => $this->revol_amt,
            'theorBal' => $this->theor_bal,
            'billPrincBal' => $this->bill_princ_bal,
            'billBaseintBal' => $this->bill_baseint_bal,
            'billComintBal' => $this->bill_comint_bal,
            'billFinebBal' => $this->bill_fineb_bal,
            'billFinepBal' => $this->bill_finep_bal,
            'acrBaseintBal' => $this->acr_baseint_bal,
            'availComBal' => $this->avail_com_bal,
            'usedComBal' => $this->used_com_bal,
            'prepaidBaseintBal' => $this->prepaid_baseint_bal,
            'totalBal' => $this->total_bal,
            'totalBill' => $this->total_bill,
            'billPrincDate' => $this->bill_princ_date,
            'billBaseintDate' => $this->bill_baseint_date,
            'billFineDate' => $this->bill_fine_date,
            'termBasis' => $this->term_basis,
            'minTermUnit' => $this->min_term_unit,
            'maxTermUnit' => $this->max_term_unit,
            'defTermUnit' => $this->def_term_unit,
            // 'autooffInt' => $this->autooff_int,
            // 'autooffOptionInt' => $this->autooff_option_int,
            // 'autooffClsInt' => $this->autooff_cls_int,
            // 'autooffDueoptInt' => $this->autooff_dueopt_int,
            // 'autooffDuedaysInt' => $this->autooff_duedays_int,
            // 'autochgCls' => $this->autochg_cls,
            // 'autochgOptionCls' => $this->autochg_option_cls,
            // 'autochgDueCls' => $this->autochg_due_cls,
            // 'autochgFormulaidCls' => $this->autochg_formulaid_cls,
            'crtBillbintTopay' => $this->crt_billbint_topay,
            // 'allowedCam' => $this->allowed_cam,
            // 'fineCondition' => $this->fine_condition,
            'fineGrace' => $this->fine_grace,
            // 'flagStoppedName' => $this->flag_stopped_name,
            // 'flagStoppedIntName' => $this->flag_stopped_int_name,
            // 'flagMoveSaName' => $this->flag_move_sa_name,
            // 'repayAcntName' => $this->repay_acnt_name,
            'purposeName' => $this->purpose_name,
            // 'subPurposeName' => $this->sub_purpose_name,
            // 'isSecure' => $this->is_secure,
            'nextSchdDate' => $this->next_schd_date,
            'nextSchdAmt' => $this->next_schd_amt,
            'nextSchdInt' => $this->next_schd_int,
            // 'catCode' => $this->cat_code,
            // 'catSubCode' => $this->cat_sub_code,
            // 'catSubName' => $this->cat_sub_name,
            // 'catName' => $this->cat_name,
            // 'secAcntCode' => $this->sec_acnt_code,
            // 'secAcntName' => $this->sec_acnt_name,
            'limit' => $this->limit,
        ];
    }
}