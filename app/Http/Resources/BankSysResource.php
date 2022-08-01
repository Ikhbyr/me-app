<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BankSysResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $user = auth()->user();
        return [
            'id' => $this->id,
            'name' => $this->name,
            'typeid' => $this->typeid,
            'config' => $this->config,
            'conn_conf_id' => $this->conn_conf_id,
            'descr' => $this->descr,
            'statusid' => $this->statusid,
            'created_at' => formatDate($this->created_at),
            'created_by' => $this->created_by,
            'updated_at' => formatDate($this->updated_at),
            'updated_by' => $this->updated_by,
            'instid' => $this->instid,
            'sec1' => $this->instid == $user->instid ? safeDecrypt($this->sec1) : "",
            'sec2' => $this->instid == $user->instid ? safeDecrypt($this->sec2) : "",
        ];
    }
}
