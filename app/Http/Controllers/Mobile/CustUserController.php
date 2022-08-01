<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Jobs\InquiryEmailNotificationJob;
use App\Models\Cust;
use App\Models\DicPassPolicy;
use App\Models\CustUser;
use App\Models\InstCustConn;
use App\Services\AcntService;
use App\Services\InstCustConnService;
use App\Services\UserService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Queue;

class CustUserController extends Controller
{
    protected $service;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
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
            'withInsts' => 'nullable|max:1',
            'withBranch' => 'nullable|max:1',
            'instid' => 'nullable|numeric',
        ]);

        $withs = [];
        $users = CustUser::where('status', '<>', '-1');
        if (@$validated['withRoles'] == 1) {
            $withs[] = "roles";
            $withs[] = "roles.role";
        }
        if (@$validated['withInsts'] == 1) {
            $withs[] = "insts";
        }

        $users = $users->with($withs);

        $users = $this->applyFilters($users, @$validated['filters']);
        $users = $this->applyOrders($users, @$validated['orders']);
        $users = $this->applyPaginate($users, @$validated['perPage'], @$validated['page']);
        return response()->json($users);
    }

    public function show(Request $request)
    {
        // $validated = $this->validate($request, [
        //     'userid' => 'required|numeric',
        // ]);
        try {
            $user = auth()->user();
            $user = CustUser::where('userid', $user->userid)->where('status', '<>', '-1')->first();
            if (!empty($user->photo_url)) {
                try {
                    $user['photo_base64'] = base64_encode(file_get_contents(env('APP_URL') . $user->photo_url));
                } catch (Exception $ex) {
                    $user['photo_base64'] = "";
                }
            }
            return response()->json($user);
        } catch (QueryException $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function getDetail(Request $request)
    {
        $validated = $this->validate($request, [
            'registernum' => 'required',
        ]);
        try {
            $user = CustUser::where('registernum', $validated['registernum'])->where('status', '<>', '-1');
            $user = $user->first();
            return response()->json($user);
        } catch (QueryException $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function update(Request $request)
    {
        $validated = $this->validate($request, [
            'phoneuser' => 'max:60',
            'firstname' => 'required|max:50',
            'lastname' => 'required|max:50',
            'userid' => 'required|numeric',
            'roles' => 'nullable|array',
            'address' => 'nullable|max:100',
            'photo_url' => 'nullable',
            'roles.*.roleid' => 'required|max:10',
            'roles.*.statusid' => 'required|numeric|max:1',
            'roles.*.startdate' => 'nullable|date_format:Y-m-d',
            'roles.*.enddate' => 'nullable|date_format:Y-m-d',
            'region' => 'nullable',
            'subregion' => 'nullable',
        ]);

        try {
            DB::beginTransaction();
            $user = auth()->user();
            $user = CustUser::with(['roles'])
                ->where('userid', $user->userid)
                ->where('status', '<>', '-1')->first();
            if (!$user) {
                return response()->json('User not found!', 404);
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
                $user->setRoles($validated['roles']);
            }
            DB::commit();
            return response()->json($user, 200);
        } catch (QueryException $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 500);
        }
    }

    public function photoUpdate(Request $request)
    {
        $validated = $this->validate($request, [
            'photo_url' => 'required'
        ]);

        $user = CustUser::where('userid', auth()->user()->userid)->where('status', '<>', '-1')->first();
        if (!$user) {
            return response()->json('User not found!', 404);
        }

        $user['photo_url'] = $validated['photo_url'];
        $user['updated_at'] = Carbon::now();
        $user['updated_by'] = auth()->user()  ? auth()->user()->userid : 1;
        $user->save();
        return response()->json($user, 200);
    }

    public function destroy(Request $request)
    {
        $validated = $this->validate($request, [
            'userid' => 'required|numeric',
        ]);
        try {
            $user = CustUser::where('userid', $validated['userid'])->where('status', '<>', '-1')->first();

            if (!$user) {
                return response()->json('Cust User not found!', 404);
            }

            // $user->update(['status' => -1, 'updated_by' => auth()->user()->userid, 'updated_at' => Carbon::now()]);
            $instConnCust = new InstCustConnService();
            if ($user) {
                $request['userid'] = $user->userid;
                $instConnCust->disConnect($validated['instid'], auth()->user()->instid);
            }
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
            $user = CustUser::where('userid', $validated['userid'])->where('status', '<>', '-1')->first();
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
        $hostname = env("APP_URL");
        $user = CustUser::where("email", $email)->where('status', '<>', '-1')->first();
        $data['hostname'] = $hostname;
        if ($user) {
            try {
                $token = rand(100000, 999999);
                $user->update(['passtoken' => $token, 'passtokendate' => DB::raw('sysdate'), 'passtokenstatus' => 1]);
                $data['token'] = $token;
                $email = [
                    "to" => $user->email,
                    "subject" => "Me. Нууц үг сэргээх хүсэлт",
                    "data" => $data,
                    "template" => "mail.resetPasswordMobile"
                ];
                Queue::push(new InquiryEmailNotificationJob($email));

                // MailService::sendMail($user->email, "mail.forgotPassword", $data, "ME систем. Forgotten password request");
                return response()->json('Таны имэйл хаяг руу баталгаажуулах код илгээлээ.', 200);
            } catch (Exception $e) {
                return response()->json($e->getMessage(), 500);
            }
        }
        return response()->json('Уучлаарай. Та бүртгэлгүй байна!', 404);
    }

    public function resetPassword(Request $request)
    {
        $validated = $this->validate($request, [
            'token' => 'required|min:7',
            'password' => 'required',
        ]);
        $password = $validated['password'];
        // $passpolicy->checkPassPolicy($password);

        $token = $validated['token'];
        $user = CustUser::select("CUST_USER.*")->selectRaw("CASE WHEN passtokendate IS NULL THEN 1 WHEN passtokenstatus = 0 THEN 2 WHEN passtokendate < (SELECT SYSDATE - OPTIONVALUE/60/24 FROM DIC_PASSPOLICY WHERE OPTIONNAME = 'TOKEN_LIFETIME') THEN 3 ELSE 4 END tokenstatus")->where("passtoken", $token)->first();
        if ($user) {
            try {
                $checkTkn = $this->service->checkToken($user->tokenstatus);
                if ($checkTkn['code']) {
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
                    return response()->json($checkTkn['msg'], 500);
                }
            } catch (Exception $e) {
                return response()->json($e->getMessage(), 500);
            }
        }
        return response()->json('Token not found!', 404);
    }

    public function passTokenConfirm(Request $request)
    {
        $validated = $this->validate($request, [
            'token' => 'required',
            'email' => 'required',
        ]);
        $token = $validated['token'];
        $user = CustUser::select("CUST_USER.*")->selectRaw("CASE WHEN passtokendate IS NULL THEN 1 WHEN passtokenstatus = 0 THEN 2 WHEN passtokendate < sysdate - 1/24 THEN 3 ELSE 4 END tokenstatus")->where('email', $validated['email'])->where("passtoken", $token)->first();
        if ($user) {
            try {
                $checkTkn = $this->service->checkToken($user->tokenstatus);
                if ($checkTkn['code']) {
                    $token = generateRandomString(50);
                    $user->update(['passtoken' => $token, 'passtokendate' => DB::raw('sysdate'), 'passtokenstatus' => 1]);
                    return response()->json(['token' => $token], 200);
                } else {
                    return response()->json($checkTkn['msg'], 500);
                }
            } catch (Exception $e) {
                return response()->json($e->getMessage(), 500);
            }
        }
        return response()->json('Token not found!', 404);
    }

    public function getPassPolicy(Request $request)
    {
        return DicPassPolicy::get();
    }

    public function confirmRegister(Request $request, $token)
    {
        $user = CustUser::where("passtoken", $token)->first();
        if ($user) {
            $user->status = 1;
            $user->passtoken = '';
            $user['updated_at'] = Carbon::now();
            $user['updated_by'] = $user->userid;
            $user->save();
            return response()->json('Таны бүртгэл баталгаажлаа.', 200);
        } else {
            return response()->json('Таны оруулсан токен буруу байна!', 404);
        }
    }

    public function getCustInsts(Request $request)
    {
        $validated = $this->validate($request, [
            'userid' => 'required',
            'islimit' => 'nullable'
        ]);
        $user = auth()->user();
        $acnt_srevice = new AcntService();
        if ((int)$user->userid != (int)$validated['userid']) {
            $validated['userid'] = $user->userid;
        }

        $custuser = CustUser::find($validated['userid']);
        if ($custuser && $custuser->status != -1) {
            if (@$validated['islimit'] == 1) {
                if ($custuser->insts) {
                    for ($i = 0; $i < count($custuser->insts); $i++) {
                        $elem = $custuser->insts[$i];
                        $elem['avail_bal'] = 0;
                        $elem['all_bal'] = 0;
                        $accounts = $this->service->getInstAccounts($elem['id']);
                        foreach ($accounts as $key => $acnt) {
                            if ($acnt['acntType'] == 'LINE') {
                                $lineAcnt = $acnt_srevice->getLoanAccountDetail($acnt['acntCode'], $elem['id']);
                                if ($lineAcnt['status'] == 200) {
                                    $line_data = json_decode(json_encode($lineAcnt['data']), true);
                                    $elem['all_bal'] = $elem['all_bal'] + $line_data['limit'];
                                    $elem['avail_bal'] = $elem['avail_bal'] + $line_data['availComBal'];
                                }
                            }
                        }
                        // return $elem;
                    }
                }
            }
            return response()->json($custuser->insts);
        }

        return response()->json('Not found cust user', 500);
    }

    public function getCustAccounts(Request $request)
    {
        $validated = $this->validate($request, [
            'instid' => 'required',
            'isAll' => 'nullable|boolean',
        ]);
        if (empty($validated['isAll'])) {
            $validated['isAll'] = false;
        }
        $user = auth()->user();
        if (!checkInstPerm('ac0100', $validated['instid'], $user)) {
            return response()->json('[ac0100] эрх олгогдоогүй байна.', 500);
        }
        $connInst = InstCustConn::where('INST_ID', $validated['instid'])->where('cust_user_userid', $user->userid)->where('statusid', 1)->first();
        if (!$connInst) {
            return response()->json('Тухайн байгууллагад бүртгэлгүй байна.', 500);
        }
        $cust = Cust::select('cif')->where('instid', $validated['instid'])->where('regno', $user->registernum)->first();
        if (!$cust) {
            return response()->json('Харилцагчийн мэдээлэл олдсонгүй.', 500);
        }
        $data = $this->service->getCustAccounts($cust->cif, $validated['instid'],  $validated['isAll']);
        return response()->json($data['data']);
        // return auth()->user()->registernum;
    }

    public function getAllAccounts(Request $request)
    {
        $validated = $this->validate($request, [
            'isAll' => 'nullable|boolean',
        ]);
        if (empty($validated['isAll'])) {
            $validated['isAll'] = false;
        }
        $user = auth()->user();
        // if (!checkInstPerm('ac0100', $validated['instid'], $user)) {
        //     return response()->json('[ac0100] эрх олгогдоогүй байна.', 500);
        // }
        $this->service->getCustAccountAllPolaris();
        $acnts = new AcntService();
        return response()->json($acnts->getAllAccounts($user, $validated['isAll']), 200);
        // return auth()->user()->registernum;
    }
}
