<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class TdAcnt extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public $table = "td_acnt";
    public $primaryKey = "id";
    public $timestamps = false;

    public $fillable = [
        'acnt_code',
        'name',
        'name2',
        'company_code',
        'prod_code',
        'brch_code',
        'cur_code',
        'joint_or_single',
        'cust_code',
        'status_sys',
        'status_custom',
        'status_date',
        'seg_code',
        'open_date_org',
        'start_date',
        'maturity_date',
        'tenor',
        'is_corp_acnt',
        'last_dt_date',
        'last_ct_date',
        'last_seq_txn',
        'casa_acnt_code',
        'acnt_version',
        'cap_method',
        'rcv_acnt_code',
        'slevel',
        'closed_by',
        'closed_date',
        'created_by',
        'created_at',
        'closed_cond',
        'term_len',
        'class_no',
        'maturity_option',
        'flag_no_tb',
        'daily_basis_code',
        'prod_name',
        'brch_name',
        'cust_name',
        'cust_type',
        'status_sys_name',
        'current_bal',
        'avail_bal',
        'block_bal',
        'acrint_bal',
        'cap_int',
        'cap_int2',
        'cap_method_name',
        'rcv_acnt_name',
        'seg_name',
        'is_corp_name',
        'joint_or_single_name',
        'closed_by_name',
        'term_basis',
        'passbook_facility',
        'class_name',
        'maturity_option_name',
        'read_name',
        'read_bal',
        'read_tran',
        'do_tran',
        'is_secure',
        'last_tb_date',
        'flag_no_tb_name',
        'cat_code',
        'cat_sub_code',
        'cat_sub_name',
        'cat_name',
        'instid',
        'userid',
        'statusid',
        'sys_no',
        'acnt_type',
        'int_rate',
    ];

    protected $casts = [
        'id' => 'integer',
        'sys_no' => 'integer',
        'instid' => 'integer',
        'is_secure' => 'integer',
        'read_name' => 'integer',
        'read_tran' => 'integer',
        'term_len' => 'integer',
        'slevel' => 'integer',
        'do_tran' => 'integer',
        'is_corp_acnt' => 'integer',
        'current_bal' => 'double',
        'avail_bal' => 'double',
        'block_bal' => 'double',
        'acrint_bal' => 'double',
        'last_seq_txn' => 'double',
        'int_rate' => 'double',
        'cap_int' => 'double',
        'cap_int2' => 'double',
        'read_bal' => 'integer',
        'tenor' => 'integer',
        'passbook_facility' => 'integer',
        'cap_method' => 'integer',
    ];

    public function inst()
    {
        return $this->belongsTo(Inst::class, 'instid', 'id');
    }
}
