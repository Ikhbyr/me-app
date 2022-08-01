<?php

namespace App\Http\Controllers;

use App\Models\ChangeLog;
use App\Services\ChangeLogService;
use Exception;
use Illuminate\Http\Request;

class ChangeLogController extends Controller
{
    protected $service;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ChangeLogService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request){
        $v = $this->validate($request, [
            'filters' => 'nullable|array',
            'filters.*.field' => 'required|max:60',
            'filters.*.value' => 'nullable|max:60',
            'filters.*.cond' => 'nullable|max:10',
            'orders' => 'nullable|array',
            'orders.*.field' => 'nullable|max:60',
            'orders.*.dir' => 'nullable|max:5',
            'perPage' => 'nullable|numeric',
            'page' => 'nullable|numeric',
            'userid' => 'nullable|numeric',
            'startdate' => 'required|date_format:Y-m-d',
            'enddate' => 'required|date_format:Y-m-d',
            'search' => 'nullable|max:30',
        ]);

        $withs = [];

        $accessLog = $this->service->list(@$v['userid'], $v['startdate'], $v['enddate'], @$v['search']);
        $accessLog = $this->applyFilters($accessLog, @$v['filters']);
        $accessLog = $this->applyOrders($accessLog, @$v['orders']);
        $accessLog = $this->applyPaginate($accessLog, @$v['perPage'], @$v['page']);

        return response()->json($accessLog);
    }

    public function show(Request $request){
        $v = $this->validate($request, [
            'id'=>'required|numeric',
        ]);
        $withs = [];
        $accessLog = $this->service->get($v['id'])->first();
        if (!$accessLog){
            throw new Exception("ChangeLog [{$v['id']}] not found!");
        }
        return response()->json($accessLog);
    }
}
