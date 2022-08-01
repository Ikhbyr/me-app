<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;


class DicMain extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    public $table = "dic_main";
    public $primaryKey = "id";
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'parentid',
        'value',
        'name',
        'info',
        'maintype',
        'haschild',
        'status',
    ];

    protected $casts = [
        'id'=>'integer',
        'parentid'=>'integer',
        'value'=>'string',
        'name'=>'string',
        'info'=>'string',
        'maintype'=>'string',
        'haschild'=>'string',
        'status'=>'integer',
    ];

    public function parent(){
        return $this->belongsTo(DicMain::class, 'parentid', 'id');
    }

    public function children(){
        return $this->hasMany(DicMain::class, 'parentid', 'id')->where('status', 1);
    }
}
