<?php

namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;

class InstUserProfileResource extends JsonResource
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
            'instid' => $this->instid,
            'userid' => $this->userid,
            'email' => $this->email,
            'phoneuser' => $this->phoneuser,
            'password' => $this->password,
            'status' => $this->status,
            'createuser' => $this->createuser,
            'createdate' => $this->createdate,
            'lastupdateuser' => $this->lastupdateuser,
            'lastupdate' => $this->lastupdate,
            'passdate' => $this->passdate,
            'passwrong' => $this->passwrong,
            'registernum' => $this->registernum,
            'startdate' => $this->startdate,
            'enddate' => $this->enddate,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'branchid' => $this->branchid,
            'google_auth_key' => $this->google_auth_key,
            'use_google_auth' => $this->use_google_auth,
            'roles' => $this->roles
        ];
    }
}
