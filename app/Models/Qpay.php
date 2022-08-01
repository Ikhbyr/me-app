<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Qpay extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public $table = "qpay";
    public $primaryKey = "id";
    public $timestamps = false;

    public $fillable = [
        'sender_invoice_no',
        'invoice_receiver_code',
        'invoice_description',
        'amount',
        'callback_url',
        'invoice_id',
        'qr_text',
        'qpay_shorturl',
        'statusid',
        'created_at',
        'created_by',
        'callbacked_at',
        'checked_paid_amount',
        'checked_count',
        'checked_rows',
        'checked_date',
        'jrno',
        'typeid',
        'txn_type',
        'to_account',
        'productno',
        'purptypeid',
        'purpdesc',
        'acnttypeid',
        'custregno',
        'custphone',
        'custemail',
        'cur_code',
        'instid',
    ];

    protected $casts = [
        'sender_invoice_no' => 'integer',
        'invoice_receiver_code' => 'string',
        'invoice_description' => 'string',
        'amount' => 'float',
        'callback_url' => 'string',
        'invoice_id' => 'string',
        'qr_text' => 'string',
        'qpay_shorturl' => 'string',
        'statusid' => 'integer',
        'created_at' => 'date',
        'created_by' => 'integer',
        'callbacked_at' => 'date',
        'checked_paid_amount' => 'float',
        'checked_count' => 'integer',
        'checked_rows' => 'string',
        'checked_date' => 'date',
        'jrno' => 'integer',
        'typeid' => 'string',
        'txn_type' => 'string',
        'to_account' => 'string',
    ];

    public static function getInvoiceRules()
    {
        return [
            'productno' => 'required',
            'purptypeid' => 'nullable',
            'purpdesc' => 'nullable',
            'acnttypeid' => 'nullable',
            'custregno' => 'nullable',
            'custphone' => 'nullable',
            'custemail' => 'nullable',

            'typeid' => 'required|string|max:10',
            'txn_type' => 'required|string|max:2',
            'to_account' => 'required_if:txn_type,02|string|max:20',
            // 'sender_invoice_no' => 'required|string|max:45',
            'sender_branch_code' => 'nullable|string|max:45',
            'amount' => 'numeric|required|min:10',
            // 'sender_branch_data' => 'nullable|array',
            // 'sender_branch_data.register' => 'nullable|string|max:20',
            // 'sender_branch_data.name' => 'nullable|string|max:100',
            // 'sender_branch_data.email' => 'nullable|string|max:255',
            // 'sender_branch_data.phone' => 'nullable|string|max:20',
            // 'sender_branch_data.address' => 'nullable|array',
            // 'sender_branch_data.address.city' => 'nullable|string|max:100',
            // 'sender_branch_data.address.district' => 'nullable|string|max:100',
            // 'sender_branch_data.address.street' => 'nullable|string|max:100',
            // 'sender_branch_data.address.building' => 'nullable|string|max:100',
            // 'sender_branch_data.address.address' => 'nullable|string|max:100',
            // 'sender_branch_data.address.zipcode' => 'nullable|string|max:20',
            // 'sender_branch_data.address.longitude' => 'nullable|string|max:20',
            // 'sender_branch_data.address.latitude' => 'nullable|string|max:20',

            'sender_staff_code' => 'nullable|string|max:100',
            'sender_staff_data' => 'nullable|array',
            'sender_terminal_code' => 'nullable|string|max:45',
            'sender_terminal_data' => 'nullable|array',
            'sender_terminal_data.name' => 'nullable|string|max:100',
            'invoice_receiver_code' => 'required|string|max:45',
            // 'callback_url' => 'required|string|max:255',
            // 'invoice_receiver_data' => 'nullable|array',
            // 'invoice_receiver_data.register' => 'nullable|string|max:20',
            // 'invoice_receiver_data.name' => 'nullable|string|max:100',
            // 'invoice_receiver_data.email' => 'nullable|string|max:255',
            // 'invoice_receiver_data.phone' => 'nullable|string|max:20',
            // 'invoice_receiver_data.address' => 'nullable|array',
            // 'invoice_receiver_data.address.city' => 'nullable|string|max:100',
            // 'invoice_receiver_data.address.district' => 'nullable|string|max:100',
            // 'invoice_receiver_data.address.street' => 'nullable|string|max:100',
            // 'invoice_receiver_data.address.building' => 'nullable|string|max:100',
            // 'invoice_receiver_data.address.address' => 'nullable|string|max:100',
            // 'invoice_receiver_data.address.zipcode' => 'nullable|string|max:20',
            // 'invoice_receiver_data.address.longitude' => 'nullable|string|max:20',
            // 'invoice_receiver_data.address.latitude' => 'nullable|string|max:20',

            'invoice_description' => 'required|string|max:255',
            'invoice_due_date' => 'nullable|date',
            'enable_expiry' => 'nullable|bool',
            'expiry_date' => 'nullable|date',
            'calculate_vat' => 'nullable|bool',
            'tax_customer_code' => 'nullable|string',
            'line_tax_code' => 'nullable|string',
            'allow_partial' => 'nullable|bool',
            'minimum_amount' => 'numeric|nullable',
            'allow_exceed' => 'bool|nullable',
            'maximum_amount' => 'numeric|nullable',
            'calback_url' => 'string|nullable|255',
            'note' => 'string|nullable|1000',
            'lines' => 'nullable|array',
            // 'lines.*.sender_product_code' => 'nullable|string|max:45',
            // 'lines.*.tax_product_code' => 'nullable|string|max:45',
            // 'lines.*.line_description' => 'required|string|max:255',
            // 'lines.*.line_quantity' => 'required|numeric',
            // 'lines.*.line_unit_price' => 'required|numeric',
            // 'lines.*.note' => 'nullable|string',
            // 'lines.*.disctounts' => 'nullable|array',
            // 'lines.*.surcharges' => 'nullable|array',
            // 'lines.*.taxes' => 'nullable|array',

            // 'lines.*.disctounts.*.discount_code' => 'nullable|string|max:45',
            // 'lines.*.disctounts.*.description' => 'required|string|max:100',
            // 'lines.*.disctounts.*.amount' => 'required|numeric',
            // 'lines.*.disctounts.*.note' => 'nullable|string',

            // 'lines.*.surcharges.*.surcharge_code' => 'nullable|string|max:45',
            // 'lines.*.surcharges.*.description' => 'required|string|max:100',
            // 'lines.*.surcharges.*.amount' => 'required|numeric',
            // 'lines.*.surcharges.*.note' => 'nullable|string',

            // 'lines.*.taxes.*.tax_code' => 'nullable|string',
            // 'lines.*.taxes.*.description' => 'required|string|max:100',
            // 'lines.*.taxes.*.amount' => 'required|numeric',
            // 'lines.*.taxes.*.city_tax' => 'required|numeric',
            // 'lines.*.taxes.*.note' => 'nullable|string',


            // 'transactions' => 'nullable|array',
            // 'transactions.*.description' => 'required|string|max:100',
            // 'transactions.*.amount' => 'required|numeric',
            // 'transactions.*.accounts' => 'nullable|array',
            // 'transactions.*.accounts.*.account_bank_code' => 'required|string',
            // 'transactions.*.accounts.*.account_number' => 'required|string|max:100',
            // 'transactions.*.accounts.*.account_name' => 'required|string|max:100',
            // 'transactions.*.accounts.*.account_currency' => 'required|string',
        ];
    }

    public static function getInvoiceMessages()
    {
        return [
            'typeid.required' => 'Type Id талбар хоосон байж болохгүй.'
        ];
    }
}
