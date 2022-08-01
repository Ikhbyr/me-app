<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanTransaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public $table = "loan_transaction";
    public $primaryKey = "id";
    public $timestamps = false;

    public $fillable = [
        'id',
        'txn_acnt_code',
        'cur_code',
        'tran_amt',
        'tran_cur_code',
        'identity_type',
        'rate',
        'internal_cont_acnt_code',
        'cont_amount',
        'cont_cur_code',
        'cont_rate',
        'txn_desc',
        'tcust_name',
        'tcust_addr',
        'tcust_register',
        'tcust_register_mask',
        'tcust_contact',
        'source_type',
        'is_tmw',
        'is_preview',
        'is_preview_fee',
        'cont_acnt_code',
        'txn_jrno',
        'is_supervisor',
        'jr_item_no_and_incr',
        'cont_bank_code',
        'created_at',
        'created_by',
        'statusid',
        'instid',
        'txn_type',
        'core_jrno',
        'txn_corr_jrno',
        'core_corr_jrno',
        'err_desc'
    ];

    protected $casts = [
        'id' => 'integer',
        'userid' => 'integer',
        'created_at' => 'date:Y-m-d H:i:s',
        'tran_amt' => 'double',
        'cont_amount' => 'double',
        'cont_rate' => 'double',
        'rate' => 'double',
        'txn_jrno' => 'double',
        'core_jrno' => 'double',
        'txn_corr_jrno' => 'double',
        'core_corr_jrno' => 'double',
        'core_jrno' => 'double',
        'is_tmw' => 'integer',
        'is_preview' => 'integer',
        'is_preview_fee' => 'integer',
        'is_supervisor' => 'integer',
        'instid' => 'integer',
        'txn_type' => 'integer',
    ];
}
