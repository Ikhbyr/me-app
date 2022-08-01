<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Provider extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public $table = "dic_provider_conf";
    public $primaryKey = "id";
    public $timestamps = false;

    public $fillable = [
        'id',
        'name',
        'conn_conf_id',
        'config',
        'statusid',
        'descr',
        'typeid',
        'code',
        'instid',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
    ];

    protected $casts = [
        'id' => 'integer',
        'name' => 'string',
        'conn_conf_id' => 'integer',
        'config' => 'string',
        'statusid' => 'integer',
        'descr' => 'string',
        'typeid' => 'string',
        'code' => 'string',
        'instid' => 'integer',
        'created_at' => 'date:Y-m-d H:i:s',
        'created_by' => 'integer',
        'updated_at' => 'date:Y-m-d H:i:s',
        'updated_by' => 'integer',
    ];

    public static function getStoreRules()
    {
        return [
            'name' => 'required|max:50',
            'conn_conf_id' => 'required|numeric',
            'config' => 'required|max:4000|json',
            'descr' => 'nullable|max:500',
            'typeid' => 'required|max:30',
            'code' => 'required|max:2',
        ];
    }

    public static function getUpdateRules()
    {
        return [
            'id' => 'required|numeric',
            'name' => 'required|max:50',
            'conn_conf_id' => 'required|numeric',
            'config' => 'required|max:4000|json',
            'descr' => 'nullable|max:500',
            'typeid' => 'nullable|max:30',
            'code' => 'required|max:2',

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

    public function connConf(){
        return $this->belongsTo(ConnConf::class, 'conn_conf_id', 'id')->where('statusid', 1);
    }
    public function type(){
        return $this->belongsTo(DicMain::class, 'typeid', 'value')->where('parentid', 1845)->select(['value', 'name']);
    }
}
