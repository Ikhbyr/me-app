<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModulePerm extends Model
{
    use HasFactory;

    public $table = "module_perms";
    public $primaryKey = "permid";
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'moduleid',
        'permid',
        'parentpermid',
        'permname',
        'permnameeng',
        'granttype',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
    ];

    protected $casts = [
        'moduleid' => 'string',
        'permid' => 'string',
        'parentpermid' => 'string',
        'permname' => 'string',
        'permnameeng' => 'string',
        'granttype' => 'integer',
        'created_at' => 'date:Y-m-d',
        'created_by' => 'integer',
        'updated_at' => 'date:Y-m-d',
        'updated_by' => 'integer',
    ];

}
