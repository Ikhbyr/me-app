<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAccessToken extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public $table = "user_access_tokens";
    public $primaryKey = "id";
    //  public $timestamps = false;

    protected $fillable = [
        'id', 'userid', 'name', 'token', 'abilities', 'last_used_at', 'created_at', 'updated_at', 'channel'
    ];

    protected $casts = [
        'id' => 'integer',
        'userid' => 'integer',
        'name' => 'string',
        'token' => 'string',
        'abilities',
        'last_used_at' => 'date:Y-m-d',
        'created_at' => 'date:Y-m-d',
        'updated_at' => 'date:Y-m-d',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */

    public function user()
    {
        return $this->belongsTo(CustUser::class, 'userid', 'userid');
    }

    public function backUser()
    {
        return $this->belongsTo(InstUser::class, 'userid', 'userid');
    }
}
