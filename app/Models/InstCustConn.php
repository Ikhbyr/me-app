<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class InstCustConn extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public $table = "inst_cust_conn";
    public $primaryKey = "id";
    public $timestamps = false;

    public $fillable = [
        'id',
        'instid',
        'custid',
        'created_by',
        'created_at',
        'udpated_by',
        'updated_at',
        'statusid',
    ];

    protected $casts = [
        'id' => 'integer',
        'instid' => 'integer',
        'custid' => 'integer',
        'statusid' => 'integer',
        'created_at' => 'date:Y-m-d',
        'created_by' => 'integer',
        'updated_at' => 'date:Y-m-d',
        'updated_by' => 'integer',
    ];

    public function insts()
    {
        return $this->hasMany(Inst::class, 'id', 'instid')->where('statusid', 1);
    }

    public function custusers()
    {
        return $this->belongsTo(CustUser::class, 'userid', 'custid')->where('status', 1);
    }
}
