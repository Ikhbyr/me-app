<?php

namespace App\Http\Controllers\Back;
use App\Models\Provider;
use App\Services\ProviderService;
use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProviderParamController extends Controller
{
    protected $service;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ProviderService $service)
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

        $withs = ["connConf:id,name,typeid,config,descr", "connConf.type:value,name", "type"];

        $clientCust = $this->service->list($withs);
        $clientCust = $this->applyFilters($clientCust, @$validated['filters']);
        $clientCust = $this->applyOrders($clientCust, @$validated['orders']);
        $clientCust = $this->applyPaginate($clientCust, @$validated['perPage'], @$validated['page']);

        return response()->json($clientCust);
    }

    public function show(Request $request){
        $validated = $this->validate($request, [
            'id'=>'required|numeric',
        ]);
        $withs = ["connConf:id,name,typeid,config,descr", "connConf.type:value,name", "type"];
        $clientProduct = $this->service->get($validated['id'], $withs)->first();
        if (!$clientProduct){
            return throw new Exception("ClientCust [{$validated['id']}] not found!");
        }
        return response()->json($clientProduct);
    }

    public function store(Request $request){
        $validated = $this->validate($request, Provider::getStoreRules(), Provider::getStoreMessages());
        $validated["datapack_no"] = 1;
        return response()->json($this->service->store($validated));
    }

    public function update(Request $request){
        $validated = $this->validate($request, Provider::getUpdateRules(), Provider::getUpdateMessages());
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
