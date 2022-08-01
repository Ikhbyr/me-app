<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\InstContact;
use App\Services\InstContactService;
use Exception;
use Illuminate\Http\Request;

class InstContactController extends Controller
{
    protected $service;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(InstContactService $service)
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

        $withs = ['inst:id,instname'];
        $clientContacts = $this->service->list($withs);
        $clientContacts = $this->applyFilters($clientContacts, @$validated['filters']);
        $clientContacts = $this->applyOrders($clientContacts, @$validated['orders']);
        $clientContacts = $this->applyPaginate($clientContacts, @$validated['perPage'], @$validated['page']);

        return response()->json($clientContacts);
    }

    public function show(Request $request){
        $validated = $this->validate($request, [
            'id' => 'required|numeric',
        ]);
        $clientContact = $this->service->get($validated['id'])->first();
        if (!$clientContact){
            throw new Exception("InstContact [".$validated['id']."] not found!");
        }
        return response()->json($clientContact);
    }

    public function store(Request $request){
        $validated = $this->validate($request, InstContact::getStoreRules());
        return $this->service->store($validated);
    }

    public function update(Request $request){
        $validated = $this->validate($request, InstContact::getUpdateRules());
        return response()->json($this->service->update($validated));
    }

    public function delete(Request $request){
        $validated = $this->validate($request, InstContact::getDeleteRules());
        return $this->service->delete($validated['id']);
    }

    public function restore($id){
        return response()->json($this->service->restore($id));
    }
}
