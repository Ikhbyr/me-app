<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CasaDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // return parent :: toArray($request);

        return [
            'acntCode' => $this->acnt_code,
            'custName' => $this->cust_name,
            //'sysNo' => $this->sys_no,
            'name' => $this->name,
            // 'name2' => $this->name2,
            // 'companyCode' => $this->company_code,
            // 'dormancyDate' => $this->dormancy_date,
            // 'prodCode' => $this->prod_code,
            'prodName' => $this->prod_name,
            // 'brchCode' => $this->brch_code,
            // 'brchName' => $this->brch_name,
            'curCode' => $this->cur_code,
            // 'statusCustom' => $this->status_custom,
            // 'jointOrSingle' => $this->joint_or_single,
            // 'statusDate' => $this->status_date,
            // 'statusSys' => $this->status_sys,
            // 'statusSysName' => $this->status_sys_name,
            'custCode' => $this->cust_code,
            // 'segCode' => $this->seg_code,
            'segName' => $this->seg_name,
            // 'acntType' => $this->acnt_type,
            'acntTypeName' => $this->acnt_type_name,
            // 'flagStopped' => $this->flag_stopped,
            // 'flagDormant' => $this->flag_dormant,
            // 'flagStoppedInt' => $this->flag_stopped_int,
            // 'flagStoppedPayment' => $this->flag_stopped_payment,
            // 'flagFrozen' => $this->flag_frozen,
            // 'flagNoCredit' => $this->flag_no_credit,
            // 'flagNoDebit' => $this->flag_no_debit,
            // 'salaryAcnt' => $this->salary_acnt,
            // 'corporateAcnt' => $this->corporate_acnt,
            'openDate' => $this->open_date,
            // 'closedBy' => $this->closed_by,
            // 'closedDate' => $this->closed_date,
            // 'lastDtDate' => $this->last_dt_date,
            // 'lastCtDate' => $this->last_ct_date,
            // 'lastSeqTxn' => $this->last_seq_txn,
            // 'monthlyWdCount' => $this->monthly_wd_count,
            // 'capMethod' => $this->cap_method,
            'capMethodName' => $this->cap_method_name,
            // 'capAcntCode' => $this->cap_acnt_code,
            // 'capCurCode' => $this->cap_cur_code,
            // 'minAmount' => $this->min_amount,
            // 'maxAmount' => $this->max_amount,
            // 'paymtDefault' => $this->paymt_default,
            'odContractCode' => $this->od_contract_code,
            // 'odClassNo' => $this->od_class_no,
            'odClassName' => $this->od_class_name,
            // 'acntManager' => $this->acnt_manager,
            // 'odType' => $this->od_type,
            // 'odFlagWroffInt' => $this->od_flag_wroff_int,
            // 'odFlagWroff' => $this->od_flag_wroff,
            'acrintBal' => $this->acrint_bal,
            'availBal' => $this->avail_bal,
            'availLimit' => $this->avail_limit,
            'blockedBal' => $this->blocked_bal,
            'currentBal' => $this->current_bal,
            // 'dailyBasisCode' => $this->daily_basis_code,
            // 'custType' => $this->cust_type,
            'odLimit' => $this->od_limit,
            // 'passbookFacility' => $this->passbook_facility,
            // 'penaltyRcv' => $this->penalty_rcv,
            'totalAvailBal' => $this->total_avail_bal,
            // 'unex' => $this->unex,
            // 'unexintRcv' => $this->unexint_rcv,
            // 'unexintRcvBill' => $this->unexint_rcv_bill,
            // 'isSecure' => $this->is_secure,
            // 'readName' => $this->read_name,
            // 'readBal' => $this->read_bal,
            // 'readTran' => $this->read_tran,
            // 'doTran' => $this->do_tran,
            // 'getWithSecure' => $this->get_with_secure,
            // 'statusSys' => $this->status,
            // 'isAllowPartialLiq' => $this->is_allow_partial_liq,

        ];
    }
}
