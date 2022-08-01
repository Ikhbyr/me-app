<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class InstUserRole extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    public $table = "inst_user_role";
    public $primaryKey = "id";
    public $timestamps = false;
    // public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'userid',
        'roleid',
        'instid',
        'isadmin',
        'statusid',
        'startdate',
        'enddate',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
    ];

    protected $casts = [
        'id'=>'integer',
        'userid'=>'integer',
        'roleid'=>'integer',
        'instid'=>'integer',
        'statusid'=>'integer',
        'startdate'=>'date:Y-m-d',
        'enddate'=>'date:Y-m-d',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
    ];

    function user() {
        return $this->belongsTo(InstUser::class, 'userid', 'userid')->where('status', '<>', -1);
    }

    function role() {
        return $this->belongsTo(InstRole::class, 'roleid', 'roleid')->where('statusid', '<>', -1);
    }
}
