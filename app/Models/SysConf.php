<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class SysConf extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public $table = "dic_system_conf";
    public $primaryKey = "id";
    public $timestamps = false;

    public $fillable = [
        'id',
        'name',
        'typeid',
        'config',
        'descr',
        'statusid',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
    ];

    protected $casts = [
        'id' => 'integer',
        'name' => 'string',
        'typeid' => 'string',
        'config' => 'string',
        'descr' => 'string',
        'statusid' => 'integer',
        'created_at' => 'date:Y-m-d',
        'created_by' => 'integer',
        'updated_at' => 'date:Y-m-d',
        'updated_by' => 'integer',
    ];

    public static function getStoreRules()
    {
        return [
            'name' => 'required|max:50',
            'typeid' => 'required|max:30',
            'config' => 'required|max:4000|json',
            'descr' => 'nullable|max:500',
        ];
    }

    public static function getUpdateRules()
    {
        return [
            'id' => 'required|numeric',
            'name' => 'required|max:50',
            'typeid' => 'required|max:30',
            'config' => 'required|max:4000|json',
            'descr' => 'nullable|max:500',
        ];
    }

    public static function getUpdateMessages()
    {
        return [
        ];
    }

    public static function getStoreMessages()
    {
        return [
        ];
    }

    public function type(){
        return $this->belongsTo(DicMain::class, 'typeid', 'value')->where('parentid', 1761);
    }
}
