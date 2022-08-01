<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;


class DicPassPolicy extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    public $table = "dic_passpolicy";
    public $primaryKey = "optionname";
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'optionname',
        'optiondesc',
        'optionvalue',
        'optiontype',
    ];

    protected $casts = [
        'id'=>'integer',
        'optionname'=>'string',
        'optiondesc'=>'string',
        'optionvalue'=>'string',
        'optiontype'=>'string'
    ];
}
