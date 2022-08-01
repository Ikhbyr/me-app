<?php

namespace App\Http\Controllers\Mobile;

use App\Models\Contract;
use App\Services\ContractService;
use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ContactController extends Controller
{
    public function show(Request $request){
        $validated = $this->validate($request, [
            'instid'=>'required|numeric',
            'type_id'=>'required'
        ]);
        $contract = Contract::where('statusid', '<>', -1)->where('instid', $validated['instid'])->where('type_id', $validated['type_id'])->first();
        if (!$contract){
            return response()->json("Contract not found!", 500);
        }
        return response()->json($contract);
    }
}
