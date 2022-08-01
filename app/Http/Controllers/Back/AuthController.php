<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Jobs\InquiryEmailNotificationJob;
use App\Models\InstUser;
use App\Models\UserAccessToken;
use App\Services\DicPassPolicyService;
use App\Services\GoogleAuthenticator\GoogleAuthenticator;
use App\Services\LoginActivityService;
use App\Services\LoginCofirmService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

class AuthController extends Controller
{

    public function loginWithGoogle(Request $request)
    {
        $validated = $this->validate($request, [
            'username' => 'required|max:50',
            'password' => 'required',
            // 'secret'=>'required',
            'code' => 'required',
        ]);
        $googleAuth = new GoogleAuthenticator();
        $validated['username'] = Str::lower($validated['username']);
        $user = InstUser::where('email', $validated['username'])->where('status', '<>', '-1')->first();
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
        ]);
        $validated['username'] = Str::lower($validated['username']);
        $user = InstUser::where('email', $validated['username'])->where('status', '<>', '-1')->first();
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
            // $validated['password'] = $passpolicy->safeDecrypt($validated['password']);
            // return $validated['password'];
            if (Hash::check($validated['password'], $userPassword)) {
                //generate token
                $token = sha1(mt_rand(1, 90000)) . sha1(mt_rand(1, 90000));

                // Баталгаажсан төхөөрөмжөөс хандаж байгаа эсэх.
                try {
                    $loginConfirm = new LoginCofirmService();
                    $loginConfirm->checkConfirmDevice($user, 0);
                } catch (Exception $e) {
                    return response()->json($e->getMessage(), 500);
                }

                //insert token
                UserAccessToken::create([
                    'userid' => $user->userid,
                    'name' => 'login',
                    'token' => $token,
                    'abilities' => '',
                    'last_used_at' => Carbon::now(),
                    'created_at' => Carbon::now(),
                    'updated_at' => null,
                    'channel' => 'WEB'
                ]);

                //reset wrong pass count
                $user->passwrong = 0;
                $user->save();

                // Google authenticator идэвхжүүлж логин хийгдсэн эсэх
                if (!$checkGoogleAuth && $user->use_google_auth == "1") {
                    return response()->json(['use_google_auth' => 1, 'google_auth_key' => true]);
                }

                // Login хийгдсэн түүх хадгална.
                $activityLog = new LoginActivityService();
                $activityLog->store($request, $user, 0);

                $perms = [];
                // return response()->json(['user' => $user->activeRoles]);
                foreach ($user->activeRoles as $userRole) {
                    // return response()->json(['user' => $userRole]);
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
                    'instid' => $user->instid,
                    'branch' => $user->branch,
                    'isadmin' => $user->isadmin,
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
            'instid' => $user->instid,
            'isadmin' => $user->isadmin,
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
        return $activityLog->getUserLoginLog(auth()->user()->userid, 0);
    }

    public function confirmDevice(Request $request, $token)
    {
        $loginConf = new LoginCofirmService();
        $host = env('WEB_URL');
        return $loginConf->confirmDevicePage($request, $token, $host);
    }
}
