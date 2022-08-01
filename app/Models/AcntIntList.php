<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class AcntIntList extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public $primaryKey = "id";
    public $timestamps = false;
    public $table = "acnt_int_list";

    public $fillable = [
        'acnt_code',
        'instid',
        'userid',
        'statusid',
        'created_at',
        'created_by',
        'other_info',
        'pay_cust_name',
        'int_rate',
        'source_bal_type',
        'last_acr_info',
        'type',
        'accr_int_amt',
        'int_type_name',
        'int_rate_option',
        'daily_int_amt',
        'last_acr_txn_seq',
        'bal_type_code',
        'int_type_code',
        'last_acr_amt',
        'last_accrual_date',
        'int_lvl',
        'int_lvl_name',
    ];

    protected $casts = [
        'accr_int_amt' => 'double',
        'daily_int_amt' => 'double',
        'last_acr_amt' => 'double',
        'last_accrual_date' => 'date:Y-m-d H:i:s',
        'id' => 'int',
        'instid' => 'int',
        'int_rate' => 'double',
    ];

    public function type()
    {
        return $this->belongsTo(DicMain::class, 'typeid', 'value')->where('parentid', 418);
    }
}
