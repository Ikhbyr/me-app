<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Http\Resources\InstUserProfileResource;
use App\Jobs\InquiryEmailNotificationJob;
use App\Models\DicPassPolicy;
use App\Models\InstUser;
use App\Services\InstUserService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

class InstUserController extends Controller
{

    protected $service;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(InstUserService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        if ($user->isadmin == '1') {
            $users = InstUser::where('status', '<>', '-1')->where('isadmin', '<>', '1');
        } else {
            $users = InstUser::where('status', '<>', '-1')->where('isadmin', '<>', '1')->where('instid', $user->instid);
        }
        return $this->service->getList($request, $users);
    }

    public function getProfileFront(Request $request)
    {
        $validated = $this->validate($request, [
            'withRoles' => 'nullable|numeric',
        ]);
        $withs = [];
        if (@$validated['withRoles'] == 1) {
            $withs = ['roles', 'roles.role'];
        }
        $user = new InstUserProfileResource(InstUser::with($withs)
            ->where('userid', auth()->user()->userid)
            ->where('status', '<>', '-1')->first());
        return response()->json($user);
    }

    public function getadmin(Request $request)
    {
        $user = auth()->user();
        if ($user->instid != getOwnInstId()) {
            return response()->json("Та хандах эрхгүй байна.", 500);
        }
        $users = InstUser::where('status', '<>', '-1')->where('isadmin', '1');
        return $this->service->getList($request, $users);
    }

    public function storeAdmin(Request $request)
    {
        $user = auth()->user();
        if ($user->instid != getOwnInstId()) {
            return response()->json("Та хандах эрхгүй байна.", 500);
        }
        $request['instid'] = getOwnInstId();
        return $this->service->store($request, '1');
    }

    public function store(Request $request)
    {
        // $user = auth()->user();
        // if ($user->isadmin == '1') {
        //     return response()->json('Та мэдээллийг өөрчлөх эрхгүй байна.', 500);
        // }
        return $this->service->store($request, '0');
    }

    public function showadmin(Request $request)
    {
        $user = auth()->user();
        if ($user->instid != getOwnInstId()) {
            return response()->json("Та хандах эрхгүй байна.", 500);
        }
        $validated = $this->validate($request, [
            'id' => 'required|numeric',
        ]);
        $user = InstUser::where('userid', $validated['id'])->where('status', '<>', '-1')->where('isadmin', '1');
        return $this->service->show($request, $user);
    }

    public function show(Request $request)
    {
        $validated = $this->validate($request, [
            'id' => 'required|numeric',
        ]);
        $user = auth()->user();
        if ($user->isadmin == '1') {
            $user = InstUser::where('userid', $validated['id'])->where('status', '<>', '-1')->where('isadmin', '<>', '1');
        } else {
            $user = InstUser::where('userid', $validated['id'])->where('status', '<>', '-1')->where('isadmin', '<>', '1')->where('instid', $user->instid);
        }
        return $this->service->show($request, $user);
    }

    public function updateAdmin(Request $request)
    {
        $user = auth()->user();
        if ($user->instid != getOwnInstId()) {
            return response()->json("Та хандах эрхгүй байна.", 500);
        }
        return $this->service->update($request, '1');
    }

    public function update(Request $request)
    {
        // $user = auth()->user();
        // if ($user->isadmin == '1') {
        //     return response()->json('Та мэдээллийг өөрчлөх эрхгүй байна.', 500);
        // }
        return $this->service->update($request, '0');
    }

    public function destroy(Request $request)
    {
        $validated = $this->validate($request, [
            'id' => 'required|numeric',
        ]);
        try {
            $user = auth()->user();
            if ($user->isadmin == '1') {
                $user = InstUser::where('userid', $validated['id'])->where('status', '<>', '-1')->where('isadmin', '<>', '1')->first();
            } else {
                $user = InstUser::where('userid', $validated['id'])->where('status', '<>', '-1')->where('instid', $user->instid)->where('isadmin', '<>', '1')->first();
            }

            if (!$user) {
                return response()->json('Хэрэглэгч олдсонгүй!', 404);
            }

            $user->update(['status' => -1, 'lastupdateuser' => auth()->user()->userid, 'lastupdate' => Carbon::now()]);

            return response()->json('User deleted', 200);
        } catch (QueryException $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function destroyAdmin(Request $request)
    {
        $validated = $this->validate($request, [
            'id' => 'required|numeric',
        ]);
        $user = auth()->user();
        if ($user->instid != getOwnInstId()) {
            return response()->json("Та хандах эрхгүй байна.", 500);
        }
        try {
            $user = InstUser::where('userid', $validated['id'])->where('status', '<>', '-1')->where('isadmin', '1')->first();
            if (!$user) {
                return response()->json('User not found!', 404);
            }

            $user->update(['status' => -1, 'lastupdateuser' => auth()->user()->userid, 'lastupdate' => Carbon::now()]);

            return response()->json('User deleted', 200);
        } catch (QueryException $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function changePassword(Request $request)
    {

        $validated = $this->validate($request, [
            'userid' => 'required|numeric',
            'oldpassword' => 'required',
            'newpassword' => 'required',
            'newpassword2' => 'required',
        ]);

        // $passpolicy = new DicPassPolicyService();
        // $password = $passpolicy->safeDecrypt($validated['newpassword']);
        $password = $validated['newpassword'];
        // $passpolicy->checkPassPolicy($password);

        if ($validated['newpassword'] != $validated["newpassword2"]) {
            return response()->json('Passwords mismatch!', 500);
        }

        try {
            $user = InstUser::where('userid', $validated['userid'])->where('status', '<>', '-1')->first();
            if (!$user) {
                return response()->json('User not found!', 404);
            }
            if (!Hash::check($validated["oldpassword"], $user->password)) {
                return response()->json("Хуучин нууц үг таарахгүй байна!", 500);
            }
            $user->update([
                'password' => Hash::make($password),
                'updated_by' => auth()->user()->userid,
                'updated_at' => Carbon::now(),
                'password_changed_at' => Carbon::now(),
                'mustchgpass' => 0,
            ]);
            return response()->json('Password changed!', 200);
        } catch (QueryException $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function forgotPassword(Request $request)
    {

        $validated = $this->validate($request, [
            'email' => 'required|max:50',
        ]);

        // $hostname = env("APP_URL", "http://192.168.10.10:8005");
        $email = Str::lower($validated['email']);

        $data = array();
        $user = InstUser::select("INST_USER.*")
            ->selectRaw("CASE WHEN passtokendate IS NULL THEN 1 WHEN passtokenstatus = 1 AND passtokendate < sysdate - 1/24 THEN 2 ELSE 3 END tokenstatus")
            ->where("email", $email)->where('status', '<>', '-1')->first();
        $data['hostname'] = env('WEB_URL');
        if ($user) {
            // return $user->tokenstatus;
            try {
                if ($user->tokenstatus == "3" && !empty($user->passtoken)) {
                    $token = $user->passtoken;
                } else {
                    $token = generateRandomString(50);
                }
                $user->update(['passtoken' => $token, 'passtokendate' => DB::raw('sysdate'), 'passtokenstatus' => 1]);
                $data['token'] = $token;
                $email = [
                    "to" => $user->email,
                    "subject" => "ME систем. Forgotten password request",
                    "data" => $data,
                    "template" => "mail.forgotPassword"
                ];
                Queue::push(new InquiryEmailNotificationJob($email));

                // MailService::sendMail($user->email, "mail.forgotPassword", $data, "ME систем. Forgotten password request");
                return response()->json('Email sent!', 200);
            } catch (Exception $e) {
                return response()->json($e->getMessage(), 500);
            }
        }
        return response()->json('Уучлаарай. Та бүртгэлгүй байна!', 404);
    }

    public function resetPassword(Request $request)
    {
        $validated = $this->validate($request, [
            'token' => 'required|max:64',
            'password' => 'required',
        ]);
        $password = $validated['password'];
        $token = $validated['token'];
        $user = InstUser::select("INST_USER.*")->selectRaw("CASE WHEN passtokendate IS NULL THEN 1 WHEN passtokenstatus = 0 THEN 2 WHEN passtokendate < (SELECT SYSDATE - OPTIONVALUE/60/24 FROM DIC_PASSPOLICY WHERE OPTIONNAME = 'TOKEN_LIFETIME') THEN 3 ELSE 4 END tokenstatus")->where("passtoken", $token)->first();
        if ($user) {
            try {
                if ($user->tokenstatus == "4") {
                    try {
                        DB::beginTransaction();
                        $user->changePassword($password);
                        $user->update([
                            'passwrong' => 0,
                            'updated_at' => Carbon::now(),
                            'password_changed_at' => Carbon::now(),
                            'updated_by' => $user->userid,
                            'mustchgpass' => 0,
                        ]);
                        DB::commit();
                        return response()->json('Password changed!', 200);
                    } catch (Exception $e) {
                        DB::rollBack();
                        return response()->json($e->getMessage(), 500);
                    }
                } else {
                    // $msg = $user->tokenstatus == "1" ? "" :
                    switch ($user->tokenstatus) {
                        case "1":
                            $msg = "Reset request not created!";
                            break;
                        case "2":
                            $msg = "Password changed previously by this token";
                            break;
                        case "3":
                            $msg = "Token expired!";
                            break;
                    }
                    return response()->json($msg, 500);
                }
            } catch (Exception $e) {
                return response()->json($e->getMessage(), 500);
            }
        }
        return response()->json('User not found!', 404);
    }

    public function getPassPolicy(Request $request)
    {
        return DicPassPolicy::get();
    }

    public function confirmRegister(Request $request, $token)
    {
        $user = InstUser::where("passtoken", $token)->first();
        $host = env('WEB_URL');
        if ($user) {
            $user->status = 1;
            $user->passtoken = '';
            $user['updated_at'] = Carbon::now();
            $user['updated_by'] = $user->userid;
            $user->save();
            $msg = 'Таны бүртгэл баталгаажлаа.';
            // return response()->json('Таны бүртгэл баталгаажлаа.', 200);
        } else {
            $msg = 'Таны токен буруу байна!';
            // return response()->json('Таны токен буруу байна!', 404);
        }
        return view('pages.confirmRegister', compact('host', 'msg'));
    }
}
