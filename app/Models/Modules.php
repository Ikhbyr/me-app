<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modules extends Model
{
    use HasFactory;

    public $table = "module_list";
    public $primaryKey = "id";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'moduleid',
        'parentid',
        'modulename',
        'modulenameeng',
        'status',
        'weburl',
        'webversion',
        'moduleversion',
        'corder',
        'typeid',
    ];
    protected $casts = [
        'id'=>'integer',
        'moduleid',
        'parentid'=>'integer',
        'modulename',
        'modulenameeng',
        'status',
        'weburl',
        'webversion',
        'moduleversion',
        'corder'=>'integer',
        'typeid',
    ];

    public function perms() {
        return $this->hasMany(ModulePerm::class, 'moduleid', 'moduleid');
    }
}
