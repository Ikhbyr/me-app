<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CcaAcntIntResource extends JsonResource
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
            'intRateOption' => $this->int_rate_option,
            'intRate' => $this->int_rate,
            'intLvl' => $this->int_lvl,
            'intLvlName' => $this->int_lvl_name,
            'intTypeCode' => $this->int_type_code,
        ];
    }
}
