<?php

namespace App\Services;

use App\Models\ConnConf;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class ConnConfService{
    public function list($withs = [], $deleted = false){
        $user = auth()->user();
        $result = ConnConf::with($withs)->where('statusid', '<>', -1);
        return $result;
    }

    public function get($id, $withs = []){
        return $this->list( $withs)->where('id', $id);
    }

    public function update($id, $data){
        $conf = $this->get($id)->first();
        $user = auth()->user();
        if (!$conf) throw new Exception("ConnConf [".$id."] not found!");

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
            $conf = new ConnConf();
            foreach ($conf->fillable as $field){
                if (array_key_exists($field, $data)){
                    $conf->$field = $data[$field];
                }
            }
            $conf->statusid = 1;
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
            throw new Exception("ConnConf [".$id."] not found!");
        }
        $conf->statusid = -1;
        $conf->updated_at = Carbon::now();
        $conf->updated_by = auth()->user()->userid;
        $conf->save();
        return $conf;
    }

    public function restore($id){
        $conf = ConnConf::where('statusid', -1)->first();
        if (!$conf) {
            throw new Exception("ConnConf [".$id."] not deleted!");
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
