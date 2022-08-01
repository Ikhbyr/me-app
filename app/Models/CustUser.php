<?php

namespace App\Models;

use Carbon\Carbon;
use Exception;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Auth\Authorizable;
use OwenIt\Auditing\Contracts\Auditable;

class CustUser extends Model implements AuthenticatableContract, AuthorizableContract, Auditable
{
    use Authenticatable, Authorizable, HasFactory;
    use \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public $table = "cust_user";
    public $primaryKey = "userid";
    public $timestamps = false;

    public $fillable = [
        'userid',
        'email',
        'phoneuser',
        'password',
        'status',
        'createuser',
        'createdate',
        'updated_by',
        'updated_at',
        'passdate',
        'passwrong',
        'registernum',
        'iprest',
        'startdate',
        'enddate',
        'mustchgpass',
        'color',
        'domain_verified',
        'phone_verified',
        'passtoken',
        'passtokendate',
        'passtokenstatus',
        'firstname',
        'lastname',
        'branchid',
        'google_auth_key',
        'password_changed_at',
        'photo_url',
        'address',
        'region',
        'subregion',
        'device_token'
    ];

    protected $casts = [
        'userid' => 'integer',
        'email' => 'string',
        'phoneuser',
        'password' => 'string',
        'status' => 'integer',
        'created_by' => 'integer',
        'created_at' => 'date:Y-m-d H:i:s',
        'updated_by' => 'integer',
        'updated_at' => 'date:Y-m-d H:i:s',
        'passdate' => 'date:Y-m-d',
        'passwrong' => 'integer',
        'registernum' => 'string',
        'iprest' => 'string',
        'startdate' => 'date:Y-m-d',
        'enddate' => 'date:Y-m-d',
        'mustchgpass' => 'string',
        'color',
        'domain_verified',
        'phone_verified',
        'passtoken',
        'passtokendate',
        'passtokenstatus',
        'firstname',
        'lastname',
        'branchid' => 'integer',
        'google_auth_key',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'passwrong',
        'passtoken',
        'passtokendate',
        'passtokenstatus',
        'domain_verified',
        'google_auth_key',
        'device_token'
    ];

    public function tokens()
    {
        return $this->hasMany(UserAccessToken::class, 'userid', 'userid');
    }

    public function insts()
    {
        return $this->belongsToMany(Inst::class, 'inst_cust_conn')->select(['inst.id', 'instname', 'instnameeng', 'logo', 'color'])->where('inst_cust_conn.statusid', 1);
    }

    public function roles()
    {
        return $this->hasMany(CustUserRole::class, 'userid', 'userid')->where('statusid', '<>', '-1');
    }

    public function instRoles()
    {
        return $this->hasMany(CustUserRole::class, 'userid', 'userid')->where('statusid', '<>', '-1')->where('instid', auth()->user()->instid);
    }

    public function activeRoles()
    {
        return $this->hasMany(CustUserRole::class, 'userid', 'userid')->where('statusid', '=', '1')->where('startdate', '<=', Carbon::now())->where('enddate', '>=', Carbon::now());
    }

    public function setRoles($roles)
    {
        $newRoleIds = [];
        $user = auth()->user();
        foreach ($roles as $role) {
            $newRoleIds[] = $role['roleid'];
        }

        foreach ($this->roles as $role) {
            if (in_array($role->roleid, $newRoleIds) === false) {
                if ($role->instid == $user->instid) {
                    $role->delete();
                }
            }
        }

        foreach ($roles as $role) {
            $userRole = CustUserRole::where('userid', $this->userid)->where('roleid', $role['roleid'])->where('instid', $user->instid)->first();
            if ($userRole) {
                $userRole->update([
                    'statusid' => $role['statusid'],
                    'instid' => $user->instid,
                    'startdate' => Carbon::createFromFormat("Y-m-d", $role['startdate']),
                    'enddate' => Carbon::createFromFormat("Y-m-d", $role['enddate']),
                    'updated_at' => Carbon::now(),
                    'updated_by' => auth()->user()->userid,
                ]);
            } else {
                CustUserRole::create([
                    'userid' => $this->userid,
                    'roleid' => $role['roleid'],
                    'instid' => $user->instid,
                    'statusid' => $role['statusid'],
                    'startdate' => $role['startdate'],
                    'enddate' => $role['enddate'],
                    'created_at' => Carbon::now(),
                    'password_changed_at' => Carbon::now(),
                    'created_by' => auth()->user()->userid,
                ]);
            }
        }
    }

    public function passwordHistories()
    {
        return $this->hasMany(UserPassHist::class, 'userid', 'userid');
    }

    public function changePassword($newPassword)
    {
        try {
            $newPassword = Hash::make($newPassword);
            if ($this->password == $newPassword || $this->passwordHistories()->where('password', $newPassword)->first()) {
                throw new Exception("Duplicated password!");
            }
            DB::beginTransaction();
            $this->passwordHistories()->create([
                'userid' => $this->userid,
                'passdate' => $this->passdate,
                'password' => $this->password,
                'createdate' => Carbon::now(),
            ]);
            $this->password = $newPassword;
            $this->passdate = Carbon::now();
            $this->passtokenstatus = 0;
            $this->save();
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function checkPerm($permid)
    {
        if ($this->roles()->whereHas('role', function ($q) use ($permid) {
            $q->whereHas('perms', function ($qq) use ($permid) {
                $qq->where('permid', $permid);
            });
        })->first()) {
            return true;
        }

        return false;
    }
}
