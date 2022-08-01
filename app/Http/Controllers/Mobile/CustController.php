<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Cust;
use Illuminate\Http\Request;

class CustController extends Controller
{
    public function getDetail(Request $request)
    {
        $validated = $this->validate($request, [
            'instid' => 'required'
        ]);
        $user = auth()->user();
        if (!checkInstPerm('cu0410', $validated['instid'], $user)) {
            return response()->json('[cu0410] эрх олгогдоогүй байна.', 500);
        }
        $cust = Cust::with(['inst:id,instname,instnameeng'])->where('regno', $user->registernum)->where('instid', $validated['instid'])->where('statusid', '<>', '-1')->first();
        if (empty($cust)) {
            return response()->json('Customer not found', 500);
        }
        return response()->json($cust);
    }
}
