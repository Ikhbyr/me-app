<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\CustNotification;
use App\Models\CustUser;
use App\Models\InstCustConn;
use App\Models\Notification;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function getSendNotification(Request $request)
    {
        $validated = $this->validate($request, [
            'filters' => 'nullable|array',
            'filters.*.field' => 'required|max:60',
            'filters.*.value' => 'nullable|max:60',
            'filters.*.cond' => 'nullable|max:10',
            'orders' => 'nullable|array',
            'orders.*.field' => 'nullable|max:60',
            'orders.*.dir' => 'nullable|max:5',
            'perPage' => 'nullable|numeric',
            'page' => 'nullable|numeric',
        ]);

        $trans = Notification::select('title', 'created_by', 'created_at', 'id')->where('instid', auth()->user()->instid);
        $trans = $this->allServiceList($trans, $validated);
        return response()->json($trans);
    }

    public function getSentNotifUser(Request $request)
    {
        $validated = $this->validate($request, [
            'id' => 'required',
            'filters' => 'nullable|array',
            'filters.*.field' => 'required|max:60',
            'filters.*.value' => 'nullable|max:60',
            'filters.*.cond' => 'nullable|max:10',
            'orders' => 'nullable|array',
            'orders.*.field' => 'nullable|max:60',
            'orders.*.dir' => 'nullable|max:5',
            'perPage' => 'nullable|numeric',
            'page' => 'nullable|numeric',
        ]);
        $trans = Notification::where('id', $validated['id'])->where('instid', auth()->user()->instid)->first();
        if (empty($trans)) {
            return response()->json("[" . $validated['id'] . "] дугаартай бүртгэлийн мэдээлэл олдсонгүй.", 500);
        }
        $custNotif = CustNotification::with('user:userid,firstname,lastname')->where('notification_id', $validated['id'])->get();
        return response()->json(['data' => $custNotif]);
    }

    public function getDetailNotification(Request $request)
    {
        $validated = $this->validate($request, [
            'id' => 'required|numeric'
        ]);

        $trans = Notification::where('instid', auth()->user()->instid)->where('id', $validated['id'])->first();
        return response()->json($trans);
    }

    public function sendNotification(Request $request)
    {
        $validated = $this->validate($request, [
            'title' => 'required|string|max:100',
            'description' => 'required|string|max:500',
            'usersid' => 'nullable|array', // [1,2]
            'is_all' => 'nullable|boolean',
        ]);

        $userids = [];
        $user = auth()->user();
        if (@$validated['is_all']) {
            $validated['is_all'] = 1;
            if ($user->isadmin == '1') {
                $users = CustUser::select('device_token', 'userid')->where('status', 1)->whereNotNull('device_token')->get();
            } else {
                $users = InstCustConn::select('cust_user.device_token', 'cust_user.userid')->where('inst_id', $user->instid)->where('statusid', 1)
                    ->leftJoin('cust_user', 'inst_cust_conn.cust_user_userid', '=', 'cust_user.userid')
                    ->whereNotNull('cust_user.device_token')->get();
            }
        } else {
            $validated['is_all'] = 0;
            $userids = $validated['usersid'] ?? [];
            if ($user->isadmin == '1') {
                $users = CustUser::select('device_token', 'userid')->where('status', 1)->whereNotNull('device_token')->whereIn('userid', $userids)->get();
            } else {
                $users = InstCustConn::select('cust_user.device_token', 'cust_user.userid')->where('inst_id', $user->instid)->where('statusid', 1)
                    ->leftJoin('cust_user', 'inst_cust_conn.cust_user_userid', '=', 'cust_user.userid')
                    ->whereNotNull('cust_user.device_token')->whereIn('userid', $userids)->get();
            }
        }
        $userids = [];
        $service = new NotificationService();
        $notif = $service->createMainNotif([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'is_all' => $validated['is_all'],
            'instid' => $user->instid,
        ]);
        $count = 0;
        foreach ($users as $cuser) {
            $userids[] = $cuser->device_token;
            $count = $count + 1;
            $service->store([
                'cust_userid' => $cuser->userid,
                'notification_id' => $notif->id,
            ]);
            // Мэдэгдлийг багцалж илгээх
            if ($count == 50) {
                $service->sendNotification($validated['title'], $validated['description'], $userids);
                $userids = [];
                $count = 0;
            }
        }
        // Багцалж илгээхэд үлдсэн хүмүүсрүү илгээх
        if(!empty($userids)) {
            $service->sendNotification($validated['title'], $validated['description'], $userids);
        }
    }
}
