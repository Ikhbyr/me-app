<?php

namespace App\Services;

use App\Models\BankSys;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class BankSysService
{
    public function list($withs = [], $deleted = false)
    {
        $user = auth()->user();
        $result = BankSys::with($withs)->where('statusid', '<>', -1)->where('instid', $user->instid);
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
        if (isset($data['sec1'])) {
            $data['sec1'] = safeEncrypt($data['sec1']);
        }
        if (isset($data['sec2'])) {
            $data['sec2'] = safeEncrypt($data['sec2']);
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
            $bankSys = BankSys::where('typeid', $data['typeid'])->where('statusid', '<>', -1)
                ->where('instid', $user->instid)->first();
            if ($bankSys) {
                throw new Exception('Банкны тохиргоо давхардаж байна.');
            }
            $conf = new BankSys();
            if (isset($data['sec1'])) {
                $data['sec1'] = safeEncrypt($data['sec1']);
            }
            if (isset($data['sec2'])) {
                $data['sec2'] = safeEncrypt($data['sec2']);
            }
            foreach ($conf->fillable as $field) {
                if (array_key_exists($field, $data)) {
                    $conf->$field = $data[$field];
                }
            }
            $conf->statusid = 1;
            $conf->created_at = Carbon::now();
            $conf->created_by = $user->userid;
            $conf->instid = $user->instid;
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
            throw new Exception("BankSys [" . $id . "] not found!");
        }
        $conf->statusid = -1;
        $conf->updated_at = Carbon::now();
        $conf->updated_by = auth()->user()->userid;
        $conf->save();
        return $conf;
    }

    public function restore($id)
    {
        $conf = BankSys::where('statusid', -1)->first();
        if (!$conf) {
            throw new Exception("BankSys [" . $id . "] not deleted!");
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
