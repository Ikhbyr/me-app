<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginActivityLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public $table = "login_activity_log";
    public $primaryKey = "id";
    public $timestamps = false;

    public $fillable = [
        'id',
        'userid',
        'device_ip',
        'created_at',
        'last_login_date',
        'agent',
        'channel',
        'deviceid',
        'devicename'
    ];

    protected $casts = [
        'id' => 'integer',
        'userid' => 'integer',
        'device_ip' => 'string',
        'created_at' => 'date:Y-m-d H:i:s',
        'last_login_date' => 'date:Y-m-d H:i:s',
        'agent' => 'string',
    ];
}
