<?php

namespace App\Http\Controllers;

use App\Models\InstUser;
use App\Services\GoogleAuthenticator\GoogleAuthenticator;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class GoogleAuthenticatorController extends Controller
{
    protected $service;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(GoogleAuthenticator $service)
    {
        $this->service = $service;
    }

    public function getAuthQrCode(Request $request)
    {
        $validated = $this->validate($request, [
            'secret' => 'required|string',
        ]);
        $user = auth()->user();
        return $this->service->getUrl(config('app.name'), $user->email, $validated['secret']);
    }

    public function getAuthCheckCode(Request $request)
    {
        $validated = $this->validate($request, [
            'secret' => 'required|string',
            'code' => 'required',
        ]);
        $res = array();
        if ($this->service->checkCode($validated['secret'], $validated['code'])) {
            $res['isSuccess'] = true;
        } else {
            $res['isSuccess'] = false;
        }
        return response()->json($res);
    }

    public function updateUseGoogleAuth(Request $request)
    {
        $validated = $this->validate($request, [
            'useGoogleAuth' => 'required',
            'id' => 'required'
        ]);
        try {
            $user = InstUser::where('userid', $validated['id'])->where('status', '<>', '-1')->first();
            $user->use_google_auth = $validated['useGoogleAuth'];
            // if ($validated['useGoogleAuth'] == '0') {
            //     $user->google_auth_key = $this->service->generateSecret();
            // }
            $user->updated_by = auth()->user()->userid;
            $user->updated_at = Carbon::now();
            $user->save();
            return response()->json('success', 200);
        } catch (QueryException $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function getCode(Request $request)
    {
        $validated = $this->validate($request, [
            'secret' => 'required'
        ]);
        return $this->service->getCode($validated['secret']);
    }
}
