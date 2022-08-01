<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\Inst;
use App\Services\InstPermService;
use Exception;
use Illuminate\Http\Request;

class InstPermController extends Controller
{
    protected $service;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(InstPermService $service)
    {
        $this->service = $service;
    }

    public function getPerms(Request $request){
        if ($request->has('instid')){
            $instid = $request->instid;
        } else {
            $instid = Inst::where('id', auth()->user()->instid)->first()->instid;
        }
        if ($request->has('moduleid')){
            $moduleid = $request->moduleid;
        } else {
            $moduleid = false;
        }
        return $this->service->getPerms($instid, $moduleid);
    }

    public function setPerms(Request $request){
        $v = $this->validate($request, [
            'instid'=>'required|numeric',
            'perms'=>'nullable|array',
            'perms.*'=>'required|max:20',
        ]);
        return $this->service->setPerms($v['instid'], empty($v["perms"]) ? [] : $v["perms"]);
    }

    // public function index(Request $request, $onlyMy = false)
    // {
    //     $validated = $this->validate($request, [
    //         'filters' => 'nullable|array',
    //         'filters.*.field' => 'required|max:60',
    //         'filters.*.value' => 'nullable|max:60',
    //         'filters.*.cond' => 'nullable|max:10',
    //         'orders' => 'nullable|array',
    //         'orders.*.field' => 'nullable|max:60',
    //         'orders.*.dir' => 'nullable|max:5',
    //         'perPage' => 'nullable|numeric',
    //         'page' => 'nullable|numeric',
    //         'withInst' => 'nullable|numeric',
    //         'withType' => 'nullable|numeric',
    //     ]);

    //     $accounts = $this->service->list();

    //     $withs = [];
    //     if (@$validated['withInst']) {
    //         $withs[] = 'client';
    //     }

    //     if (@$validated['withType']) {
    //         $withs[] = 'type';
    //     }

    //     $accounts->with($withs);
    //     if ($onlyMy) {
    //         $user = auth()->user();
    //         $accounts = $accounts->where('instid', $user->instid);
    //         if ($user->branchid){
    //             $branch = InstBranch::where('instid', $user->instid)->where('id', $user->branchid)->first();
    //             if ($branch){
    //                 $accounts = $accounts->where('branchno', $branch->branchno);
    //             }
    //         }
    //     }

    //     $accounts = $this->applyFilters($accounts, @$validated['filters']);
    //     $accounts = $this->applyOrders($accounts, @$validated['orders']);
    //     $accounts = $this->applyPaginate($accounts, @$validated['perPage'], @$validated['page']);

    //     return response()->json($accounts);
    // }

    // public function getByInst(Request $request){
    //     return $this->index($request, true);
    // }

    // public function show(Request $request)
    // {
    //     $validated = $this->validate($request, [
    //         'accountno' => 'required|numeric',
    //     ]);
    //     $account = $this->service->get($validated['accountno'])->first();
    //     if (!$account) {
    //         return throw new Exception("Inst [{$validated['instid']}] does not have [{$validated['accountno']}] found!");
    //     }
    //     return response()->json($account);
    // }

    // public function store(Request $request)
    // {
    //     $validated = $this->validate($request, [
    //         'instid' => 'required|max:20',
    //         // 'balance' => 'nullable|numeric',
    //         'accountname' => 'required|max:100',
    //         'glaccount' => 'required|max:20',
    //         'limit' => 'required|numeric',
    //         'typeid' => 'required|max:30',
    //         'curcode' => 'required|max:3',
    //         'statusid' => 'nullable|integer',
    //         'branchno' => 'nullable|max:30',
    //     ]);
    //     return response()->json($this->service->store($validated));
    // }

    // public function update(Request $request)
    // {
    //     $validated = $this->validate($request, [
    //         'instid' => 'required|max:20',
    //         'accountno' => 'required|max:20',
    //         // 'balance' => 'nullable|numeric',
    //         'accountname' => 'required|max:100',
    //         'glaccount' => 'required|max:20',
    //         'limit' => 'required|numeric',
    //         'tmp_limit' => 'nullable|numeric',
    //         'typeid' => 'required|max:30',
    //         'statusid' => 'nullable|integer',
    //         'branchno' => 'nullable|max:30',
    //     ]);
    //     return response()->json($this->service->update($validated['accountno'], $validated));
    // }

    // public function delete(Request $request)
    // {
    //     $validated = $this->validate($request, [
    //         // 'instid' => 'required|max:20',
    //         'accountno' => 'required|max:20',
    //     ]);
    //     return response()->json($this->service->delete($validated['accountno']));
    // }

    // public function restore(Request $request)
    // {
    //     $validated = $this->validate($request, [
    //         // 'instid' => 'required|max:20',
    //         'accountno' => 'required|max:20',
    //     ]);
    //     return response()->json($this->service->restore($validated['accountno']));
    // }

}
