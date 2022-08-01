<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use OwenIt\Auditing\Contracts\Auditable;

class InstContact extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public $table = "inst_contact";
    public $primaryKey = "id";
    public $timestamps = false;

    public $fillable = [
        'id',
        'instid',
        'contacttype',
        'fname',
        'lname',
        'userid',
        'phone',
        'email',
        'statusid',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
    ];

    protected $casts = [
        'id' => 'integer',
        'instid' => 'integer',
        'contacttype' => 'string',
        'fname' => 'string',
        'lname' => 'string',
        'userid' => 'integer',
        'phone' => 'string',
        'email' => 'string',
        'statusid' => 'integer',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
    ];

    public static function getStoreRules(){
        return [
            'id' => 'nullable|numeric',
            'instid' => 'required|numeric',
            'contacttype' => ['required', 'max:2', Rule::exists('dic_main', 'value')->where('parentid', 1918)],
            'fname' => 'nullable|max:50',
            'lname' => 'nullable|max:50',
            'email' => 'nullable|max:50',
            'phone' => 'nullable|max:20',
            'userid' => 'nullable|numeric',
            'statusid' => 'nullable|numeric',
        ];
    }

    public static function getUpdateRules(){
        return [
            'id' => 'required|numeric',
            'contacttype' => ['nullable', 'max:2', Rule::exists('dic_main', 'value')->where('parentid', 1918)],
            'fname' => 'nullable|max:50',
            'lname' => 'nullable|max:50',
            'email' => 'nullable|max:50',
            'phone' => 'nullable|max:20',
            'userid' => 'nullable|numeric',
        ];
    }

    public static function getDeleteRules(){
        return [
            'id' => 'required|numeric'
        ];
    }

    public function contactTypeName() {
        return $this->belongsTo(DicMain::class, 'contacttype', 'value')->where('dic_main.parentid', 1918);
    }

    public function inst(){
        return $this->belongsTo(Inst::class, 'instid', 'id');
    }
}
