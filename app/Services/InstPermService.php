<?php

namespace App\Services;

use App\Models\Inst;
use App\Models\InstPerm;
use App\Models\InstTxn;
use App\Models\ModulePerm;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class InstPermService
{

    public function getPerms($instid, $moduleid = false)
    {
        $inst = Inst::find($instid);
        if (!$inst) throw new Exception("Inst [{$instid}] not found");
        return InstPerm::with(['perm:permid,moduleid,permname,permnameeng', 'module:moduleid,modulename,modulenameeng'])->where('statusid', 1)->where('instid', $instid)->orderBy('moduleid')->get();
    }

    public function setPerms($instid, $perms)
    {
        $user = auth()->user();
        try {
            DB::beginTransaction();
            $currents = $this->getPerms($instid);
            $c = [];
            foreach ($currents as $current) {
                if (in_array($current->permid, $perms) === false) {
                    //dd($current->permid);
                    $current->statusid = -1;
                    $current->updated_at = Carbon::now();
                    $current->updated_by = $user->userid;
                    $current->save();
                } else {
                    $c[] = $current->permid;
                }
            }

            foreach ($perms as $permid) {
                if (in_array($permid, $c) === false) {
                    $perm = ModulePerm::where('permid', $permid)->first();
                    if ($perm) {
                        $cp = new InstPerm();
                        $cp->instid = $instid;
                        $cp->permid = $perm->permid;
                        $cp->moduleid = $perm->moduleid;
                        $cp->statusid = 1;
                        $cp->created_at = Carbon::now();
                        $cp->created_by = $user->userid;
                        $cp->save();
                    }
                }
            }
            DB::commit();
            return response()->json("Successfully updated");
        }
        catch (Exception $e){
            DB::rollBack();
            return response()->json($e->getMessage(), 500);
        }
    }

    function list($deleted = false) {
        if ($deleted) {
            $account_list = InstPerm::where('statusid', -1);
        } else {
            $account_list = InstPerm::where('statusid', '<>', -1);
        }

        return $account_list;
    }

    public function get($accountNo)
    {
        $instAccount = $this->list()->where('accountno', $accountNo);
        return $instAccount;
    }

    public function update($accountNo, $data)
    {
        $instAccount = $this->get($accountNo)->first();
        if (!$instAccount) {
            throw new Exception("Account [{$accountNo}] not found!");
        }

        $account = InstPerm::where('instid', $instAccount->instid)->where('typeid', $data['typeid'])->where('branchno', @$data['branchno'])->where('accountno', '<>', $instAccount->accountno)->first();
        if ($account) {
            throw new Exception("Cannot register account with same type in same branch!");
        }

        foreach ($instAccount->fillable as $field) {
            if (array_key_exists($field, $data)) {
                $instAccount->$field = $data[$field];
            }
        }
        $instAccount->updated_at = Carbon::now();
        $instAccount->updated_by = @$data['userid'] ? @$data['userid'] : auth()->user()->userid;
        $instAccount->save();
        return $instAccount;
    }

    public function store($data)
    {
        $inst = Inst::find($data['instid']);
        if (!$inst) {
            throw new Exception("Inst [{$data['instid']}] not found!");
        }

        $account = InstPerm::where('instid', $inst->instid)->where('typeid', $data['typeid'])->where('branchno', @$data['branchno'])->first();
        if ($account) {
            throw new Exception("Cannot register account with same type in same branch!");
        }

        $instAccount = new InstPerm();
        $instAccount->clientId = $inst->instid;
        foreach ($instAccount->fillable as $field) {
            if (array_key_exists($field, $data)) {
                $instAccount->$field = $data[$field];
            }
        }
        $instAccount->accountNo = InstPerm::generateAccoutNo();
        $instAccount->statusId = 1;
        $instAccount->created_at = Carbon::now();
        $instAccount->created_by = auth()->user()->userid;
        $instAccount->balance = 0;
        $instAccount->tmp_limit = $data['limit'] ? $data['limit'] : 0;
        $instAccount->save();
        return $instAccount;
    }

    public function delete($accountNo)
    {
        $instAccount = $this->get($accountNo)->first();
        if (!$instAccount) {
            throw new Exception("Account [" . $accountNo . "] not found!");
        }
        if ($instAccount->balance != 0) {
            throw new Exception("Account [" . $accountNo . "] balance must be 0!");
        }
        $instAccount->statusid = -1;
        $instAccount->updated_at = Carbon::now();
        $instAccount->updated_by = auth()->user()->userid;
        $instAccount->save();
        return $instAccount;
    }

    public function restore($accountNo)
    {
        $instAccount = $this->list(true)->where('accountno', $accountNo)->first();
        if (!$instAccount) {
            throw new Exception("Account [" . $accountNo . "] not deleted!");
        }
        $instAccount->statusid = 1;
        $instAccount->updated_at = Carbon::now();
        $instAccount->updated_by = auth()->user()->userid;
        $instAccount->save();
        return $instAccount;
    }

    public function statement($accountno, $startdate, $enddate)
    {
        return InstTxn::where('accountno', $accountno)->whereBetween('txndate', [$startdate, $enddate])->get();
    }
}
