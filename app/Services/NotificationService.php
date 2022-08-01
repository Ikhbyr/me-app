<?php

namespace App\Services;

use App\Models\CustNotification;
use App\Models\CustUser;
use App\Models\Notification;
use Carbon\Carbon;

class NotificationService
{
    public function store($data)
    {
        $user = auth()->user();
        $notif = new CustNotification();
        foreach ($notif->fillable as $field) {
            if (array_key_exists($field, $data)) {
                $notif->$field = $data[$field];
            }
        }
        $notif->is_read = 0;
        $notif->created_at = Carbon::now();
        $notif->created_by = $user ? $user->userid : 1;
        $notif->save();
    }

    public function createMainNotif($data)
    {
        $user = auth()->user();
        $notif = new Notification();
        $notif->title = $data['title'];
        $notif->description = $data['description'];
        $notif->instid = $data['instid'];
        $notif->is_all = $data['is_all'];
        $notif->created_at = Carbon::now();
        $notif->created_by = $user ? $user->userid : 1;
        $notif->save();
        return $notif;
    }

    public function sendNotification($title, $message, $firebaseToken)
    {
        $SERVER_API_KEY = env('FIREBASE_SERVER_KEY');

        $data = [
            "registration_ids" => $firebaseToken,
            "notification" => [
                "title" => $title,
                "body" => $message,
            ]
        ];
        $dataString = json_encode($data);

        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
        curl_exec($ch);
    }
}
