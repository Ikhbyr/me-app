<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CcaResource extends JsonResource
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
            'sysNo' => $this->sys_no,
            'acntName' => $this->name,
            'acntCode' => $this->acnt_code,
            'isSecure' => $this->is_secure,
            'custCode' => $this->cust_code,
            'prodCode' => $this->prod_code,
            'availBalance' => $this->avail_balance,
            'balance' => $this->total_exp_amount,
            'isAllowPartialLiq' => 1,
            'acntType' => $this->acnt_type,
            'prodName' => $this->prod_code_name,
            'curCode' => $this->cur_code,
            'status' => $this->status_sys,
            'instid' => $this->instid,
            'inst' => $this->inst,
        ];
    }
}
