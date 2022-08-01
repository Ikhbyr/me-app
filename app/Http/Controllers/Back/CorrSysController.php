<?php

namespace App\Http\Controllers\Back;

use App\Models\CorrSys;
use App\Services\CorrSysService;
use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CorrSysController extends Controller
{
    protected $service;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(CorrSysService $service)
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

        $CorrSys = $this->service->list($withs);
        $CorrSys = $this->applyFilters($CorrSys, @$validated['filters']);
        $CorrSys = $this->applyOrders($CorrSys, @$validated['orders']);
        $CorrSys = $this->applyPaginate($CorrSys, @$validated['perPage'], @$validated['page']);

        return response()->json($CorrSys);
    }

    public function show(Request $request){
        $validated = $this->validate($request, [
            'id'=>'required|numeric',
        ]);
        $withs = ["type:value,name"];
        $CorrSys = $this->service->get($validated['id'], $withs)->first();
        if (!$CorrSys){
            throw new Exception("CorrSystem [{$validated['id']}] not found!");
        }
        return response()->json($CorrSys);
    }

    public function store(Request $request){
        $validated = $this->validate($request, CorrSys::getStoreRules(), CorrSys::getStoreMessages());
        $validated["datapack_no"] = 1;
        return response()->json($this->service->store($validated));
    }

    public function update(Request $request){
        $validated = $this->validate($request, CorrSys::getUpdateRules(), CorrSys::getUpdateMessages());
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
