<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AccountStatementResource extends JsonResource
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
            'contCurRate' => $this->cont_cur_rate,
            'income' => $this->income,
            'jrno' => $this->jrno,
            'beginBal' => $this->begin_bal,
            'endBal' => $this->end_bal,
            'txnDate' => $this->txn_date->format('Y-m-d'),
            'txnCode' => $this->txn_code,
            'balTypeCode' => $this->bal_type_code,
            'outcome' => $this->outcome,
            'balance' => $this->balance,
            'txnDesc' => $this->txn_desc,
            'contAcntCode' => $this->cont_acnt_code,
            'contBankAcntCode' => $this->cont_bank_acnt_code,
            'contBankAcntName' => $this->cont_bank_acnt_name,
            'contBankCode' => $this->cont_bank_code,
            'contBankName' => $this->cont_bank_name,
            'postDate' => formatDate($this->post_date),
        ];
    }
}
