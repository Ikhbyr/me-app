<?php

namespace App\Http\Controllers\Back;

use App\Models\SysConf;
use App\Services\SysConfService;
use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SysConfController extends Controller
{
    protected $service;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(SysConfService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request){
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

        $withs = ["type:value,name"];

        $SysConf = $this->service->list($withs);
        $SysConf = $this->applyFilters($SysConf, @$validated['filters']);
        $SysConf = $this->applyOrders($SysConf, @$validated['orders']);
        $SysConf = $this->applyPaginate($SysConf, @$validated['perPage'], @$validated['page']);

        return response()->json($SysConf);
    }

    public function show(Request $request){
        $validated = $this->validate($request, [
            'id'=>'required|numeric',
        ]);
        $withs = ["type:value,name"];
        $SysConf = $this->service->get($validated['id'], $withs)->first();
        if (!$SysConf){
            throw new Exception("SysConf [{$validated['id']}] not found!");
        }
        return response()->json($SysConf);
    }

    public function store(Request $request){
        $validated = $this->validate($request, SysConf::getStoreRules(), SysConf::getStoreMessages());
        $validated["datapack_no"] = 1;
        return response()->json($this->service->store($validated));
    }

    public function update(Request $request){
        $validated = $this->validate($request, SysConf::getUpdateRules(), SysConf::getUpdateMessages());
        return response()->json($this->service->update($validated['id'], $validated));
    }

    public function delete(Request $request){
        $validated = $this->validate($request, [
            'id' => 'required|numeric',
        ]);
        return response()->json($this->service->delete($validated['id']));
    }

    public function restore(Request $request){
        $validated = $this->validate($request, [
            'id' => 'required|numeric',
        ]);
        return response()->json($this->service->restore($validated['id']));
    }
}
