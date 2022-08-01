<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    protected $service;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(DashboardService $service)
    {
        $this->service = $service;
    }

    public function get(){
        return $this->service->getCountUserWithClient();
    }

    public function getInstInfo(Request $request){
        $validated = $this->validate($request, [
            'countUserWithClient' => 'nullable|numeric',
            'custMainHist' => 'nullable|numeric',
            'custDutyHist' => 'nullable|numeric',
            'countDutyMain' => 'nullable|numeric',
            'countGeneral' => 'nullable|numeric',
        ]);
        $res = array();
        if (@$validated['countUserWithClient'] == 1) {
            $res['countUserWithClient'] = $this->service->getCountUserWithClient();
        }
        if (@$validated['custMainHist'] == 1) {
            $res['custMainHist'] = $this->service->getCountCustMainHist();
        }
        if (@$validated['custDutyHist'] == 1) {
            $res['custDutyHist'] = $this->service->getCountCustDutyHist();
        }
        if (@$validated['countDutyMain'] == 1) {
            $res['countDutyMain'] = $this->service->getCountDutyMain();
        }
        if (@$validated['countGeneral'] == 1) {
            $res['countGeneral'] = $this->service->getCountGeneral();
        }
        return response()->json($res, 200);
    }
    //
}
