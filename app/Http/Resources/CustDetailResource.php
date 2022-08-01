<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CustDetailResource extends JsonResource
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
            'id' => $this->id,
            // 'instid' => $this->instid ,
            // 'corrid' => $this->corrid ,
            'cif' => $this->cif,
            'familyname' => $this->familyname ,
            // 'familyname2' => $this->familyname2 ,
            'lname' => $this->lname,
            // 'lname2' => $this->lname2 ,
            'fname' => $this->fname ,
            // 'fname2' => $this->fname2 ,
            'gender' => $this->gender ,
            'regno' => $this->regno ,
            // 'register_mask_code' => $this->register_mask_code ,
            'nationality' => $this->nationality ,
            'birthday' => $this->birthday->format('Y-m-d'),
            // 'lang' => $this->lang ,
            'ethnicity' => $this->ethnicity ,
            'citizenship' => $this->citizenship ,
            'birthplace' => $this->birthplace ,
            'segment' => $this->segment ,
            // 'employment' => $this->employment ,
            // 'categories' => $this->categories ,
            'education' => $this->education ,
            'maritalstatus' => $this->maritalstatus ,
            'phone' => $this->phone ,
            'phone2' => $this->phone2 ,
            'email' => $this->email ,
            'fax' => $this->fax ,
            // 'familysize' => $this->familysize ,
            // 'region' => $this->region ,
            // 'subregion' => $this->subregion ,
            'address' => $this->address ,
            // 'status' => $this->status ,
            // 'created_at' => $this->created_at ,
            // 'created_by' => $this->created_by ,
            // 'updated_at' => $this->updated_at ,
            // 'updated_by' => $this->updated_by ,
            // 'industry' => $this->industry ,
            'shortname' => $this->shortname ,
            // 'shortname2' => $this->shortname2 ,
            // 'isbl' => $this->isbl ,
            // 'iscompanycustomer' => $this->iscompanycustomer ,
            // 'ispolitical' => $this->ispolitical ,
            // 'isvatpayer' => $this->isvatpayer ,
            // 'monthlyincome' => $this->monthlyincome ,
            // 'immovabletype' => $this->immovabletype ,
            // 'ownership' => $this->ownership
        ];
    }
}
