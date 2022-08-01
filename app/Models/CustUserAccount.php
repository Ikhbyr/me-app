<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class CustUserAccount extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    public $table = "cust_user_account";
    public $primaryKey = "id";
    public $timestamps = false;
    // public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cust_user_id',
        'acnt_code',
        'acnt_name',
        'token',
        'confirmed_at',
        'instid',
        'statusid',
        'created_at',
        'created_by',
    ];

    protected $casts = [
        'cust_user_id' => 'integer',
        'statusid' => 'integer',
        'confirmed_at' => 'date:Y-m-d H:i:s',
        'created_at' => 'date:Y-m-d H:i:s',
        'created_by' => 'integer',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'token',
        'confirmed_at',
    ];

    function user() {
        return $this->belongsTo(CustUser::class, 'userid', 'userid')->where('status', '<>', -1);
    }
}
