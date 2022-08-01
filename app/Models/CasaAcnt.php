<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class CasaAcnt extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public $table = "casa_acnt";
    public $primaryKey = "id";
    public $timestamps = false;

    public $fillable = [
        'acnt_code',
        'name',
        'name2',
        'company_code',
        'dormancy_date',
        'prod_code',
        'prod_name',
        'brch_code',
        'brch_name',
        'cur_code',
        'status_custom',
        'joint_or_single',
        'status_date',
        'status_sys',
        'status_sys_name',
        'cust_code',
        'seg_code',
        'seg_name',
        'acnt_type',
        'acnt_type_name',
        'flag_stopped',
        'flag_dormant',
        'flag_stopped_int',
        'flag_stopped_payment',
        'flag_frozen',
        'flag_no_credit',
        'flag_no_debit',
        'salary_acnt',
        'corporate_acnt',
        'open_date',
        'closed_by',
        'closed_date',
        'created_by',
        'created_at',
        'last_dt_date',
        'last_ct_date',
        'last_seq_txn',
        'monthly_wd_count',
        'cap_method',
        'cap_method_name',
        'cap_acnt_code',
        'cap_cur_code',
        'min_amount',
        'max_amount',
        'paymt_default',
        'od_contract_code',
        'od_class_no',
        'od_class_name',
        'acnt_manager',
        'od_type',
        'od_flag_wroff_int',
        'od_flag_wroff',
        'id',
        'instid',
        'userid',
        'acrint_bal',
        'avail_bal',
        'avail_limit',
        'blocked_bal',
        'current_bal',
        'daily_basis_code',
        'cust_type',
        'od_limit',
        'passbook_facility',
        'penalty_rcv',
        'total_avail_bal',
        'unex',
        'unexint_rcv',
        'unexint_rcv_bill',
        'is_secure',
        'read_name',
        'read_bal',
        'read_tran',
        'do_tran',
        'get_with_secure',
        'status',
        'sys_no',
        'is_allow_partial_liq'
    ];

    protected $casts = [
        'id' => 'integer',
        'acnt_code' => 'string',
        'is_secure' => 'integer',
        'read_name' => 'integer',
        'sys_no' => 'integer',
        'is_allow_partial_liq' => 'integer',
        'instid' => 'integer',
        'avail_bal'=> 'double',
        'avail_limit'=> 'double',
        'total_avail_bal'=> 'double',
        'last_seq_txn'=> 'double',
        'unexint_rcv'=> 'double',
        'unexint_rcv_bill'=> 'double',
        'current_bal'=> 'double',
        'penalty_rcv'=> 'double',
        'od_limit'=> 'double',
        'acrint_bal'=> 'double',
        'unex'=> 'double',
        'blocked_bal'=> 'double',
        'flag_no_debit'=> 'integer',
        'flag_stopped_payment'=> 'integer',
        'read_tran'=> 'integer',
        'flag_frozen'=> 'integer',
        'flag_dormant'=> 'integer',
        'cust_type'=> 'integer',
        'do_tran'=> 'integer',
        'monthly_wd_count'=> 'integer',
        'flag_stopped'=> 'integer',
        'flag_stopped_int'=> 'integer',
        'read_bal'=> 'integer',
        'passbook_facility'=> 'integer',
        'paymt_default'=> 'integer',
        'cap_method'=> 'integer',
        'od_class_no'=> 'integer',
        'flag_no_credit'=> 'integer',
    ];

    public function inst()
    {
        return $this->belongsTo(Inst::class, 'instid', 'id');
    }
}
