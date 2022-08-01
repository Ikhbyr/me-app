<?php

namespace App\Services;

use App\Models\ChangeLog;
use Carbon\Carbon;
class ChangeLogService{
    public function list($userid = "", $startdate = "", $enddate = "", $search = ""){
        $result = ChangeLog::select(['id', 'user_id', 'event', 'auditable_type', 'auditable_id', 'url', 'ip_address', 'user_agent', 'created_at']);
        if ($startdate){
            $start = Carbon::createFromFormat("Y-m-d H:i:s", $startdate." 00:00:00");
            $result->where('created_at', '>=', $start);
        }
        if ($enddate){
            $end = Carbon::createFromFormat("Y-m-d H:i:s", $enddate." 00:00:00")->addDay(1);
            $result->where('created_at', '<', $end);
        }
        if ($userid){
            $result->where('user_id', $userid);
        }
        if ($search){
            $result->where(function ($q) use ($search) { $q->where('old_values', 'like', "%".$search."%")->orWhere('new_values', 'like', "%".$search."%"); });
        }
        $result->orderBy('id');
        return $result;
    }

    public function get($id){
        return $this->list()->select(['id', 'user_id', 'event', 'auditable_type', 'auditable_id', 'url', 'ip_address', 'user_agent', 'created_at', 'old_values', 'new_values'])->where('id', $id);
    }
}
