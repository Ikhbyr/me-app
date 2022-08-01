<?php

namespace App\Services;

use App\Models\InstCustConn;
use Carbon\Carbon;
use Exception;

class InstCustConnService
{
    public function connect($instid, $cust_userid)
    {
        $conncust = InstCustConn::where('inst_id', $instid)->where('cust_user_userid', $cust_userid)->where('statusid', 1)->first();
        if (!$conncust) {
            $conncust = new InstCustConn();
            $conncust->inst_id = $instid;
            $conncust->cust_user_userid = $cust_userid;
            $conncust->created_at = Carbon::now();
            $conncust->created_by = auth()->user() ? auth()->user()->userid : 1;
            $conncust->statusid = 1;
            $conncust->save();
        }
    }

    public function disConnect($instid, $cust_userid)
    {
        $conncust = InstCustConn::where('inst_id', $instid)->where('cust_user_userid', $cust_userid)->where('statusid', 1)->first();
        if ($conncust) {
            $conncust->statusid = -1;
            $conncust->save();
        }
    }

    /**
     * Тухайн байгууллага дээр бүртгэлтэй эсэх
     *
     * @param  mixed $instid байгууллагын бүртгэлийн дугаар
     * @param  mixed $cust_userid хэрэглэгчийн дугаар
     * @return boolean
     */
    public function isConnect($instid, $cust_userid) {
        $conncust = InstCustConn::where('inst_id', $instid)->where('cust_user_userid', $cust_userid)->where('statusid', 1)->first();
        if ($conncust) {
            return true;
        } else {
            return false;
        }
    }
}
