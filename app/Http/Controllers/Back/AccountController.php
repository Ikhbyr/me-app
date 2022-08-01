<?php

namespace App\Http\Controllers\Back;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AccountStatement;
use App\Models\CasaAcnt;
use App\Models\CcaAcnt;
use App\Models\LoanAcnt;
use App\Models\TdAcnt;
use App\Services\AcntService;

class AccountController extends Controller
{
    public function getTransaction(Request $request)
    {
        $validated = $this->validate($request, [
            'filters' => 'nullable|array',
            'filters.*.field' => 'required|max:60',
            'filters.*.value' => 'nullable|max:60',
            'filters.*.cond' => 'nullable|max:10',
            'orders' => 'nullable|array',
            'orders.*.field' => 'nullable|max:60',
            'orders.*.dir' => 'nullable|max:5',
            'perPage' => 'nullable|numeric',
            'page' => 'nullable|numeric',
        ]);

        $trans = AccountStatement::where('instid', auth()->user()->instid);
        $trans = $this->allServiceList($trans, $validated);
        return response()->json($trans);
    }

    public function getLoanAcntList(Request $request)
    {
        $validated = $this->validate($request, [
            'filters' => 'nullable|array',
            'filters.*.field' => 'required|max:60',
            'filters.*.value' => 'nullable|max:60',
            'filters.*.cond' => 'nullable|max:10',
            'orders' => 'nullable|array',
            'orders.*.field' => 'nullable|max:60',
            'orders.*.dir' => 'nullable|max:5',
            'perPage' => 'nullable|numeric',
            'page' => 'nullable|numeric',
        ]);

        $lnAcnt = LoanAcnt::select('acnt_code', 'acnt_type', 'prod_name', 'cust_code', 'name', 'princ_bal', 'cur_code')->where('instid', auth()->user()->instid);
        $lnAcnt = $this->allServiceList($lnAcnt, $validated);
        return response()->json($lnAcnt);
    }

    public function getCasaAcntList(Request $request)
    {
        $validated = $this->validate($request, [
            'filters' => 'nullable|array',
            'filters.*.field' => 'required|max:60',
            'filters.*.value' => 'nullable|max:60',
            'filters.*.cond' => 'nullable|max:10',
            'orders' => 'nullable|array',
            'orders.*.field' => 'nullable|max:60',
            'orders.*.dir' => 'nullable|max:5',
            'perPage' => 'nullable|numeric',
            'page' => 'nullable|numeric',
        ]);

        $casaAcnt = CasaAcnt::select(
            'acnt_code',
            'acnt_type',
            'prod_name',
            'cust_code',
            'name',
            'current_bal',
            'cur_code'
        )->where('instid', auth()->user()->instid);
        $casaAcnt = $this->allServiceList($casaAcnt, $validated);
        return response()->json($casaAcnt);
    }

    public function getTdAcntList(Request $request)
    {
        $validated = $this->validate($request, [
            'filters' => 'nullable|array',
            'filters.*.field' => 'required|max:60',
            'filters.*.value' => 'nullable|max:60',
            'filters.*.cond' => 'nullable|max:10',
            'orders' => 'nullable|array',
            'orders.*.field' => 'nullable|max:60',
            'orders.*.dir' => 'nullable|max:5',
            'perPage' => 'nullable|numeric',
            'page' => 'nullable|numeric',
        ]);

        $tdAcnt = TdAcnt::select(
            'acnt_code',
            'acnt_type',
            'prod_name',
            'cust_code',
            'name',
            'current_bal',
            'cur_code'
        )->where('instid', auth()->user()->instid);
        $tdAcnt = $this->allServiceList($tdAcnt, $validated);
        return response()->json($tdAcnt);
    }

    public function getCcaAcntList(Request $request)
    {
        $validated = $this->validate($request, [
            'filters' => 'nullable|array',
            'filters.*.field' => 'required|max:60',
            'filters.*.value' => 'nullable|max:60',
            'filters.*.cond' => 'nullable|max:10',
            'orders' => 'nullable|array',
            'orders.*.field' => 'nullable|max:60',
            'orders.*.dir' => 'nullable|max:5',
            'perPage' => 'nullable|numeric',
            'page' => 'nullable|numeric',
        ]);

        $ccaAcnt = CcaAcnt::select(
            'acnt_code',
            'acnt_type',
            'prod_code_name',
            'cust_code',
            'name',
            'avail_balance',
            'cur_code'
        )->where('instid', auth()->user()->instid);
        $ccaAcnt = $this->allServiceList($ccaAcnt, $validated);
        return response()->json($ccaAcnt);
    }

    public function getAccountDetail(Request $request) {
        $validated = $this->validate($request, [
            'acntCode' => 'required',
            'acntType' => 'required'
        ]);
        $acntSer = new AcntService();
        $data = $acntSer->getAccountDetail($validated['acntCode'], $validated['acntType'], 0, true);
        return response()->json($data['data'], $data['status']);
    }
}
