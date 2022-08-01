<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class CorpStatement extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public $table = "corp_statement";
    public $primaryKey = "id";
    public $timestamps = false;

    public $fillable = [
        'id',
        'inst',
        'channel',
        'txndate',
        'txnacntno',
        'txntype',
        'txn_jrno',
        'txnamount',
        'txncurcode',
        'txndesc',
        'statusid'
    ];

    protected $casts = [
        'id'=> 'integer',
        'inst' => 'integer',
        'channel' => 'string',
        'txndate' => 'date:Y-m-d',
        'txnacntno' => 'integer',
        'txntype' => 'integer',
        'txn_jrno' => 'integer',
        'txnamount' => 'float',
        'txncurcode' => 'string',
        'txndesc' => 'string',
        'statusid' => 'integer'
    ];
}
