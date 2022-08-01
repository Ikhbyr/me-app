<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\InstRole;
use App\Services\InstUserRoleService;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class InstRoleController extends Controller
{

    protected $service;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(InstUserRoleService $service)
    {
        $this->service = $service;
    }

    public function indexAdmin(Request $request)
    {
        $user = auth()->user();
        if ($user->instid != getOwnInstId()) {
            return response()->json("Та хандах эрхгүй байна.", 500);
        }
        $roles = InstRole::where('isadmin', 1)->where('statusid', '<>', '-1');
        return $this->service->index($request, $roles);
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        if ($user->isadmin == '1') {
            $roles = InstRole::where('isadmin', '<>', '1')->where('statusid', '<>', '-1');
        } else {
            $roles = InstRole::where('isadmin', '<>', '1')->where('statusid', '<>', '-1')->where('instid', $user->instid);
        }
        return $this->service->index($request, $roles);
    }

    public function store(Request $request)
    {
        $request['isadmin'] = 0;
        return $this->service->store($request);
    }

    public function storeAdmin(Request $request)
    {
        $user = auth()->user();
        if ($user->instid != getOwnInstId()) {
            return response()->json("Та хандах эрхгүй байна.", 500);
        }
        $request['isadmin'] = 1;
        $request['instid'] = getOwnInstId();
        return $this->service->store($request);
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
                $role = InstRole::where('roleid', $validated['roleid'])->where('isadmin', '<>', '1')->where('statusid', '<>', '-1');
            } else {
                $role = InstRole::where('roleid', $validated['roleid'])->where('isadmin', '<>', '1')->where('instid', $user->instid)->where('statusid', '<>', '-1');
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

    public function showAdmin(Request $request)
    {
        try {
            $user = auth()->user();
            if ($user->instid != getOwnInstId()) {
                return response()->json("Та хандах эрхгүй байна.", 500);
            }
            $validated = $this->validate($request, [
                'roleid' => 'required|numeric',
                'withPerms' => 'nullable|numeric',
            ]);

            $role = InstRole::where('roleid', $validated['roleid'])->where('instid', $user->instid)->where('statusid', '<>', '-1');
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
            $role = InstRole::with(['perms'])->where('roleid', $request['roleid'])->where('isadmin', '<>', '1')->where('statusid', '<>', '-1')->first();
        } else {
            $role = InstRole::with(['perms'])->where('roleid', $request['roleid'])->where('isadmin', '<>', '1')->where('instid', $user->instid)->where('statusid', '<>', '-1')->first();
        }
        if (!$role) {
            return response()->json("InstRole not found!", 404);
        }
        return $this->service->update($request, $role);
    }

    public function updateAdmin(Request $request)
    {
        $user = auth()->user();
        if ($user->instid != getOwnInstId()) {
            return response()->json("Та хандах эрхгүй байна.", 500);
        }

        // Admin онцгой эрхтэй учраас заавал админ Role Байх шаардлаггүй засвар хийгдэнэ.
        $role = InstRole::with(['perms'])->where('roleid', $request['roleid'])->where('statusid', '<>', '-1')->first();
        if (!$role) {
            return response()->json("InstRole not found!", 404);
        }
        return $this->service->update($request, $role);
    }

    public function destroy(Request $request)
    {
        $user = auth()->user();
        try {
            $validated = $this->validate($request, [
                'instid' => 'required|numeric',
                'roleid' => 'required|numeric',
            ]);
            if ($user->isadmin == '1') {
                $role = InstRole::where('instid', $validated['instid'])->where('isadmin', '<>', '1')->where('roleid', $validated['roleid'])->where('statusid', '<>', '-1')->first();
            } else {
                $role = InstRole::where('instid', $validated['instid'])->where('isadmin', '<>', '1')->where('instid', $user->instid)->where('roleid', $validated['roleid'])->where('statusid', '<>', '-1')->first();
            }
            if (!$role) {
                return response()->json('InstRole not found!', 404);
            }
            $role->setPerms([]);
            $role->update(['statusid' => -1, 'updated_by' => auth()->user()->userid, 'updated_at' => Carbon::now()]);

            return response()->json('InstRole deleted', 200);
        } catch (QueryException $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function destroyAdmin(Request $request)
    {
        $user = auth()->user();
        if ($user->instid != getOwnInstId()) {
            return response()->json("Та хандах эрхгүй байна.", 500);
        }
        try {
            $validated = $this->validate($request, [
                'roleid' => 'required|numeric',
            ]);
            $role = InstRole::where('instid', getOwnInstId())->where('roleid', $validated['roleid'])->where('statusid', '<>', '-1')->first();
            if (!$role) {
                return response()->json('InstRole not found!', 404);
            }
            $role->setPerms([]);
            $role->update(['statusid' => -1, 'updated_by' => auth()->user()->userid, 'updated_at' => Carbon::now()]);

            return response()->json('InstRole deleted', 200);
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
            $role = InstRole::where('roleid', $validated['roleid'])->where('statusid', '-1')->first();
            if (!$role) {
                return response()->json('InstRole not deleted!', 404);
            }

            $role->update(['statusid' => 1, 'updated_by' => auth()->user()->userid, 'updated_at' => Carbon::now()]);

            return response()->json('InstRole restored', 200);
        } catch (QueryException $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function setPerms(Request $request)
    {
    }
}
