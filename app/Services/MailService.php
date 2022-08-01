<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;

class MailService
{
    public static function send($toAddress, $subject, $body)
    {
        Mail::send([], [], function ($message) use ($toAddress, $subject, $body) {
            $message->to($toAddress)
                ->subject($subject)
                ->setBody($body, 'text/html');
        });
        // Mail::raw($body, function ($message) use ($toAddress, $subject) {
        //     $message->to($toAddress)
        //         ->subject($subject);
        // });
    }

    public static function sendMail($toAddress, $template, $data, $subject)
    {
        // $data = array('content'=>$datas);
        $data = array('data' => $data);
        Mail::send($template, $data, function ($message) use ($toAddress, $subject) {
            $message->to($toAddress);
            $message->subject($subject);
        });
    }
}
