<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;


class Notification extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    public $primaryKey = "id";
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = [
        'userid',
        'title',
        'description',
        'created_by',
        'created_at'
    ];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
        'is_all' => 'integer'
    ];
}
