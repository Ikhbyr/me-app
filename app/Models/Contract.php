<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;


class Contract extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    public $primaryKey = "id";
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = [
        'name',
        'body',
        'type_id',
        'created_by',
        'created_by',
        'updated_at',
        'updated_by'
    ];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
        'updated_at' => 'date:Y-m-d H:i:s'
    ];

    public static function getStoreRules()
    {
        return [
            'name' => 'required|max:200',
            'body' => 'required',
            'type_id' => 'required'
        ];
    }


    public static function getUpdateRules()
    {
        return [
            'name' => 'required|max:200',
            'body' => 'required',
            'type_id' => 'required',
            'id' => 'required'
        ];
    }

    public static function getUpdateMessages()
    {
        return [];
    }

    public static function getStoreMessages()
    {
        return [];
    }

    public function type()
    {
        return $this->belongsTo(DicMain::class, 'type_id', 'value')->where('parentid', 1923);
    }
}
