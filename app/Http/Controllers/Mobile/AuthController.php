<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Jobs\InquiryEmailNotificationJob;
use App\Models\CustUser;
use App\Models\UserAccessToken;
use App\Services\DicPassPolicyService;
use App\Services\GoogleAuthenticator\GoogleAuthenticator;
use App\Services\LoginActivityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

class AuthController extends Controller
{

    public function loginWithGoogle(Request $request, $office = "BACK")
    {
        $validated = $this->validate($request, [
            'username' => 'required|max:50',
            'password' => 'required',
            // 'secret'=>'required',
            'code' => 'required',
            'deviceid' => 'nullable',
            'devicename' => 'nullable'
        ]);
        $validated['username'] = Str::lower($validated['username']);
        $googleAuth = new GoogleAuthenticator();
        $user = CustUser::where('email', $validated['username'])->where('status', '<>', '-1')->first();
        if ($googleAuth->checkCode($user->google_auth_key, $validated['code'])) {
            return $this->login($request, true);
        } else {
            return response()->json('Баталгаажуулах код буруу байна.', 500);
        }
    }

    public function login(Request $request, $checkGoogleAuth = false)
    {
        $validated = $this->validate($request, [
            'username' => 'required|max:50',
            'password' => 'required',
            'deviceid' => 'nullable',
            'devicename' => 'nullable',
            'pushToken' => 'nullable',
        ]);
        $validated['username'] = Str::lower($validated['username']);
        $user = CustUser::where('email', $validated['username'])->where('status', '<>', '-1')->first();
        if ($user) {

            $passpolicy = new DicPassPolicyService();
            // $limit = env('WRONG_PASS_COUNT', 5);
            $limit = (int) $passpolicy->getPolicyValue("PassWrongTimes");
            if ($user->passwrong > $limit) {
                if ($user->passwrong == $limit + 1) {
                    return response()->json('Таны эрх блоклогдлоо.', 500);
                }
                return response()->json('Блоклогдсон байна.', 500);
            }
            $userPassword = $user->password;
            if (Hash::check($validated['password'], $userPassword)) {
                //generate token
                $token = sha1(mt_rand(1, 90000)) . sha1(mt_rand(1, 90000));

                //insert token
                UserAccessToken::create([
                    'userid' => $user->userid,
                    'name' => 'login',
                    'token' => $token,
                    'abilities' => '',
                    'last_used_at' => Carbon::now(),
                    'created_at' => Carbon::now(),
                    'updated_at' => null,
                    'channel' => 'APP'
                ]);

                //reset wrong pass count
                $user->passwrong = 0;
                $user->device_token = $validated['pushToken'] ?? "";
                $user->save();

                // Google authenticator идэвхжүүлж логин хийгдсэн эсэх
                if (!$checkGoogleAuth && $user->use_google_auth == "1") {
                    return response()->json(['use_google_auth' => 1, 'google_auth_key' => true]);
                }

                // Login хийгдсэн түүх хадгална.
                $activityLog = new LoginActivityService();
                $activityLog->store($request, $user, 1, $validated);

                $perms = [];
                // foreach ($user->activeRoles as $userRole) {
                //     if ($userRole->role && $userRole->role->perms) {
                //         foreach ($userRole->role->perms as $perm) {
                //             $perms[$perm->permid] = 1;
                //         }
                //     }
                // }

                $userInfo = [
                    'userid' => $user->userid,
                    'email' => $user->email,
                    'phone' => $user->phoneuser,
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'branch' => $user->branch,
                    'perms' => $perms,
                    'mustchgpass' => $user->mustchgpass
                ];

                return response()->json(['user' => $userInfo, 'token' => $token]);
            }

            //increase wrong pass count
            $user->passwrong = $user->passwrong + 1;
            $user->save();

            return response()->json('Нэвтрэх мэдээлэл буруу байна.', 500);
        }
        return response()->json('Нэвтрэх мэдээлэл буруу байна.', 500);
    }

    public function logout(Request $request)
    {
        $token = $request->bearerToken();
        $user = auth()->user();
        if ($user) {
            $user->tokens()->where('token', $token)->delete();
            return response()->json('Successfully logged out!', 200);
        }
        return response()->json('Session not found!', 404);
    }

    public function check(Request $request)
    {
        $user = auth()->user();
        if (!$user) return null;
        $perms = [];
        foreach ($user->activeRoles as $userRole) {
            if ($userRole->role && $userRole->role->perms) {
                foreach ($userRole->role->perms as $perm) {
                    $perms[$perm->permid] = 1;
                }
            }
        }
        $userInfo = [
            'userid' => $user->userid,
            'email' => $user->email,
            'phone' => $user->phoneuser,
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'branch' => $user->branch,
            'perms' => $perms,
        ];
        return $userInfo;
        // $token =  $request->bearerToken();
        // $user = User::whereHas('tokens', function($q) use ($token) {$q->where('token', $token); })->first();
        // if ($user){
        //     return response()->json($user);
        // }
        // return response()->json('Session not found!', 404);
    }

    public function getLoginActivityLog(Request $request)
    {
        // return IpAddressResolver::resolve();
        $activityLog = new LoginActivityService();
        return $activityLog->getUserLoginLog(auth()->user()->userid, 1);
    }
}
