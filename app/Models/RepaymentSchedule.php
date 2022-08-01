<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class RepaymentSchedule extends Model implements Auditable
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
        'schd_date',
        'amount',
        'int_amount',
        'total_amount',
        'theor_bal',
        'acnt_code',
        'id',
        'instid',
    ];

    protected $casts = [
        'schd_date' => 'date:Y-m-d',
        'amount' => 'double',
        'int_amount' => 'double',
        'total_amount' => 'double',
        'theor_bal' => 'double',
        'acnt_code',
        'id' => 'int',
        'instid' => 'int',
    ];
}
