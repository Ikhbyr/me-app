<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\InstCustConn;
use App\Services\AcntService;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    protected $service;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(AcntService $service)
    {
        $this->service = $service;
    }

    public function getAccountStatemnt(Request $request)
    {
        $validated = $this->validate($request, [
            'acntCode' => 'required|max:20',
            'startDate' => 'required|date',
            'endDate' => 'required|date',
            'instid' => 'required|numeric',
            'startPosition' => 'required|numeric',
            'count' => 'required|numeric',
        ]);
        $tmp = $this->service->checkDates($validated['startDate'], $validated['endDate']);
        if ($tmp['status'] != 200) {
            return response()->json($tmp['data'], $tmp['status']);
        }
        $user = auth()->user();
        if (!checkInstPerm('ac0510', $validated['instid'], $user)) {
            return response()->json('[ac0510] эрх олгогдоогүй байна.', 500);
        }
        $connInst = InstCustConn::where('INST_ID', $validated['instid'])->where('cust_user_userid', $user->userid)->where('statusid', 1)->first();
        if (!$connInst) {
            return response()->json('Тухайн байгууллагад бүртгэлгүй байна.', 500);
        }

        $sendData = [
            'acntCode' => $validated['acntCode'],
            'startDate' => $validated['startDate'],
            'endDate' => $validated['endDate'],
            'orderBy' => 'desc',
            'seeNotFinancial' => 0,
            'seeCorr' => 0,
            'seeReverse' => 0,
            'startPosition' => $validated['startPosition'],
            'count' => $validated['count']
        ];

        $data = $this->service->getAccountStatement($sendData, $validated['instid']);
        return response()->json($data['data']);
    }

    public function getRepaymentSchedule(Request $request)
    {
        $validated = $this->validate($request, [
            'acntCode' => 'required|max:20',
            'instid' => 'required|numeric',
        ]);
        $user = auth()->user();
        if (!checkInstPerm('ac0710', $validated['instid'], $user)) {
            return response()->json('[ac0710] эрх олгогдоогүй байна.', 500);
        }
        $connInst = InstCustConn::where('INST_ID', $validated['instid'])->where('cust_user_userid', $user->userid)->where('statusid', 1)->first();
        if (!$connInst) {
            return response()->json('Тухайн байгууллагад бүртгэлгүй байна.', 500);
        }

        $data = $this->service->getRepaymentSchedule($validated['acntCode'], $validated['instid']);
        return response()->json($data['data']);
    }

    public function getAccountDetail(Request $request)
    {
        $validated = $this->validate($request, [
            'acntCode' => 'required|max:20',
            'instid' => 'required|numeric',
            'acntType' => 'required',
        ]);
        $this->service->acntTypes = $this->service->getAcntTypes();
        $acntType = $this->service->getAcntTypeTable($validated['acntType'], $this->service->acntTypes);
        switch ($acntType) {
            case 'LOAN_ACNT':
                if (!checkInstPerm('ac0613', $validated['instid'])) {
                    return response()->json('[ac0613] эрх олгогдоогүй байна.', 500);
                }
            case 'CASA_ACNT':
                if (!checkInstPerm('ac0612', $validated['instid'])) {
                    return response()->json('[ac0612] эрх олгогдоогүй байна.', 500);
                }
            case 'TD_ACNT':
                if (!checkInstPerm('ac0611', $validated['instid'])) {
                    return response()->json('[ac0611] эрх олгогдоогүй байна.', 500);
                }
            case 'CCA_ACNT':
                if (!checkInstPerm('ac0614', $validated['instid'])) {
                    return response()->json('[ac0614] эрх олгогдоогүй байна.', 500);
                }
            default:
                # code...
                break;
        }
        $data = $this->service->getAccountDetail($validated['acntCode'], $validated['acntType'], $validated['instid']);
        return response()->json($data['data'], $data['status']);
    }

    public function getCasaAccountDetail(Request $request)
    {
        $validated = $this->validate($request, [
            'acntCode' => 'required|max:20',
            'instid' => 'required|numeric',
        ]);
        $data = $this->service->getCasaAccountDetail($validated['acntCode'], $validated['instid']);
        return response()->json($data['data'], $data['status']);
    }

    public function getTdAccountDetail(Request $request)
    {
        $validated = $this->validate($request, [
            'acntCode' => 'required|max:20',
            'instid' => 'required|numeric',
        ]);
        $data = $this->service->getTdAccountDetail($validated['acntCode'], $validated['instid']);
        return response()->json($data['data'], $data['status']);
    }

    public function getLoanAccountDetail(Request $request)
    {
        $validated = $this->validate($request, [
            'acntCode' => 'required|max:20',
            'instid' => 'required|numeric',
        ]);
        $data = $this->service->getLoanAccountDetail($validated['acntCode'], $validated['instid']);
        return response()->json($data['data'], $data['status']);
    }

    public function getCreditAccountDetail(Request $request)
    {
        $validated = $this->validate($request, [
            'acntCode' => 'required|max:20',
            'instid' => 'required|numeric',
        ]);
        $data = $this->service->getCreditAccountDetail($validated['acntCode'], $validated['instid']);
        return response()->json($data['data'], $data['status']);
    }

    /**
     * Дансны хүүний дэлгэрэнгүй
     *
     * @param  mixed $request
     * @return void
     */
    public function getAccountInt(Request $request)
    {
        $validated = $this->validate($request, [
            'acntCode' => 'required|max:20',
            'instid' => 'required|numeric',
        ]);
        $data = $this->service->getAccountInt($validated['acntCode'], $validated['instid']);
        return response()->json($data['data'], $data['status']);
    }
}
