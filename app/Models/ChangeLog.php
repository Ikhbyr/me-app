<?php

namespace App\Models;

use App\Models\Cust\CustMain;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Yajra\Oci8\Eloquent\OracleEloquent;

class ChangeLog extends OracleEloquent
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public $table = "log_changes";
    public $primaryKey = "id";
    public $timestamps = false;

    public $fillable = [
        'id',
        'user_type',
        'user_id',
        'event',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'url',
        'ip_address',
        'user_agent',
        'tags',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'user_type' => 'string',
        'user_id' => 'integer',
        'event' => 'string',
        'auditable_type' => 'string',
        'auditable_id' => 'integer',
        'old_values' => 'string',
        'new_values' => 'string',
        'url' => 'string',
        'ip_address' => 'string',
        'user_agent' => 'string',
        'tags' => 'string',
        'created_at' => 'string',
        'updated_at' => 'string',
    ];
}
