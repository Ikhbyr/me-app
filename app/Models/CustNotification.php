<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;


class CustNotification extends Model implements Auditable
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
        'cust_userid',
        'notification_id',
        'is_read',
        'created_by',
        'created_at'
    ];

    protected $casts = ['created_at' => 'date:Y-m-d H:i:s'];

    public function user()
    {
        return $this->belongsTo(CustUser::class, 'cust_userid', 'userid');
    }

    public function notification()
    {
        return $this->belongsTo(Notification::class, 'notification_id', 'id');
    }
}
