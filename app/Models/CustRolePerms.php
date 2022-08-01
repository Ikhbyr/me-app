<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class CustRolePerms extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

     public $table = "cust_role_perms";
     public $primaryKey = "roleid";
     public $timestamps = false;

    public $fillable = [
        'id',
        'roleid',
        'permid',
        'instid',
        'statusid',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
    ];

    protected $casts = [
        'id' => 'integer',
        'roleid' => 'integer',
        'permid' => 'string',
        'instid' => 'integer',
        'statusid' => 'integer',
        'created_at' => 'date:Y-m-d',
        'created_by' => 'integer',
        'updated_at' => 'date:Y-m-d',
        'updated_by' => 'integer',
    ];

    public function perm()
    {
        return $this->belongsTo(MobilePerms::class, 'permid', 'permid');
    }

    public function role()
    {
        return $this->belongsTo(CustRole::class, 'roleid', 'roleid');
    }
}
