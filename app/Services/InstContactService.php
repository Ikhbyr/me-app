<?php

namespace App\Services;

use App\Models\InstContact;
use Carbon\Carbon;
use Exception;

class InstContactService
{
    function list($withs = [], $deleted = false)
    {
        $user = auth()->user();
        $result = InstContact::with('contactTypeName:value,name')->where('statusid', '<>', -1)->with($withs);
        if ($user->isadmin != '1') {
            $result = $result->where('instid', $user->instid);
        }
        return $result;
    }

    public function get($id, $withs = [])
    {
        return $this->list($withs)->where('id', $id);
    }

    public function update($data)
    {
        $clientContact = $this->get($data['id'])->first();
        if (!$clientContact) {
            throw new Exception("InstContact [" . $data['id'] . "] not found!");
        }

        foreach ($clientContact->fillable as $field) {
            if (array_key_exists($field, $data)) {
                $clientContact->$field = $data[$field];
            }
        }
        $clientContact->updated_at = Carbon::now();
        $clientContact->updated_by = auth()->user()->userid;
        $clientContact->save();
        return $clientContact;
    }

    public function store($data)
    {
        $clientContact = new InstContact();
        $user = auth()->user();
        foreach ($clientContact->fillable as $field) {
            if (array_key_exists($field, $data)) {
                $clientContact->$field = $data[$field];
            }
        }
        if ($user->isadmin != '1') {
            $clientContact->instid = $user->instid;
        }
        $clientContact->statusId = 1;
        $clientContact->created_at = Carbon::now();
        $clientContact->created_by = auth()->user()->userid;
        $clientContact->save();
        return $clientContact;
    }

    public function delete($id)
    {
        $clientContact = $this->get($id)->first();
        if (!$clientContact) {
            throw new Exception("InstContact [" . $id . "] not found!");
        }
        $clientContact->statusid = -1;
        $clientContact->updated_at = Carbon::now();
        $clientContact->updated_by = auth()->user()->userid;
        $clientContact->save();
        return $clientContact;
    }

    public function restore($id)
    {
        $clientContact = InstContact::where('statusid', -1)->where('id', $id)->first();
        if (!$clientContact) {
            throw new Exception("InstContact [" . $id . "] not deleted!");
        }
        $clientContact->statusid = 1;
        $clientContact->updated_at = Carbon::now();
        $clientContact->updated_by = auth()->user()->userid;
        $clientContact->save();
        return $clientContact;
    }
}
