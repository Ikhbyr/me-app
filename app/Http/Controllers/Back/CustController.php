<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\Cust;
use Illuminate\Http\Request;
use App\Http\Resources\CustDetailResource;

class CustController extends Controller
{
    public function index(Request $request)
    {
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
            'withInst' => 'nullable|numeric'
        ]);
        $roles = Cust::select(
            'id',
            'cif',
            'gender',
            'lname',
            'fname',
            'regno',
            'phone'
        )->where('statusid', '<>', '-1');
        $user = auth()->user();
        if ($user->isadmin != '1') {
            $roles->where('instid', $user->instid);
        }

        if (@$validated['withInst'] == 1) {
            $roles = $roles->with(['inst:id,instname,instnameeng']);
        }

        $roles = $this->applyFilters($roles, @$validated['filters']);
        $roles = $this->applyOrders($roles, @$validated['orders']);
        $roles = $this->applyPaginate($roles, @$validated['perPage'], @$validated['page']);

        return response()->json($roles);
    }

    public function getDetail(Request $request)
    {
        $validated = $this->validate($request, [
            'id' => 'required',
            'instid' => 'nullable'
        ]);
        $roles = Cust::with(['inst:id,instname,instnameeng'])->where('id', $validated['id'])->where('statusid', '<>', '-1');
        $user = auth()->user();
        if ($user->isadmin != '1') {
            $roles->where('instid', $user->instid);
        } else {
            if (!@$validated['instid']) {
                $validated['instid'] = $user->instid;
            }
            $roles->where('instid', $validated['instid']);
        }

        $roles = $roles->first();
        if (empty($roles)) {
            return response()->json('Customer not found', 500);
        }

        $roles = new CustDetailResource($roles);
        return response()->json($roles);
    }
}
