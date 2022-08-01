<?php

namespace App\Services;

use App\AuditResolvers\IpAddressResolver;
use App\Models\LoginActivityLog;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class LoginActivityService
{

    public function list($withs = [], $deleted = false)
    {
        $result = LoginActivityLog::with($withs);
        return $result;
    }

    public function get($id, $withs = [])
    {
        return $this->list($withs)->where('id', $id);
    }

    public function store($request, $user, $channel, $validated = [])
    {

        try {
            $api = new LoginActivityLog();
            $api->userid = $user->userid;
            $api->agent = $request->header('User-Agent');
            $api->device_ip = IpAddressResolver::resolve();
            $api->statusid = 1;
            $api->channel = $channel;
            $api->deviceid = $validated['deviceid'] ?? null;
            $api->devicename = $validated['devicename'] ?? null;
            $api->created_at = Carbon::now();
            $api->save();

            return $api;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getUserLoginLog($userid, $channel)
    {
        return LoginActivityLog::where('userid', $userid)->where('channel', $channel)->orderBy('created_at', 'desc')->paginate(10);
    }
}
