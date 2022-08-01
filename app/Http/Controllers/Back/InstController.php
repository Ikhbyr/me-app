<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
//use App\Models\Client;
use App\Models\Inst;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InstController extends Controller
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
        ]);
        $insts = Inst::where('statusid', '<>', '-1');
        $user = auth()->user();
        if ($user->isadmin != '1') {
            $insts->where('id', $user->instid);
        }
        if (@$validated['withPerms'] == 1) {
            $insts = $insts->with(['perms', 'perms.perm:permid,permname,permnameeng,moduleid']);
        }
        // if (@$validated['withClient'] == 1) {
        //     $insts = $insts->with(['client:clientcode,clientid,clientname']);
        // }

        $insts = $this->applyFilters($insts, @$validated['filters']);
        $insts = $this->applyOrders($insts, @$validated['orders']);
        $insts = $this->applyPaginate($insts, @$validated['perPage'], @$validated['page']);

        return response()->json($insts);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $validated = $this->validate($request, [
            'instname' => 'required|max:100',
            'instnameeng' => 'nullable|max:100',
            'regno' => 'required|max:20',
            'nationid' => 'nullable|max:100',
            'stabledate' => 'nullable|max:100',
            'inst_typeid' => 'nullable|max:100',
            'license_typeid' => 'nullable|max:100',
            'email' => 'required|max:100',
            'phone' => 'required|max:100',
            'dir_name' => 'required|max:100',
            'color' => 'required|max:100',
            'logo' => 'nullable|max:100',
            'state' => 'nullable|max:100',
            'region' => 'nullable|max:100',
            'subregion' => 'nullable|max:100',
            'street' => 'nullable|max:300',
            'zipcode' => 'nullable|max:10',
            'listorder' => 'nullable',
        ]);

        if (!array_key_exists('listorder', $validated)) {
            $validated['listorder'] = 0;
        }
        try {
            DB::beginTransaction();
            $inst = new Inst();

            foreach ($inst->fillable as $field) {
                if (array_key_exists($field, $validated)) {
                    $inst->$field = $validated[$field];
                }
            }
            $inst->statusid = 1;
            $inst->created_at = Carbon::now();
            $inst->created_by = $user->userid;
            $inst->save();

            if (array_key_exists('perms', $validated)) {
                $inst->setPerms($validated['perms']);
            }

            DB::commit();
            $inst = Inst::find($inst->id);
            return response()->json($inst, 200);
        } catch (QueryException $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 500);
        }
    }

    public function show(Request $request)
    {
        try {
            $validated = $this->validate($request, [
                'id' => 'required|numeric',
                'withPerms' => 'nullable|numeric',
            ]);
            $inst = Inst::where('id', $validated['id'])->where('statusid', '<>', '-1');
            $withs = [];
            $withs[] = 'type:value,name';
            if (@$request['withPerms'] == 1) {
                $withs[] = 'perms:id,permid';
                $withs[] = 'perms.perm:permid,permname,moduleid';
            }
            $inst = $inst->with($withs);
            $inst = $inst->first();
            if (empty($inst)) {
                return response()->json('Not found institution.', 500);
            }
            if (!empty($inst->logo)) {
                try {
                    $inst['logo_base64'] = base64_encode(file_get_contents(env('APP_URL') . $inst->logo));
                } catch (Exception $ex) {
                    $inst['logo_base64'] = "";
                }
            }
            return response()->json($inst);
        } catch (QueryException $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function getOwnInst()
    {
        try {
            $user = auth()->user();
            $inst = Inst::select(
                'id',
                'inst_typeid',
                'instname',
                'regno',
                'region',
                'subregion',
                'street'
            )->where('id', $user->instid)->where('statusid', '<>', '-1');
            $withs = [];
            $withs[] = 'type:value,name';
            $withs[] = 'oregion:parentid,value,name,maintype';
            $inst = $inst->with($withs);
            $inst = $inst->first();
            if (empty($inst)) {
                return response()->json('Not found institution.', 500);
            }
            return response()->json($inst);
        } catch (QueryException $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function update(Request $request)
    {
        $validated = $this->validate($request, [
            'id' => 'required',
            'instname' => 'required|max:100',
            'instnameeng' => 'nullable|max:100',
            'regno' => 'required|max:20',
            'nationid' => 'nullable|max:100',
            'stabledate' => 'nullable|max:100',
            'inst_typeid' => 'nullable|max:100',
            'license_typeid' => 'nullable|max:100',
            'email' => 'required|max:100',
            'phone' => 'required|max:100',
            'dir_name' => 'required|max:100',
            'color' => 'required|max:100',
            'logo' => 'nullable|max:100',
            'state' => 'nullable|max:100',
            'region' => 'nullable|max:100',
            'subregion' => 'nullable|max:100',
            'street' => 'nullable|max:300',
            'zipcode' => 'nullable|max:10',
            'listorder' => 'nullable',
        ]);

        try {
            DB::beginTransaction();
            $inst = Inst::where('id', $validated['id'])->where('statusid', '<>', '-1')->first();
            if (!$inst) {
                return response()->json("Inst not found!", 404);
            }

            foreach ($validated as $key => $data) {
                if ($key != "perms" && in_array($key, $inst->fillable) !== false) {
                    $inst[$key] = $data;
                }
            }
            $inst['updated_at'] = Carbon::now();
            $inst['updated_by'] = auth()->user()->userid;

            $inst->save();

            if (array_key_exists('perms', $validated)) {
                $inst->setPerms($validated['perms']);
            }
            DB::commit();
            $inst = Inst::find($inst->id);
            return response()->json($inst, 200);
        } catch (QueryException $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 500);
        }
    }

    public function destroy(Request $request)
    {
        $user = auth()->user();
        try {
            $validated = $this->validate($request, [
                'id' => 'required|numeric',
            ]);

            $inst = Inst::where('id', $validated['id'])->where('statusid', '<>', '-1')->first();
            if (!$inst) {
                return response()->json('Inst not found!', 404);
            }

            $inst->statusid = -1;
            $inst->updated_by = $user->userid;
            $inst->updated_at = Carbon::now();
            $inst->save();
            return response()->json('Inst deleted', 200);
        } catch (QueryException $e) {
            return response()->json($e->getMessage(), 500);
        }
    }


    public function setPerms(Request $request)
    {
    }
}
