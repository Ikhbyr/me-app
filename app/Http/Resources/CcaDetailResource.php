<?php

namespace App\Http\Resources;

use App\Models\AcntIntList;
use Illuminate\Http\Resources\Json\JsonResource;

class CcaDetailResource extends JsonResource
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
            // 'sysNo' => $this->sys_no,
            'acntCode' => $this->acnt_code,
            'acntCodeName' => $this->name,
            // 'name2' => $this->name2,
            'lastLiquidateDate' => $this->last_liquidate_date,
            'endDate' => $this->end_date,
            'graceDays' => $this->grace_days,
            'dueDate' => $this->due_date,
            'blockAmountPurch' => $this->block_amount_purch,
            'statementDate' => $this->statement_date,
            'statusName' => $this->status_name,
            'minPayAmt' => $this->min_pay_amt,
            // 'getWithSecure' => $this->get_with_secure,
            'actualStartDate' => $this->actual_start_date,
            'availBalance' => $this->avail_balance,
            'blockAmountCash' => $this->block_amount_cash,
            // 'brchCode' => $this->brch_code,
            // 'brchName' => $this->brch_name,
            // 'brchName2' => $this->brch_name2,
            'cashLimit' => $this->cash_limit,
            'className' => $this->class_name,
            // 'className2' => $this->class_name2,
            'cycleNo' => $this->cycle_no,
            // 'companyCode' => $this->company_code,
            'curCode' => $this->cur_code,
            'custCode' => $this->cust_code,
            'classNo' => $this->class_no,
            // 'dailyBasisCode' => $this->daily_basis_code,
            'description' => $this->description,
            'expCashAmount' => $this->exp_cash_amount,
            'expInterestAmount' => $this->exp_interest_amount,
            'expPurchaseAmount' => $this->exp_purchase_amount,
            // 'isSecure' => $this->is_secure,
            'prodCode' => $this->prod_code,
            'expTransferAmount' => $this->exp_transfer_amount,
            // 'isNotAutoClass' => $this->is_not_auto_class,
            'lastExpDate' => $this->last_exp_date,
            'lastTxnDate' => $this->last_txn_date,
            'odFee' => $this->od_fee,
            'olFee' => $this->ol_fee,
            'otherFee' => $this->other_fee,
            'overLimitAmt' => $this->over_limit_amt,
            'overLimitPercent' => $this->over_limit_percent,
            'prodCodeName' => $this->prod_code_name,
            // 'prodCodeName2' => $this->prod_code_name2,
            'repaymentAcnt' => $this->repayment_acnt,
            // 'repaymentMode' => $this->repayment_mode,
            'repaymentModeName' => $this->repayment_mode_name,
            // 'repaymentModeName2' => $this->repayment_mode_name2,
            // 'repaymentType' => $this->repayment_type,
            'repaymentTypeName' => $this->repayment_type_name,
            // 'repaymentTypeName2' => $this->repayment_type_name2,
            // 'segCode' => $this->seg_code,
            'startDate' => $this->start_date,
            // 'statusId' => $this->status_id,
            'statusIdName' => $this->status_id_name,
            // 'statusIdName2' => $this->status_id_name2,
            // 'statusName2' => $this->status_name2,
            // 'statusSys' => $this->status_sys,
            'totalExpAmount' => $this->total_exp_amount,
            'totalLimit' => $this->total_limit,
            'acntIntInfos' => $this->acntIntInfos($this->acnt_code, $this->instid),
        ];
    }

    public function acntIntInfos($acnt_code, $instid)
    {
        return new CcaAcntIntCollection(AcntIntList::where('acnt_code', $acnt_code)->where('instid', $instid)->get());
    }
}
