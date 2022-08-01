<?php

namespace App\Services;

use App\Http\Controllers\Controller;
use App\Jobs\InquiryEmailNotificationJob;
use App\Models\InstUser;
use App\Services\GoogleAuthenticator\GoogleAuthenticator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Queue;
use Illuminate\Database\QueryException;

class InstUserService extends Controller
{
    public function getList($request, $users)
    {
        $validated = $this->validate($request, [
            'filters' => 'nullable|array',
            'filters.*.field' => 'required|max:60',
            'filters.*.value' => 'nullable|max:60',
            'filters.*.cond' => 'nullable|max:10',
            'orders' => 'nullable|array',
            'orders.*.field' => 'required|max:60',
            'orders.*.dir' => 'nullable|max:5',
            'perPage' => 'nullable|numeric',
            'page' => 'nullable|numeric',
            'withRoles' => 'nullable|max:1',
            'withBranch' => 'nullable|max:1',
            'withInst' => 'nullable|max:1',
        ]);
        $withs = [];
        if (@$validated['withRoles'] == 1) {
            $withs[] = "roles";
            $withs[] = "roles.role";
        }
        if (@$validated['withInst'] == 1) {
            $withs[] = "inst:id,instname,instnameeng";
        }
        if (@$validated['withBranch'] == 1) {
            $withs[] = "branch:id,branchname,branchno";
        }
        $users = $users->with($withs);

        $users = $this->applyFilters($users, @$validated['filters']);
        $users = $this->applyOrders($users, @$validated['orders']);
        $users = $this->applyPaginate($users, @$validated['perPage'], @$validated['page']);
        return response()->json($users);
    }

    public function store($request, $isadmin)
    {
        $validated = $this->validate($request, [
            'email' => 'required|max:60',
            'instid' => 'required',
            'phoneuser' => 'max:60',
            'registernum' => 'required|max:18',
            'isadmin' => 'nullable|max:1',
            'firstname' => 'required|max:50',
            'lastname' => 'required|max:50',
            // 'password' => 'required',
            // 'confirm_password' => 'required',
            'branchid' => 'nullable|numeric',
            'status' => 'nullable|numeric|digits_between:1,3',
            'roles' => 'nullable|array',
            'roles.*.roleid' => 'required|max:10',
            'roles.*.statusid' => 'required|numeric|max:1',
            'roles.*.startdate' => 'nullable|date_format:Y-m-d',
            'roles.*.enddate' => 'nullable|date_format:Y-m-d'
        ]);
        $validated['email'] = Str::lower($validated['email']);
        $user = auth()->user();
        if ($user->instid != getOwnInstId()) {
            return response()->json("Та хандах эрхгүй байна.", 500);
        }

        $userExist = InstUser::where('email', $validated['email'])->where('status', '<>', '-1')->first();

        if ($userExist && $userExist->status == '1') {
            return response()->json('Таны мэйл хаяг бүртгэлтэй байна.', 500);
        }

        $random_password = '#@1' . Str::random(8);
        $googleAuth = new GoogleAuthenticator();
        try {
            DB::beginTransaction();
            $data = array();
            $mail = '';
            $token = generateRandomString(50);
            $data['random_password'] = $random_password;
            $data['token'] = $token;
            $data['hostname'] = env('APP_URL');
            if ($userExist && $userExist->status == '0') {
                $userExist->passtoken = $token;
                $userExist->passtokendate = DB::raw('sysdate');
                $userExist->passtokenstatus = 1;
                $userExist->save();
                $data['firstname'] = $userExist->firstname;
                $mail = $userExist->email;
            } else {
                $user = new InstUser();
                $validated = array_change_key_case($validated);
                foreach ($user->fillable as $field) {
                    if (array_key_exists($field, $validated)) {
                        $user->$field = $validated[$field];
                    }
                }
                $user->google_auth_key = $googleAuth->generateSecret();
                $user->status = 0;
                $user->isadmin = $isadmin;
                $user->passtoken = $token;
                $user->password = Hash::make(me_hmac($random_password));
                $user->created_at = Carbon::now();
                $user->created_by = auth()->user() ? auth()->user()->userid : 1;
                $user->save();

                if (array_key_exists('roles', $validated)) {
                    if ($isadmin == '1') {
                        $user->setRolesAdmin($validated['roles']);
                    } else {
                        $user->setRoles($validated['roles']);
                    }
                }

                $data['firstname'] = $user->firstname;
                $user->update(['passtoken' => $token, 'passtokendate' => DB::raw('sysdate'), 'passtokenstatus' => 1]);
                $mail = $user->email;
            }

            $email = [
                "to" => $mail,
                "subject" => "Бүртгэл амжилттай хийгдлээ.",
                "data" => $data,
                "template" => "mail.mailRegister"
            ];
            Queue::push(new InquiryEmailNotificationJob($email));
            DB::commit();
            return response()->json('Таны бүртгэл амжилттай хийгдлээ. Бүртгүүлсэн мэйл хаягт баталгаажуулах линк илгээсэн. Бүртгэл баталгаажсны дараа системд нэвтрэхийг анхаарна уу.', 200);
        } catch (QueryException $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 500);
        }
    }

    public function show($request, $user)
    {
        try {
            if (@$request['withRoles'] == 1) {
                $user = $user->with(['roles', 'roles.role']);
            }
            $user = $user->first();
            return response()->json($user);
        } catch (QueryException $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function update($request, $isadmin)
    {
        $validated = $this->validate($request, [
            'userid' => 'required',
            'phoneuser' => 'max:60',
            'registernum' => 'required|max:18',
            'isadmin' => 'nullable|max:1',
            'firstname' => 'required|max:50',
            'lastname' => 'required|max:50',
            'branchid' => 'nullable|numeric',
            'roles' => 'nullable|array',
            'roles.*.roleid' => 'required|max:10',
            'roles.*.statusid' => 'required|numeric|max:1',
            'roles.*.startdate' => 'nullable|date_format:Y-m-d',
            'roles.*.enddate' => 'nullable|date_format:Y-m-d',
        ]);

        $user = auth()->user();
        if ($user->instid != getOwnInstId()) {
            return response()->json("Та хандах эрхгүй байна.", 500);
        }

        try {
            DB::beginTransaction();
            $user = InstUser::with(['roles'])->where('userid', $validated['userid'])->where('status', '<>', '-1')->first();
            if (!$user) {
                return response()->json('User not found!', 404);
            }

            if ($user->isadmin == '1') {
                if ($isadmin != '1') {
                    return response()->json('Та энэ үйлдлийг хийх эрхгүй байна.', 500);
                }
            }

            $validated = array_change_key_case($validated);
            foreach ($user->fillable as $field) {
                if (array_key_exists($field, $validated)) {
                    $user->$field = $validated[$field];
                }
            }
            $user['updated_at'] = Carbon::now();
            $user['updated_by'] = auth()->user()  ? auth()->user()->userid : 1;

            $user->save();

            if (array_key_exists('roles', $validated)) {
                if (array_key_exists('roles', $validated)) {
                    if ($isadmin == '1') {
                        $user->setRolesAdmin($validated['roles']);
                    } else {
                        $user->setRoles($validated['roles']);
                    }
                }
            }
            DB::commit();
            $user = InstUser::with(['roles'])->where('userid', $user->userid)->first();
            return response()->json($user, 200);
        } catch (QueryException $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 500);
        }
    }
}
