<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Services\CustUserAccountService;
use Illuminate\Http\Request;

class CustUserAccountController extends Controller
{

    protected $service;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(CustUserAccountService $service)
    {
        $this->service = $service;
    }

    /**
     * Харилцагчийн холбогдсон авах
     *
     * @param  mixed $request
     * @return void
     */
    public function getOwnAccount(Request $request)
    {
        $data = $this->service->getConnAccount();
        return response()->json($data['data'], $data['status']);
    }

    /**
     * Харилцагчийн данс холбох
     *
     * @param  mixed $request
     * @return void
     */
    public function createAccount(Request $request)
    {
        $validated = $this->validate($request, [
            'acnt_code' => 'required',
            'acnt_name' => 'required',
            'bank_code' => 'required',
        ]);
        $validated['token'] = rand(100000, 999999);
        $data = $this->service->store($validated);
        return response()->json($data['data'], $data['status']);
    }

    /**
     * Харилцагчийн данс баталгаажуулах
     *
     * @param  mixed $request
     * @return void
     */
    public function confirmAccount(Request $request)
    {
        $validated = $this->validate($request, [
            'acnt_code' => 'required',
            'token' => 'required',
        ]);
        $data = $this->service->store($validated);
        return response()->json($data['data'], $data['status']);
    }

    /**
     * Харилцагчийн данс салгах
     *
     * @param  mixed $request
     * @return void
     */
    public function deleteAccount(Request $request)
    {
        $validated = $this->validate($request, [
            'acnt_code' => 'required'
        ]);
        $this->service->delete($validated['acnt_code']);
    }
}
