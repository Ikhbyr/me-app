<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use OwenIt\Auditing\Contracts\Auditable;

class Cust extends Model implements AuthenticatableContract, AuthorizableContract, Auditable
{
    use Authenticatable, Authorizable, HasFactory;
    use \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public $table = "cust";
    public $primaryKey = "id";
    public $timestamps = false;

    public $fillable = [
        'id',
        'instid',
        'corrid',
        'cif',
        'familyname',
        'familyname2',
        'lname',
        'lname2',
        'fname',
        'fname2',
        'gender',
        'regno',
        'register_mask_code',
        'nationality',
        'birthday',
        'lang',
        'ethnicity',
        'citizenship',
        'birthplace',
        'segment',
        'employment',
        'categories',
        'education',
        'maritalstatus',
        'phone',
        'phone2',
        'email',
        'fax',
        'familysize',
        'region',
        'subregion',
        'address',
        'status',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'industry',
        'shortname',
        'shortname2',
        'isbl',
        'iscompanycustomer',
        'ispolitical',
        'isvatpayer',
        'monthlyincome',
        'immovabletype',
        'ownership'
    ];

    protected $casts = [
        'id' => 'integer',
        'instid' => 'integer',
        'familysize' => 'integer',
        'birthday' => 'date:Y-m-d',
        'updated_at' => 'date:Y-m-d H:i:s',
        'created_at' => 'date:Y-m-d H:i:s',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function inst()
    {
        return $this->belongsTo(Inst::class, 'instid', 'id');
    }
}
