<?php

namespace App\Http\Controllers\Back;

use App\Models\BankSys;
use App\Services\BankSysService;
use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\BankSysCollection;

class BankSysController extends Controller
{
    protected $service;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(BankSysService $service)
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

        $clientCust = $this->service->list($withs);
        $clientCust = $this->applyFilters($clientCust, @$validated['filters']);
        $clientCust = $this->applyOrders($clientCust, @$validated['orders']);
        $clientCust = $this->applyPaginate($clientCust, @$validated['perPage'], @$validated['page']);

        return response()->json(new BankSysCollection($clientCust));
    }

    public function show(Request $request){
        $validated = $this->validate($request, [
            'id'=>'required|numeric',
        ]);
        $withs = ["type:value,name"];
        $clientProduct = $this->service->get($validated['id'], $withs)->first();
        if (!$clientProduct){
            throw new Exception("ClientCust [{$validated['id']}] not found!");
        }
        return response()->json($clientProduct);
    }

    public function store(Request $request){
        $validated = $this->validate($request, BankSys::getStoreRules(), BankSys::getStoreMessages());
        $validated["datapack_no"] = 1;
        return response()->json($this->service->store($validated));
    }

    public function update(Request $request){
        $validated = $this->validate($request, BankSys::getUpdateRules(), BankSys::getUpdateMessages());
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
