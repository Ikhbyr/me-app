<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class InstUserRolePerms extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

     public $table = "inst_user_role_perms";
     public $primaryKey = "id";
     public $timestamps = false;

     protected $fillable = [
        'id',
        'roleid',
        'permid',
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
        'statusid' => 'integer',
        'created_at' => 'date:Y-m-d',
        'created_by' => 'integer',
        'updated_at' => 'date:Y-m-d',
        'updated_by' => 'integer',
    ];

    public function perm()
    {
        return $this->belongsTo(ModulePerm::class, 'permid', 'permid');
    }

    public function role()
    {
        return $this->belongsTo(InstRole::class, 'roleid', 'roleid');
    }
}
