<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPassHist extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $table = "USER_PASSHIST";
    public $timestamps = false;
    public $primaryKey = "id";

    protected $fillable = [
        'userid',
        'password',
        'passdate',
        'createdate',
    ];

    protected $casts = [
        'id' => 'integer',
        'userid' => 'integer',
        'password' => 'string',
        'passdate' => 'date:Y-m-d',
        'createdate' => 'date:Y-m-d',
    ];

}
