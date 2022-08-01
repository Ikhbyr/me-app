<?php

namespace App\Services;

use App\Http\Controllers\Controller;
use App\Models\InstRole;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Database\QueryException;

class InstUserRoleService extends Controller
{
    public function index($request, $roles)
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
            'withPerms' => 'nullable|numeric',
            'withInst' => 'nullable|numeric',
            'instid' => 'nullable|numeric',
        ]);

        if (array_key_exists('instid', $validated)) {
            $roles->where('instid', $validated['instid']);
        }

        // return $inst;
        if (@$validated['withPerms'] == 1) {
            $roles = $roles->with(['perms']);
        }
        if (@$validated['withInst'] == 1) {
            $roles = $roles->with(['inst:id,instname,instnameeng']);
        }

        $roles = $this->applyFilters($roles, @$validated['filters']);
        $roles = $this->applyOrders($roles, @$validated['orders']);
        $roles = $this->applyPaginate($roles, @$validated['perPage'], @$validated['page']);

        return response()->json($roles);
    }

    public function store($request)
    {
        $validated = $this->validate($request, [
            'instid' => 'required',
            'roleid' => 'nullable|max:60',
            'rolename' => 'required|max:60',
            'rolenameeng' => 'required|max:60',
            'listorder' => 'nullable|max:10',
            'statusid' => 'nullable|integer',
            'perms' => 'nullable|array',
            'isadmin' => 'required|in:0,1'
        ]);
        if (!array_key_exists('listorder', $validated)) {
            $validated['listorder'] = 0;
        }

        try {
            DB::beginTransaction();
            $role = new InstRole();

            foreach ($validated as $key => $data) {
                if ($key != "perms" && in_array($key, $role->fillable) !== false) {
                    $role[$key] = $data;
                }
            }

            $role->statusid = 1;
            $role->created_at = Carbon::now();
            $role->created_by = auth()->user()->userid;
            $role->save();

            if (array_key_exists('perms', $validated)) {
                $role->setPerms($validated['perms']);
            }

            DB::commit();
            $role = InstRole::with(['perms'])->where('roleid', $role->roleid)->first();
            return response()->json($role, 200);
        } catch (QueryException $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 500);
        }
    }

    public function update($request, $role)
    {
        $validated = $this->validate($request, [
            'instcode' => 'nullable|max:60',
            'roleid' => 'required|max:60',
            'rolename' => 'nullable|max:60',
            'rolenameeng' => 'nullable|max:60',
            'listorder' => 'nullable|max:10',
            'statusid' => 'nullable|integer',
            'perms' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();
            foreach ($validated as $key => $data) {
                if ($key != "perms" && in_array($key, $role->fillable) !== false) {
                    $role[$key] = $data;
                }
            }
            $role['updated_at'] = Carbon::now();
            $role['updated_by'] = auth()->user()->userid;

            $role->save();

            if (array_key_exists('perms', $validated)) {
                $role->setPerms($validated['perms']);
            }
            DB::commit();
            $role = InstRole::with(['perms'])->where('roleid', $role->roleid)->first();
            return response()->json($role, 200);
        } catch (QueryException $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 500);
        }
    }
}
