<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogRequest extends Model
{
    use HasFactory;

    public $table = "log_requests";
    public $primaryKey = "id";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'userid',
        'request',
        'response',
        'url',
        'ip',
        'method',
        'created_at',
        'updated_at',
    ];
}
