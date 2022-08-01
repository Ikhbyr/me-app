<?php

namespace App\Http\Controllers;

use App\Services\DicService;
use Illuminate\Http\Request;

class DicController extends Controller
{
    protected $service;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(DicService $service)
    {
        $this->service = $service;
    }

    public function get(Request $request){
        $validated = $this->validate($request, [
            'filters' => 'nullable|array',
            'filters.*.field' => 'nullable|max:60',
            'filters.*.value' => 'nullable|max:60',
            'filters.*.cond' => 'nullable|max:10',
            'orders' => 'nullable|array',
            'orders.*.field' => 'nullable|max:60',
            'orders.*.dir' => 'nullable|max:5',
            'perPage' => 'nullable|numeric',
            'page' => 'nullable|numeric',
        ]);

        $dic = $this->service->get();
        $dic = $this->applyFilters($dic, @$validated['filters']);
        $dic = $this->applyOrders($dic, @$validated['orders']);
        $dic = $this->applyPaginate($dic, @$validated['perPage'], @$validated['page']);


        return response()->json($dic);
    }

    public function getDic(Request $request, ){
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
            'maintype' => 'required|max:30',
        ]);

        $dic = $this->service->getDic($validated['maintype']);
        $dic = $this->applyFilters($dic, @$validated['filters']);
        $dic = $this->applyOrders($dic, @$validated['orders']);
        $dic = $this->applyPaginate($dic, @$validated['perPage'], @$validated['page']);


        return response()->json($dic);
    }

    public function getDicByParent(Request $request, ){
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
            'parentid' => 'required|max:30',
        ]);

        $dic = $this->service->getDicByParent($validated['parentid']);
        $dic = $this->applyFilters($dic, @$validated['filters']);
        $dic = $this->applyOrders($dic, @$validated['orders']);
        $dic = $this->applyPaginate($dic, @$validated['perPage'], @$validated['page']);


        return response()->json($dic);
    }

    public function add(Request $request){
        $validated = $this->validate($request, [
            'maintype'=>'required|max:30|unique:dic_main',
            'value'=>'required|max:30',
            'name'=>'required|max:300',
            'info'=>'nullable|max:300',
            'parentid'=>'nullable|max:10',
        ]);
        return response()->json($this->service->add($validated['maintype'], $validated['value'], $validated['name'], @$validated['info'], @$validated['parentid']));
    }

    public function delete(Request $request){
        $validated = $this->validate($request, [
            'maintype'=>'required|max:30',
        ]);
        $resp = $this->service->delete($validated['maintype']);
        return response()->json($resp['data'], $resp['status']);
    }

    public function restore(Request $request){
        $validated = $this->validate($request, [
            'maintype'=>'required|max:30',
        ]);
        return response()->json($this->service->restore($validated['maintype']));
    }

    public function setItem(Request $request){
        $validated = $this->validate($request, [
            'id'=>'required|numeric',
            'name'=>'required|max:300',
            'value'=>'required|max:30',
            'parentid'=>'nullable|max:30',
            'maintype'=>'nullable|max:30',
        ]);
        $resp = $this->service->setItem($validated['id'], $validated['value'], $validated['name'], @$validated['parentid'], @$validated['maintype']);
        return response()->json($resp['data'], $resp['status']);
    }

    public function addItem(Request $request){
        $validated = $this->validate($request, [
            'parentid'=>'required|max:30',
            'name'=>'required|max:300',
            'value'=>'required|max:30',
            'maintype'=>'nullable|max:30',
        ]);
        return response()->json($this->service->addItem($validated['parentid'], $validated['value'], $validated['name'], @$validated['maintype']));
    }

    public function deleteItem(Request $request){
        $validated = $this->validate($request, [
            'id'=>'required|numeric',
        ]);
        $resp = $this->service->deleteItem($validated['id']);
        return response()->json($resp['data'], $resp['status']);
    }

    public function getDicWithChildren(Request $request){
        $validated = $this->validate($request, [
            'maintype'=>'required|max:30',
        ]);
        return response()->json($this->service->getDicWithChildren($validated['maintype']));
    }

    //
}
