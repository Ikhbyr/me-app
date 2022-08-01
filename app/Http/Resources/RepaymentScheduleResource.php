<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RepaymentScheduleResource extends JsonResource
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
            'schdDate' => formatDate($this->schd_date),
            'amount' => $this->amount,
            'intAmount' => $this->int_amount,
            'totalAmount' => $this->total_amount,
            'theorBal' => $this->theor_bal,
        ];
    }
}
