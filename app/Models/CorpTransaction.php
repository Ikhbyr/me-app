<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class CorpTransaction extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public $table = "corp_transaction";
    public $primaryKey = "id";
    public $timestamps = false;

    public $fillable = [
        'id',
        'from_account',
        'inst',
        'to_account',
        'amount',
        'description',
        'currency',
        'transferid',
        'uuid',
        'journal_no',
        'system_date',
        'to_currency',
        'to_account_name',
        'to_bank',
        'created_by',
        'created_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'inst' => 'integer',
        'channel' => 'string',
        'system_date' => 'date:Y-m-d',
        'created_at' => 'integer'
    ];
}
