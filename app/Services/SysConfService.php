<?php

namespace App\Services;

use App\Models\SysConf;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class SysConfService{
    public function list($withs = [], $deleted = false){
        $user = auth()->user();
        if ($user->isadmin != '1') {
            $result = SysConf::with($withs)->where('statusid', '<>', -1)->where('instid', $user->instid);
        } else {
            $result = SysConf::with($withs)->where('statusid', '<>', -1);
        }
        return $result;
    }

    public function get($id, $withs = []){
        return $this->list( $withs)->where('id', $id);
    }

    public function update($id, $data){
        $conf = $this->get($id)->first();
        $user = auth()->user();
        if (!$conf) throw new Exception("SysConf [".$id."] not found!");

        foreach ($conf->fillable as $field){
            if (array_key_exists($field, $data)){
                $conf->$field = $data[$field];
            }
        }
        $conf->updated_at = Carbon::now();
        $conf->updated_by = $user->userid;
        $conf->save();
        return $conf;
    }

    public function store($data){
        $user = auth()->user();
        try{
            $conf = new SysConf();
            foreach ($conf->fillable as $field){
                if (array_key_exists($field, $data)){
                    $conf->$field = $data[$field];
                }
            }
            $conf->statusid = 1;
            $conf->instid = $user->instid;
            $conf->created_at = Carbon::now();
            $conf->created_by = $user->userid;
            $conf->save();

            return $conf;
        }
        catch(Exception $e){
            DB::rollBack();
            throw $e;
        }

    }

    public function delete($id){
        $conf = $this->get($id)->first();
        if (!$conf) {
            throw new Exception("SysConf [".$id."] not found!");
        }
        $conf->statusid = -1;
        $conf->updated_at = Carbon::now();
        $conf->updated_by = auth()->user()->userid;
        $conf->save();
        return $conf;
    }

    public function restore($id){
        $conf = SysConf::where('statusid', -1)->first();
        if (!$conf) {
            throw new Exception("SysConf [".$id."] not deleted!");
        }
        $conf->statusid = 1;
        $conf->updated_at = Carbon::now();
        $conf->updated_by = auth()->user()->userid;
        $conf->save();
        return $conf;
    }

    public function getByName($name){
        return $this->list()->where('name', $name)->first();
    }
}
