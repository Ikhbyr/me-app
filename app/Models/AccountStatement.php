<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class AccountStatement extends Model implements Auditable
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

    public $fillable = [
        'cont_cur_rate',
        'income',
        'jrno',
        'begin_bal',
        'end_bal',
        'txn_date',
        'txn_code',
        'bal_type_code',
        'outcome',
        'balance',
        'txn_desc',
        'cont_acnt_code',
        'cont_bank_acnt_code',
        'cont_bank_acnt_name',
        'cont_bank_code',
        'cont_bank_name',
        'post_date',
        'id',
        'instid',
        'acnt_code',
    ];

    protected $casts = [
        'cont_cur_rate' => 'double',
        'income' => 'double',
        'jrno' => 'string',
        'begin_bal' => 'double',
        'end_bal' => 'double',
        'txn_date' => 'date:Y-m-d',
        'txn_code' => 'string',
        'bal_type_code' => 'string',
        'outcome' => 'double',
        'balance' => 'double',
        'txn_desc' => 'string',
        'cont_acnt_code' => 'string',
        'cont_bank_acnt_code' => 'string',
        'cont_bank_acnt_name' => 'string',
        'cont_bank_code' => 'string',
        'cont_bank_name' => 'string',
        'post_date' => 'date:Y-m-d H:i:s',
        'id' => 'int',
        'instid' => 'int',
    ];

    public function type()
    {
        return $this->belongsTo(DicMain::class, 'typeid', 'value')->where('parentid', 418);
    }
}
