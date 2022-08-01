<?php

namespace App\Services;

use App\AuditResolvers\IpAddressResolver;
use App\Jobs\InquiryEmailNotificationJob;
use App\Models\LoginConfirmDevice;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class LoginCofirmService
{
    public function list($withs = [], $deleted = false)
    {
        $result = LoginConfirmDevice::with($withs);
        return $result;
    }

    public function get($id, $withs = [])
    {
        return $this->list($withs)->where('id', $id);
    }

    public function store($data)
    {
        try {
            $api = new LoginConfirmDevice();
            foreach ($api->fillable as $field) {
                if (array_key_exists($field, $data)) {
                    $api->$field = $data[$field];
                }
            }
            $api->statusid = 1;
            $api->created_at = Carbon::now();
            $api->save();

            return $api;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function checkUserLoginDevice($userid, $ip, $channel)
    {
        return LoginConfirmDevice::where('userid', $userid)->where('ip', $ip)->where('channel', $channel)->where('statusid', '1')->first();
    }

    public function confirmDevice($token)
    {
        $log = LoginConfirmDevice::where('token', $token)->first();
        if ($log) {
            $log->is_confirm = '1';
            $log->save();
            return true;
        }
        return false;
    }

    public function checkConfirmDevice($user, $channel)
    {
        $passpolicy = new DicPassPolicyService();
        $token = sha1(mt_rand(1, 90000)) . sha1(mt_rand(1, 90000));
        $confirmDevice = $passpolicy->getPolicyValue("ConfirmDevice");
        if ($confirmDevice == '1' || $confirmDevice == 1) {
            $confDevice = $this->checkUserLoginDevice($user->userid, IpAddressResolver::resolve(), $channel);
            if (!$confDevice) {
                $confdata = array(
                    'ip' => IpAddressResolver::resolve(),
                    'is_confirm' => '0',
                    'userid' => $user->userid,
                    'token' => $token,
                    'channel' => $channel
                );
                $this->store($confdata);
                $this->sendMailConfirmDevice($user->email, $token);
                throw new Exception("Баталгаажаагүй төхөөрөмжөөс хандаж байна. Таны системд бүртгэлтэй цахим хаягт баталгаажуулах холбоос илгээсэн.");
            } else {
                if ($confDevice->is_confirm != '1') {
                    $confDevice->token = $token;
                    $confDevice->updated_at = Carbon::now();
                    $confDevice->save();
                    $this->sendMailConfirmDevice($user->email, $token);
                    throw new Exception("Баталгаажаагүй төхөөрөмжөөс хандаж байна. Таны системд бүртгэлтэй цахим хаягт баталгаажуулах холбоос илгээсэн.");
                }
            }
        }
    }

    public function sendMailConfirmDevice($mail, $token)
    {
        $hostname = env('WEB_URL').'/back-api';
        $email = [
            "to" => $mail,
            "subject" => "Төхөөрөмж баталгаажуулалт",
            "data" => [
                'token' => $token,
                'hostname' => $hostname,
                'ip' => IpAddressResolver::resolve()
            ],
            "template" => "mail.confirmDevice"
        ];
        Queue::push(new InquiryEmailNotificationJob($email));
    }

    public function confirmDevicePage($request, $token, $host)
    {
        // return $request->root();
        // return $request->path();
        $loginConf = new LoginCofirmService();
        $confDevice = $loginConf->confirmDevice($token);
        if ($confDevice) {
            return view('pages.confirmDevice', compact('host'));
        } else {
            return response()->json('Token not found!', 404);
        }
    }
}
