<?php

namespace App\Services;

use App\Models\Provider;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class ProviderService
{
    public function list($withs = [], $deleted = false)
    {
        $user = auth()->user();
        $result = Provider::with($withs)->where('instid', $user->instid)->where('statusid', '<>', -1);
        return $result;
    }

    public function get($id, $withs = [])
    {
        return $this->list($withs)->where('id', $id);
    }

    public function update($id, $data)
    {
        $provider = $this->get($id)->first();
        $user = auth()->user();
        if (!$provider) throw new Exception("Provider [" . $id . "] not found!");

        foreach ($provider->fillable as $field) {
            if (array_key_exists($field, $data)) {
                $provider->$field = $data[$field];
            }
        }
        $provider->updated_at = Carbon::now();
        $provider->updated_by = $user->userid;
        $provider->save();
        return $provider;
    }

    public function store($data)
    {
        $user = auth()->user();
        try {
            $provider = new Provider();
            foreach ($provider->fillable as $field) {
                if (array_key_exists($field, $data)) {
                    $provider->$field = $data[$field];
                }
            }
            $provider->statusid = 1;
            $provider->instid = $user->instid;
            $provider->created_at = Carbon::now();
            $provider->created_by = $user->userid;
            $provider->save();

            return $provider;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete($id)
    {
        $provider = $this->get($id)->first();
        if (!$provider) {
            throw new Exception("Provider [" . $id . "] not found!");
        }
        $provider->statusid = -1;
        $provider->updated_at = Carbon::now();
        $provider->updated_by = auth()->user()->userid;
        $provider->save();
        return $provider;
    }

    public function restore($id)
    {
        $provider = Provider::where('statusid', -1)->first();
        if (!$provider) {
            throw new Exception("Provider [" . $id . "] not deleted!");
        }
        $provider->statusid = 1;
        $provider->updated_at = Carbon::now();
        $provider->updated_by = auth()->user()->userid;
        $provider->save();
        return $provider;
    }

    public function getByName($name)
    {
        return $this->list()->where('name', $name)->first();
    }
}
