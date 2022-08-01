<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class CustRole extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public $table = "cust_role";
    public $primaryKey = "roleid";
    public $timestamps = false;

    public $fillable = [
        'instid',
        'roleid',
        'rolename',
        'rolenameeng',
        'statusid',
        'listorder'
    ];

    protected $casts = [
        'instid' => 'integer',
        'roleid' => 'integer',
        'rolename' => 'string',
        'rolenameeng' => 'string',
        'statusid' => 'integer',
        'listorder' => 'float'
    ];

    public static function getValidator()
    {
        return [
            'instid' => 'nullable|max:60',
            'roleid' => 'nullable|max:60',
            'rolename' => 'required|max:60',
            'rolenameeng' => 'required|max:60',
            'listorder' => 'nullable|max:10',
            'statusid' => 'nullable|integer',
            'perms' => 'nullable|array',
        ];
    }

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */


    public function perms()
    {
        // return $this->hasManyThrough(ModulePerm::class, RolePerm::class, 'roleid', 'permid', 'roleid', 'permid')->where("ROLE_PERMS.STATUSID", "<>", -1);
        return $this->hasMany(CustRolePerms::class, 'roleid', 'roleid')->where('statusid', 1);
    }

    public function inst()
    {
        return $this->belongsTo(Inst::class, 'instid', 'id');
    }

    public function setPerms($perms = [])
    {
        $newPermIds = [];
        $userid = auth()->user()->userid;
        foreach ($perms as $permid) {
            $newPermIds[] = $permid;
        }

        foreach ($this->perms as $rp) {
            $permid = $rp->permid;
            if (auth()->user()->instid == $rp->instid) {
                if (in_array($permid, $newPermIds) === false) {
                    $rp->update([
                        'statusid' => -1,
                        'updated_by' => $userid,
                        'updated_at' => Carbon::now(),
                    ]);
                }
            }
        }

        foreach ($perms as $permid) {
            $rolePerm = CustRolePerms::where('roleid', $this->roleid)->where('permid', $permid)->where('instid', auth()->user()->instid)->where('statusid', '<>', -1)->first();
            if (!$rolePerm) {
                CustRolePerms::create([
                    'roleid' => $this->roleid,
                    'permid' => $permid,
                    'statusid' => 1,
                    'created_by' => $userid,
                    'created_at' => Carbon::now(),
                    'instid' => auth()->user()->instid
                ]);
            }
        }
    }
}
