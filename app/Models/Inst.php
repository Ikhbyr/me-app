<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;


class Inst extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    public $table = "inst";
    public $primaryKey = "id";
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = [
        'id',
        'instname',
        'instnameeng',
        'regno',
        'nationid',
        'stabledate',
        'inst_typeid',
        'license_typeid',
        'email',
        'phone',
        'dir_name',
        'color',
        'logo',
        'state',
        'region',
        'subregion',
        'street',
        'zipcode',
        'status',
        'listorder',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
    ];

    protected $casts = [
        'id' => 'integer',
        'instname' => 'string',
        'instnameeng' => 'string',
        'regno' => 'string',
        'nationid' => 'string',
        'stabledate' => 'date:Y-m-d H:i:s',
        'inst_typeid' => 'string',
        'license_typeid' => 'string',
        'email' => 'string',
        'phone' => 'string',
        'dir_name' => 'string',
        'color' => 'string',
        'logo' => 'string',
        'state' => 'string',
        'region' => 'string',
        'subregion' => 'string',
        'street' => 'string',
        'zipcode' => 'string',
        'status' => 'string',
        'listorder' => 'integer',
        'created_at' => 'date:Y-m-d H:i:s',
        'created_by' => 'integer',
        'updated_at' => 'date:Y-m-d H:i:s',
        'updated_by' => 'integer',
    ];

    public function instuser()
    {
        return $this->hasMany(InstUser::class, 'instid', 'id')->where('status', 1);
    }

    public function custusers()
    {
        return $this->belongsToMany(CustUser::class, 'inst_cust_conn');
    }

    public function smcustusers()
    {
        return $this->belongsToMany(CustUser::class, 'inst_cust_conn')->select('userid', 'firstname', 'lastname');
    }

    public function type(){
        return $this->belongsTo(DicMain::class, 'inst_typeid', 'value')->where('parentid', 425);
    }

    public function oregion(){
        return $this->belongsTo(DicMain::class, 'region', 'maintype')->where('parentid', 430);
    }
}
