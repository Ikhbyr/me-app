<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class InstPerm extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public $table = "inst_perm";
    public $primaryKey = "id";
    public $timestamps = false;

    public $fillable = [
        'id',
        'instid',
        'moduleid',
        'permid',
        'statusid',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
    ];

    protected $casts = [
        'id' => 'integer',
        'instid' => 'integer',
        'moduleid' => 'string',
        'permid' => 'string',
        'statusid' => 'integer',
        'created_at' => 'date:Y-m-d',
        'created_by' => 'integer',
        'updated_at' => 'date:Y-m-d',
        'updated_by' => 'integer',
    ];

    public function inst(){
        return $this->belongsTo(Inst::class, 'id', 'instid');
    }

    public function module(){
        return $this->belongsTo(Modules::class, 'moduleid', 'moduleid');
    }

    public function perm(){
        return $this->belongsTo(ModulePerm::class, 'permid', 'permid');
    }

}
