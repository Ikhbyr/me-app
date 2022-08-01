<?php

namespace App\Http\Controllers;

use App\Services\PolarisApiRequestService;
use Illuminate\Http\Request;

class CoreSystemController extends Controller
{

    public function getPolarisTime(Request $request)
    {
        $validated = $this->validate($request, [
            'instid' => 'required'
        ]);

        $polaris = new PolarisApiRequestService($validated['instid']);
        $respdata = $polaris->getDate($validated['instid']);
        if ($respdata['status'] == 200) {
            return response()->json($respdata['data']);
        } else {
            return response()->json('Уучлаарай, системийн цаг авч чадсангүй.', 500);
        }
    }
}
