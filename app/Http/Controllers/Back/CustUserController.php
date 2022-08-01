<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Jobs\InquiryEmailNotificationJob;
use App\Models\Cust;
use App\Models\CustUser;
use App\Models\Inst;
use App\Models\InstCustConn;
use App\Services\GoogleAuthenticator\GoogleAuthenticator;
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
        ]);

        $user = auth()->user();
        if ($user->isadmin == '1') {
            $users = CustUser::select(
                'userid',
                'registernum',
                'lastname',
                'firstname',
                'email',
                'created_at',
                'phoneuser'
            )->where('status', '<>', '-1');
        } else {
            $users = InstCustConn::select(
                'cust_user.userid',
                'cust_user.registernum',
                'cust_user.lastname',
                'cust_user.firstname',
                'cust_user.email',
                'cust_user.created_at',
                'phoneuser'
            )
                ->where('inst_id', $user->instid)->where('statusid', '1')->leftJoin('cust_user', 'inst_cust_conn.cust_user_userid', '=', 'cust_user.userid');
        }
        $users = $this->applyFilters($users, @$validated['filters']);
        $users = $this->applyOrders($users, @$validated['orders']);
        $users = $this->applyPaginate($users, @$validated['perPage'], @$validated['page']);
        return response()->json($users);
    }

    public function indexSmall(Request $request)
    {
        $validated = $this->validate($request, [
            'filters' => 'nullable|array',
            'filters.*.field' => 'required|max:60',
            'filters.*.value' => 'nullable|max:60',
            'filters.*.cond' => 'nullable|max:10',
            'perPage' => 'nullable|numeric',
            'page' => 'nullable|numeric',
        ]);

        $user = auth()->user();
        if ($user->isadmin == '1') {
            $users = CustUser::select('userid', 'firstname', 'lastname')->where('status', '<>', '-1')->orderBy('userid', 'desc');
        } else {
            $users = InstCustConn::select('cust_user.firstname', 'cust_user.lastname', 'cust_user.userid')
                ->where('inst_id', $user->instid)->leftJoin('cust_user', 'inst_cust_conn.cust_user_userid', '=', 'cust_user.userid');
        }
        $users = $this->applyFilters($users, @$validated['filters']);
        $users = $this->applyOrders($users, @$validated['orders']);
        $users = $this->applyPaginate($users, @$validated['perPage'], @$validated['page']);
        return response()->json($users);
    }

    public function store(Request $request)
    {
        $validated = $this->validate($request, [
            'email' => 'required|max:60',
            'phoneuser' => 'max:60',
            'registernum' => 'required|max:18',
            'isadmin' => 'nullable|max:1',
            'firstname' => 'required|max:50',
            'lastname' => 'required|max:50',
            'status' => 'nullable|numeric|digits_between:1,3',
            'address' => 'nullable|max:100',
            'roles' => 'nullable|array',
            'region' => 'nullable',
            'subregion' => 'nullable',
            'roles.*.roleid' => 'required|max:10',
            'roles.*.status' => 'required|numeric|max:1',
            'roles.*.startdate' => 'nullable|date_format:Y-m-d',
            'roles.*.enddate' => 'nullable|date_format:Y-m-d'
        ]);

        $validated['instid'] = auth()->user()->instid;
        $validated['registernum'] = mb_strtoupper($validated['registernum']);
        $validated['email'] = Str::lower($validated['email']);
        $inst = Inst::where('id', $validated['instid'])->where('statusid', '<>', '-1')->first();
        if (!$inst) {
            return response()->json('Та байгууллагад бүртгэлгүй байна.', 500);
        }
        $cust = Cust::where('instid', $validated['instid'])->where('regno', $validated['registernum'])->first();
        if (!$cust) {
            return response()->json('Харилцагчийн мэдээллээ татна уу.', 500);
            // $cust_resp = $this->service->createCustomerInfo($validated['registernum']);
            // if ($cust_resp['status'] != 200) {
            //     return response()->json('Суурь системд алдаа гарлаа. ' . $cust_resp['data'], 500);
            // }
        }
        $userExist = CustUser::where('registernum', $validated['registernum'])->where('status', '<>', '-1')->first();
        $instConnCust = new InstCustConnService();
        if ($userExist) {
            $request['userid'] = $userExist->userid;
            $user = $this->update($request);
            $instConnCust->connect($validated['instid'], $userExist->userid);
            $this->service->getNesCustAccounts($cust->cif, $validated['instid']);
            return $user;
        } else {
            $userExist = CustUser::where('email', $request['email'])->where('status', '<>', '-1')->first();

            if ($userExist) {
                return response()->json('Таны мэйл хаяг бүртгэлтэй байна.', 500);
            }

            $googleAuth = new GoogleAuthenticator();
            $random_password = '#@1' . Str::random(8);
            try {
                DB::beginTransaction();
                $user = new CustUser();
                $validated = array_change_key_case($validated);
                foreach ($user->fillable as $field) {
                    if (array_key_exists($field, $validated)) {
                        $user->$field = $validated[$field];
                    }
                }
                $user->google_auth_key = $googleAuth->generateSecret();
                $user->status = 0;
                $user->mustchgpass = '1';
                $user->passtoken = rand(100000, 999999);
                $user->password = Hash::make(me_hmac($random_password));
                $user->created_at = Carbon::now();
                $user->created_by = auth()->user() ? auth()->user()->userid : 1;
                $user->save();

                if (array_key_exists('roles', $validated)) {
                    $user->setRoles($validated['roles']);
                }
                $data = array();
                $data['random_password'] = $random_password;

                $data['hostname'] = env('APP_URL') . "/mobile-api/cust-user";
                $data['firstname'] = $user->firstname;
                // MailService::sendMail($user->email, 'mailRegister', $data, 'Бүртгэл амжилттай хийгдлээ.');

                $email = [
                    "to" => $user->email,
                    "subject" => "Бүртгэл амжилттай хийгдлээ.",
                    "data" => $data,
                    "template" => "mail.mailRegister"
                ];
                $instConnCust->connect($validated['instid'], $user->userid);
                Queue::push(new InquiryEmailNotificationJob($email));
                DB::commit();
                $this->service->getNesCustAccounts($cust->cif, $validated['instid']);
                return response()->json('Таны бүртгэл амжилттай хийгдлээ. Бүртгүүлсэн мэйл хаягт баталгаажуулах линк илгээсэн. Бүртгэл баталгаажсны дараа системд нэвтрэхийг анхаарна уу.', 200);
            } catch (QueryException $e) {
                DB::rollBack();
                return response()->json($e->getMessage(), 500);
            }
        }
    }

    public function show(Request $request)
    {
        $validated = $this->validate($request, [
            'userid' => 'required|numeric',
        ]);
        try {
            $user = CustUser::where('userid', $validated['userid'])->where('status', '<>', '-1');
            if (@$request['withRoles'] == 1) {
                $user = $user->with(['instRoles', 'instRoles.role']);
            }

            $user = $user->first();
            if (!empty($user->photo_url)) {
                try {
                    $user['photo_base64'] = base64_encode(file_get_contents(env('APP_URL') . $user->photo_url));
                } catch (Exception $ex) {
                    $user['photo_base64'] = "";
                }
            }
            $user['roles'] = $user->instRoles ?? [];
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
            $user = CustUser::with(['roles']);
            $user->where('userid', $validated['userid'])->where('status', '<>', '-1');
            // if ($validated['clientid']) {
            //     $user->where('clientid', $validated['clientid']);
            // }
            $user = $user->first();
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
            $user = CustUser::with(['roles'])->where('userid', $user->userid)->first();
            return response()->json($user, 200);
        } catch (QueryException $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 500);
        }
    }

    public function photoUpdate(Request $request)
    {
        $validated = $this->validate($request, [
            'photo_url' => 'required',
            'userid' => 'required|numeric'
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

    public function directory(Request $request)
    {
        $validated = $this->validate($request, [
            'registernum' => 'required|max:18'
        ]);
        $validated['registernum'] = mb_strtoupper($validated['registernum']);
        $user = auth()->user();
        if ($user->isadmin == '1') {
            return response()->json('Та хандах эрхгүй байна.(Admin)', 500);
        }
        $cust = Cust::where('instid', $user->instid)->where('regno', $validated['registernum'])->first();
        if (!$cust) {
            $cust_resp = $this->service->createCustomerInfo($validated['registernum']);
            if ($cust_resp['status'] != 200) {
                return response()->json('Суурь системд алдаа гарлаа. ' . $cust_resp['data'], 500);
            }
        }
        $userExist = CustUser::where('registernum', $validated['registernum'])->where('status', '<>', '-1')->first();
        if ($userExist) {
            $instConnCust = new InstCustConnService();
            if ($instConnCust->isConnect($user->instid, $userExist->userid)) {
                return response()->json('Бүртгэлтэй хэрэглэгч байна.', 500);
            }
            return response()->json(
                [
                    'data' => $userExist,
                    'cust' => $cust ?? $cust_resp['data']
                ]
            );
        } else {
            return response()->json(
                [
                    'data' => null,
                    'cust' => $cust ?? $cust_resp['data']
                ]
            );
        }
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
                $request['userid'] = $user->userid;
                $instConnCust->disConnect(auth()->user()->instid, $user->userid);
                return [auth()->user()->instid, $user->userid];
                $this->service->deleteCustUserRoleInst($user->userid, auth()->user()->instid);
            return response()->json('User deleted', 200);
        } catch (QueryException $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
}
