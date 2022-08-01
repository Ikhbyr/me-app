<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AccountIntResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'acntCode' => $this->acnt_code,
            'otherInfo' => $this->other_info,
            'payCustName' => $this->pay_cust_name,
            'intRate' => $this->int_rate,
            'sourceBalType' => $this->source_bal_type,
            'lastAcrInfo' => $this->last_acr_info,
            'type' => $this->type,
            'accrIntAmt' => $this->accr_int_amt,
            'intTypeName' => $this->int_type_name,
            'intRateOption' => $this->int_rate_option,
            'dailyIntAmt' => $this->daily_int_amt,
            'lastAcrTxnSeq' => $this->last_acr_txn_seq,
            'balTypeCode' => $this->bal_type_code,
            'intTypeCode' => $this->int_type_code,
            'lastAcrAmt' => $this->last_acr_amt,
            'lastAccrualDate' => formatDate($this->last_accrual_date),
        ];
    }
}
