<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\Inst;
use App\Models\CustRole;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustRoleController extends Controller
{
    public function indexDefault(Request $request)
    {
        $user = auth()->user();
        if ($user->instid != Inst::getOwnInstId()) {
            return response()->json("Unauthorized", 401);
        }
        return $this->index($request);
    }

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
            'withPerms' => 'nullable|numeric',
            'withInst' => 'nullable|numeric'
        ]);
        $roles = CustRole::where('statusid', '<>', '-1');
        $user = auth()->user();
        if ($user->isadmin != '1') {
            $roles->where('instid', $user->instid);
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

    public function store(Request $request)
    {
        $validated = $this->validate($request, [
            'instid' => 'nullable',
            'roleid' => 'nullable|max:60',
            'rolename' => 'required|max:60',
            'rolenameeng' => 'required|max:60',
            'listorder' => 'nullable|max:10',
            'statusid' => 'nullable|integer',
            'perms' => 'nullable|array'
        ]);

        if (!array_key_exists('listorder', $validated)) {
            $validated['listorder'] = 0;
        }
        $user = auth()->user();
        if ($user->isadmin == '1') {
            return response()->json('Та хандах эрхгүй байна.', 500);
        }
        try {
            DB::beginTransaction();
            $role = new CustRole();
            $validated['instid'] = $user->instid;
            foreach ($validated as $key => $data) {
                if ($key != "perms" && in_array($key, $role->fillable) !== false) {
                    $role[$key] = $data;
                }
            }

            $role->statusid = 1;
            $role->created_at = Carbon::now();
            $role->created_by = $user->userid;
            $role->save();

            if (array_key_exists('perms', $validated)) {
                $role->setPerms($validated['perms']);
            }

            DB::commit();
            $role = CustRole::with(['perms'])->where('roleid', $role->roleid)->first();
            return response()->json($role, 200);
        } catch (QueryException $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 500);
        }
    }

    public function show(Request $request)
    {
        try {
            $user = auth()->user();
            $validated = $this->validate($request, [
                'roleid' => 'required|numeric',
                'withPerms' => 'nullable|numeric',
            ]);
            if ($user->isadmin == '1') {
                $role = CustRole::where('roleid', $validated['roleid'])->where('statusid', '<>', '-1');
            } else {
                $role = CustRole::where('roleid', $validated['roleid'])->where('instid', $user->instid)->where('statusid', '<>', '-1');
            }

            if (@$request['withPerms'] == 1) {
                $role = $role->with(['perms:roleid,permid', 'perms.perm:permid,permname,moduleid']);
            }
            $role = $role->first();
            return response()->json($role);
        } catch (QueryException $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function update(Request $request)
    {

        $user = auth()->user();
        if ($user->isadmin == '1') {
            return response()->json('Та мэдээллийг өөрчлөх эрхгүй байна.', 500);
        }

        $validated = $this->validate($request, [
            'roleid' => 'required|max:60',
            'rolename' => 'required|max:60',
            'rolenameeng' => 'required|max:60',
            'listorder' => 'nullable|max:10',
            'perms' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();
            $role = CustRole::with(['perms'])->where('roleid', $validated['roleid'])->where('instid', $user->instid)->where('statusid', '<>', '-1')->first();
            if (!$role) {
                return response()->json("CustRole not found!", 404);
            }

            foreach ($validated as $key => $data) {
                if ($key != "perms" && in_array($key, $role->fillable) !== false) {
                    $role[$key] = $data;
                }
            }
            $role['updated_at'] = Carbon::now();
            $role['updated_by'] = $user->userid;

            $role->save();

            if (array_key_exists('perms', $validated)) {
                $role->setPerms($validated['perms']);
            }
            DB::commit();
            $role = CustRole::with(['perms'])->where('roleid', $role->roleid)->first();
            return response()->json($role, 200);
        } catch (QueryException $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 500);
        }
    }

    public function destroy(Request $request)
    {
        $user = auth()->user();
        if ($user->isadmin == '1') {
            return response()->json('Та мэдээллийг өөрчлөх эрхгүй байна.', 500);
        }
        try {
            $validated = $this->validate($request, [
                'roleid' => 'required|numeric',
            ]);

            $role = CustRole::where('instid', $user->instid)->where('roleid', $validated['roleid'])->where('statusid', '<>', '-1')->first();
            if (!$role) {
                return response()->json('CustRole not found!', 404);
            }
            $role->setPerms([]);
            $role->update(['statusid' => -1, 'updated_by' => auth()->user()->userid, 'updated_at' => Carbon::now()]);

            return response()->json('CustRole deleted', 200);
        } catch (QueryException $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function restore(Request $request)
    {
        try {
            $validated = $this->validate($request, [
                'roleid' => 'required|numeric',
            ]);
            $role = CustRole::where('roleid', $validated['roleid'])->where('statusid', '-1')->first();
            if (!$role) {
                return response()->json('CustRole not deleted!', 404);
            }

            $role->update(['statusid' => 1, 'updated_by' => auth()->user()->userid, 'updated_at' => Carbon::now()]);

            return response()->json('CustRole restored', 200);
        } catch (QueryException $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
}
