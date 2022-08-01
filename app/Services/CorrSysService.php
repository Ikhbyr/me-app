<?php

namespace App\Services;

use App\Models\CorrSys;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class CorrSysService
{
    public function list($withs = [], $deleted = false)
    {
        $user = auth()->user();
        $result = CorrSys::with($withs)->where('instid', $user->instid)->where('statusid', '<>', -1);
        return $result;
    }

    public function get($id, $withs = [])
    {
        return $this->list($withs)->where('id', $id);
    }

    public function update($id, $data)
    {
        $conf = $this->get($id)->first();
        $user = auth()->user();
        if (!$conf) {
            throw new Exception("CorrSys [" . $id . "] not found!");
        }

        if ($user->instid != $conf->instid) {
            // Өөр байгууллагын хэрэглэгч мэдээлэл өөрчлөх гэж оролдов.
            throw new Exception("Мэдээлэл өөрчлөх эрхгүй байна!");
        }
        foreach ($conf->fillable as $field) {
            if (array_key_exists($field, $data)) {
                $conf->$field = $data[$field];
            }
        }
        $conf->updated_at = Carbon::now();
        $conf->updated_by = $user->userid;
        $conf->save();
        return $conf;
    }

    public function store($data)
    {
        $user = auth()->user();
        try {
            $conf = new CorrSys();
            foreach ($conf->fillable as $field) {
                if (array_key_exists($field, $data)) {
                    $conf->$field = $data[$field];
                }
            }
            $conf->statusid = 1;
            $conf->instid = $user->instid;
            $conf->created_at = Carbon::now();
            $conf->created_by = $user->userid;
            $conf->save();

            return $conf;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete($id)
    {
        $conf = $this->get($id)->first();
        if (!$conf) {
            throw new Exception("CorrSys [" . $id . "] not found!");
        }
        $conf->statusid = -1;
        $conf->updated_at = Carbon::now();
        $conf->updated_by = auth()->user()->userid;
        $conf->save();
        return $conf;
    }

    public function restore($id)
    {
        $conf = CorrSys::where('statusid', -1)->first();
        if (!$conf) {
            throw new Exception("CorrSys [" . $id . "] not deleted!");
        }
        $conf->statusid = 1;
        $conf->updated_at = Carbon::now();
        $conf->updated_by = auth()->user()->userid;
        $conf->save();
        return $conf;
    }

    public function getByName($name)
    {
        return $this->list()->where('name', $name)->first();
    }
}
