<?php

namespace App\Http\Controllers\Back;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\PolarisApiRequestService;

class PolarisApiController extends Controller
{
    protected $service;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(PolarisApiRequestService $service)
    {
        $this->service = $service;
    }

    public function test(Request $request){
        $validated = $this->validate($request, [
            'operation' => 'required',
            'data' => 'array',
            'instid' => 'required'
        ]);
        $data = $this->service->sendRequest($validated['operation'], $validated['data'], $validated['instid']);
        $objData = $data['data'];
        return $objData;
    }
}
