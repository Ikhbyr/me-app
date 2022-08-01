<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginConfirmDevice extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public $table = "login_confirm_device";
    public $primaryKey = "id";
    public $timestamps = false;

    public $fillable = [
        'id',
        'userid',
        'ip',
        'is_confirm',
        'token',
        'channel',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'userid' => 'integer',
        'ip' => 'string',
        'is_confirm' => 'string',
        'created_at' => 'date:Y-m-d H:i:s',
        'updated_at' => 'date:date:Y-m-d H:i:s',
    ];
}
