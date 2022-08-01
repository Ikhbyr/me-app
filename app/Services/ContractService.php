<?php

namespace App\Services;

use App\Models\Contract;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class ContractService
{
    public function list($withs = [], $deleted = false)
    {
        $user = auth()->user();
        $result = Contract::with($withs)->where('statusid', '<>', -1)->where('instid', $user->instid);
        return $result;
    }

    public function get($id, $withs = [])
    {
        return $this->list($withs)->where('id', $id);
    }

    public function update($id, $data)
    {
        $contract = $this->get($id)->first();
        $user = auth()->user();
        if (!$contract) throw new Exception("[" . $id . "] дугаартай бүртгэл олдсонгүй!");

        foreach ($contract->fillable as $field) {
            if (array_key_exists($field, $data)) {
                $contract->$field = $data[$field];
            }
        }
        $contract->updated_at = Carbon::now();
        $contract->updated_by = $user->userid;
        $contract->save();
        return $contract;
    }

    public function store($data)
    {
        $user = auth()->user();
        try {
            $conf = new Contract();
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
        $contract = $this->get($id)->first();
        if (!$contract) {
            if (!$contract) throw new Exception("[" . $id . "] дугаартай бүртгэл олдсонгүй!");
        }
        $contract->statusid = -1;
        $contract->updated_at = Carbon::now();
        $contract->updated_by = auth()->user()->userid;
        $contract->save();
    }
}
