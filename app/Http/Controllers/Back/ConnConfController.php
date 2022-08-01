<?php

namespace App\Http\Controllers\Back;

use App\Models\ConnConf;
use App\Services\ConnConfService;
use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ConnConfController extends Controller
{
    protected $service;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ConnConfService $service)
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

        $Connection = $this->service->list($withs);
        $Connection = $this->applyFilters($Connection, @$validated['filters']);
        $Connection = $this->applyOrders($Connection, @$validated['orders']);
        $Connection = $this->applyPaginate($Connection, @$validated['perPage'], @$validated['page']);

        return response()->json($Connection);
    }

    public function show(Request $request){
        $validated = $this->validate($request, [
            'id'=>'required|numeric',
        ]);
        $withs = ["type:value,name"];
        $Connection = $this->service->get($validated['id'], $withs)->first();
        if (!$Connection){
            throw new Exception("Connection [{$validated['id']}] not found!");
        }
        return response()->json($Connection);
    }

    public function store(Request $request){
        $validated = $this->validate($request, ConnConf::getStoreRules(), ConnConf::getStoreMessages());
        $validated["datapack_no"] = 1;
        return response()->json($this->service->store($validated));
    }

    public function update(Request $request){
        $validated = $this->validate($request, ConnConf::getUpdateRules(), ConnConf::getUpdateMessages());
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
