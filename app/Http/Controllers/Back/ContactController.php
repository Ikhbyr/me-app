<?php

namespace App\Http\Controllers\Back;

use App\Models\Contract;
use App\Services\ContractService;
use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ContactController extends Controller
{
    protected $service;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ContractService $service)
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
        $user = auth()->user();
        $contract = Contract::select('name', 'id', 'type_id')->with(['type:value,name'])->where('statusid', '<>', -1)->where('instid', $user->instid);
        $contract = $this->applyFilters($contract, @$validated['filters']);
        $contract = $this->applyOrders($contract, @$validated['orders']);
        $contract = $this->applyPaginate($contract, @$validated['perPage'], @$validated['page']);

        return response()->json($contract);
    }

    public function show(Request $request){
        $validated = $this->validate($request, [
            'id'=>'required|numeric',
        ]);
        $contract = $this->service->get($validated['id'])->first();
        if (!$contract){
            throw new Exception("contract [{$validated['id']}] not found!");
        }
        return response()->json($contract);
    }

    public function store(Request $request){
        $validated = $this->validate($request, Contract::getStoreRules(), Contract::getStoreMessages());
        if(!@$validated['type_id']) {
            $validated['type_id'] = "00";
        }
        return response()->json($this->service->store($validated));
    }

    public function update(Request $request){
        $validated = $this->validate($request, Contract::getUpdateRules(), Contract::getUpdateMessages());
        return response()->json($this->service->update($validated['id'], $validated));
    }

    public function delete(Request $request){
        $validated = $this->validate($request, [
            'id' => 'required|numeric',
        ]);
        return response()->json($this->service->delete($validated['id']));
    }
}
