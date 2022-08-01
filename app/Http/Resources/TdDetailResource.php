<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TdDetailResource extends JsonResource
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
            // 'sysNo' => $this->sys_no,
            'name' => $this->name,
            'custCode' => $this->cust_code,
            'custName' => $this->cust_name,
            'prodName' => $this->prod_name,
            'termBasis' => $this->term_basis,
            'tenor' => $this->tenor,
            'termLen' => $this->term_len,
            //'name2' => $this->name2,
            //'companyCode' => $this->company_code,
            //'prodCode' => $this->prod_code,
            //'brchCode' => $this->brch_code,
            'curCode' => $this->cur_code,
            //'jointOrSingle' => $this->joint_or_single,
            //'statusSys' => $this->status_sys,
            //'statusCustom' => $this->status_custom,
            //'statusDate' => $this->status_date,
            //'segCode' => $this->seg_code,
            'openDateOrg' => $this->open_date_org,
            'startDate' => $this->start_date,
            'maturityDate' => $this->maturity_date,
            //'isCorpAcnt' => $this->is_corp_acnt,
            //'lastDtDate' => $this->last_dt_date,
            //'lastCtDate' => $this->last_ct_date,
            //'lastSeqTxn' => $this->last_seq_txn,
            //'capMethod' => $this->cap_method,
            //'rcvAcntCode' => $this->rcv_acnt_code,
            //'slevel' => $this->slevel,
            //'closedBy' => $this->closed_by,
            //'closedDate' => $this->closed_date,
            //'closedCond' => $this->closed_cond,
            //'classNo' => $this->class_no,
            //'maturityOption' => $this->maturity_option,
            //'flagNoTb' => $this->flag_no_tb,
            //'dailyBasisCode' => $this->daily_basis_code,
            //'brchName' => $this->brch_name,
            //'custType' => $this->cust_type,
            //'statusSysName' => $this->status_sys_name,
            'currentBal' => $this->current_bal,
            'availBal' => $this->avail_bal,
            'blockBal' => $this->block_bal,
            'acrintBal' => $this->acrint_bal,
            'capInt' => $this->cap_int,
            //'capInt2' => $this->cap_int2,
            'capMethodName' => $this->cap_method_name,
            //'rcvAcntName' => $this->rcv_acnt_name,
            'segName' => $this->seg_name,
            //'isCorpName' => $this->is_corp_name,
            //'jointOrSingleName' => $this->joint_or_single_name,
            //'closedByName' => $this->closed_by_name,
            //'passbookFacility' => $this->passbook_facility,
            //'className' => $this->class_name,
            'maturityOptionName' => $this->maturity_option_name,
            //'readName' => $this->read_name,
            //'readBal' => $this->read_bal,
            //'readTran' => $this->read_tran,
            //'doTran' => $this->do_tran,
            //'isSecure' => $this->is_secure,
            //'lastTbDate' => $this->last_tb_date,
            //'flagNoTbName' => $this->flag_no_tb_name,
            //'catCode' => $this->cat_code,
            //'catSubCode' => $this->cat_sub_code,
            //'catSubName' => $this->cat_sub_name,
            //'catName' => $this->cat_name,
        ];
    }
}
