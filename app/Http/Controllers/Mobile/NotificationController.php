<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationCollection;
use App\Models\CustNotification;
use App\Models\Notification;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function getNotifications(Request $request)
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
        $data = CustNotification::with('notification')->where('cust_userid', auth()->user()->userid)->orderBy('created_at', 'desc');
        $data = $this->allServiceList($data, $validated);
        $data = new NotificationCollection($data);
        return response()->json($data);
    }

    public function updateRead(Request $request)
    {
        $validated = $this->validate($request, [
            'id' => 'required',
        ]);
        $notif = CustNotification::where('cust_userid', auth()->user()->userid)->where('id', $validated['id'])->first();
        if ($notif) {
            $notif->is_read = 1;
            $notif->save();
        }
    }

    public function getUnreadCount()
    {
        $notif = CustNotification::where('cust_userid', auth()->user()->userid)->where('is_read', 0)->count();
        return response()->json(['unread' => $notif]);
    }

    public function sendNotification(Request $request)
    {
        $validated = $this->validate($request, [
            'title' => 'required',
            'description' => 'required'
        ]);
        $service = new NotificationService();
        $mnotif = $service->createMainNotif(
            [
                'title' => $validated['title'],
                'description' => $validated['description'],
                'instid' => 1,
                'is_all' => 0
            ]
        );
        $service->store([
            'cust_userid' => auth()->user()->userid,
            'notification_id' => $mnotif->id,
        ]);
        $service->sendNotification($validated['title'], $validated['description'], [auth()->user()->device_token]);
    }
}
