<?php

namespace App\Services;

use App\Models\CustUserAccount;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustUserAccountService
{
    public function list($withs = [], $deleted = false)
    {
        $result = CustUserAccount::with($withs)->where('statusid', '<>', -1);
        return $result;
    }

    public function get($id, $withs = [])
    {
        return $this->list($withs)->where('id', $id);
    }

    public function getAcnt($acnt_code, $withs = [])
    {
        return $this->list($withs)->where('acnt_code', $acnt_code)->first();
    }

    public function store($data)
    {
        $user = auth()->user();
        try {
            if (!empty($this->getAcnt($data['acnt_code']))) {
                return getSystemResp($data['acnt_code'] . ' данс бүртгэлтэй байна.', 500);
            }
            $custAccount = new CustUserAccount();

            $custAccount->acnt_code = $data['acnt_code'];
            $custAccount->acnt_name = $data['acnt_name'];
            $custAccount->bank_code = $data['bank_code'];
            $custAccount->token = $data['token'] ?? null;
            $custAccount->cust_user_id = $user->userid;
            $custAccount->statusid = 1;
            $custAccount->created_at = Carbon::now();
            $custAccount->created_by = $user->userid;
            $custAccount->save();

            return getSystemResp($custAccount);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
            return getSystemResp('Server error.', 500);
        }
    }

    public function delete($acnt)
    {
        $custAccount = CustUserAccount::where('acnt_code', $acnt)
            ->where('cust_user_id', auth()->user()->userid)
            ->where('statusid', '<>', -1)
            ->first();
        if (!$custAccount) {
            return getSystemResp("Харилцагчийн [" . $acnt . "] данс олдсонгүй!", 500);
        }
        $custAccount->statusid = -1;
        $custAccount->updated_at = Carbon::now();
        $custAccount->updated_by = auth()->user()->userid;
        $custAccount->save();
    }

    public function confirmAccount($data)
    {
        $account = CustUserAccount::where('acnt_code', $data['acnt_code'])->where('token', $data['token'])->where('statusid', 1)->first();
        if (!$account) {
            return getSystemResp("Харилцагчийн данс баталгаажуулахад алдаа гарлаа.", 500);
        }

        $account->statusid = 1;
        $account->save();
        return getSystemResp("Харилцагчийн данс амжилттай баталгаажлаа.");
    }

    public function getConnAccount()
    {
        $user = auth()->user();
        $account = CustUserAccount::where('cust_user_id', $user->userid)->where('statusid', '<>', -1)->get();
        return getSystemResp($account);
    }
}
