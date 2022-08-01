<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class CcaAcnt extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public $table = "cca_acnt";
    public $primaryKey = "id";
    public $timestamps = false;

    public $fillable = [
        'acnt_code',
        'name',
        'name2',
        'last_liquidate_date',
        'end_date',
        'grace_days',
        'due_date',
        'block_amount_purch',
        'statement_date',
        'status_name',
        'min_pay_amt',
        'get_with_secure',
        'actual_start_date',
        'avail_balance',
        'block_amount_cash',
        'brch_code',
        'brch_name',
        'brch_name2',
        'cash_limit',
        'class_name',
        'class_name2',
        'cycle_no',
        'company_code',
        'cur_code',
        'cust_code',
        'class_no',
        'daily_basis_code',
        'description',
        'exp_cash_amount',
        'exp_interest_amount',
        'exp_purchase_amount',
        'instid',
        'userid',
        'sys_no',
        'is_secure',
        'prod_code',
        'exp_transfer_amount',
        'is_not_auto_class',
        'last_exp_date',
        'last_txn_date',
        'od_fee',
        'ol_fee',
        'other_fee',
        'over_limit_amt',
        'over_limit_percent',
        'prod_code_name',
        'prod_code_name2',
        'repayment_acnt',
        'repayment_mode',
        'repayment_mode_name',
        'repayment_mode_name2',
        'repayment_type',
        'repayment_type_name',
        'repayment_type_name2',
        'seg_code',
        'start_date',
        'status_id',
        'status_id_name',
        'status_id_name2',
        'status_name2',
        'status_sys',
        'total_exp_amount',
        'total_limit',
        'acnt_type',
        'created_at',
        'created_by',
        'statusid',
        'id',
    ];

    protected $casts = [
        'id' => 'integer',
        'sys_no' => 'integer',
        'instid' => 'integer',
        'is_secure' => 'integer',
        'cycle_no' => 'integer',
        'grace_days' => 'integer',
        'class_no' => 'integer',
        'is_not_auto_class' => 'integer',
        'total_limit' => 'double',
        'total_exp_amount' => 'double',
        'avail_balance' => 'double',
        'over_limit_percent' => 'double',
        'ol_fee' => 'double',
        'od_fee' => 'double',
        'over_limit_amt' => 'double',
        'total_limit' => 'double',
        'exp_purchase_amount' => 'double',
        'cash_limit' => 'double',
        'other_fee' => 'double',
        'exp_interest_amount' => 'double',
        'user_id' => 'double',
        'avail_balance' => 'double',
        'exp_transfer_amount' => 'double',
        'min_pay_amt' => 'double',
        'exp_cash_amount' => 'double',
        'block_amount_cash' => 'double',
        'total_exp_amount' => 'double',
        'statement_date' => 'string',
    ];

    public function inst()
    {
        return $this->belongsTo(Inst::class, 'instid', 'id');
    }

    public function acntIntInfos() {
        return $this->hasMany(AcntIntList::class, 'acnt_code', 'acnt_code');
    }
}
