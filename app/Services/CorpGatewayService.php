<?php

namespace App\Services;

use App\Models\BankSys;
use App\Models\CorpStatement;
use App\Models\CorpTransaction;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CorpGatewayService
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public $providerAcc;
    private $token;
    protected $invoiceservice;

    public function __construct($instid)
    {
        $pp = BankSys::where("typeid", "05")->where('statusid', 1)->where('instid', $instid)->first();
        if (empty($pp)) {
            Log::error('CorpGateway', "Corprate provider not configured! InstID: " . $instid);
            return getSystemResp("Corprate provider not configured!", 500);
        }
        $this->providerAcc = json_decode($pp->config, true);
    }

    public function getToken()
    {
        $response = Http::withHeaders(
            [
                'Authorization' => 'Basic ' . base64_encode($this->providerAcc['corp_username'] . ":" . $this->providerAcc['corp_password']),
                'Content-Type' => 'application/x-www-form-urlencoded',
            ]
        )->post($this->providerAcc['corp_url'] . '/v1/auth/token?grant_type=client_credentials');
        $token = json_decode((string) $response->getBody(), true);
        return $token['access_token'];
    }

    public function getAccountList()
    {
        $this->token = $this->getToken();
        $response = Http::withHeaders(
            [
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ]
        )->get($this->providerAcc['corp_url'] . '/v1/accounts');

        return json_decode((string) $response->getBody(), true);
    }

    public function getAccountBalance($account)
    {
        $this->token = $this->getToken();
        $response = Http::withHeaders(
            [
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ]
        )->get($this->providerAcc['corp_url'] . '/v1/accounts/' . $account . '/balance');

        return json_decode((string) $response->getBody(), true);
    }

    public function getAccountInfo($account)
    {
        $this->token = $this->getToken();
        $response = Http::withHeaders(
            [
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ]
        )->get($this->providerAcc['corp_url'] . '/v1/accounts/' . $account . '/');

        return json_decode((string) $response->getBody(), true);
    }

    public function getAccountStatement($account)
    {
        $this->token = $this->getToken();
        $response = Http::withHeaders(
            [
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ]
        )->get($this->providerAcc['corp_url'] . '/v1/statements/' . $account);

        return json_decode((string) $response->getBody(), true);
    }

    /**
     * ХААН БАНКНЫ ДАНС РУУ ГҮЙЛГЭЭ ХИЙХ
     *
     * @param  mixed $senddata = [
     *  "fromAccount": "string",
     *  "toAccount": "string",
     *  "toCurrency": "string",
     *  "amount": decimal,
     *  "description": "string",
     *  "currency": "string",
     *  "transferid":" string "
     * ]
     * @return void
     */
    public function transactionDemostic($senddata)
    {
        return $this->transService($senddata, '/v1/transfer/domestic');
    }

    /**
     * transInterBank - БУСАД БАНКНЫ ДАНС РУУ ГҮЙЛГЭЭ ХИЙХ
     *
     * @param  mixed $senddata [
     *  "fromAccount": "string",
     *  "toAccount": "string",
     *  "toCurrency": "string",
     *  "toAccountName": "string",
     *  "toBank": "string"
     *  "amount": decimal,
     *  "description": "string",
     *  "currency": "string",
     *  "transferid":" string "
     * ]
     * @return void
     */
    public function transInterBank($senddata)
    {
        return $this->transService($senddata, '/v1/transfer/interbank');
    }

    public function transService($senddata, $url) {
        $this->token = $this->getToken();
        $senddata['loginName'] = $this->providerAcc['loginName'];
        $senddata['tranPassword'] = $this->providerAcc['tranPassword'];
        // Ямар нэгэн тохиолдлоор алдаатай болчихвол
        $senddata['statusid'] = 2;
        try {
            $response = Http::withHeaders(
                [
                    'Authorization' => 'Bearer ' . $this->token,
                    'Content-Type' => 'application/json',
                ]
            )->post($this->providerAcc['corp_url'] . $url, $senddata);
        } catch (Exception $ex) {
            $this->createTransaction($senddata);
            Log::error('CorpGatewayService - transService', $ex);
            return getSystemResp($ex->getMessage(), 500);
        }

        if ($response->status() == 200) {
            $body = json_decode((string) $response->getBody(), true);
            if (!empty($body['journalNo'])) {
                $senddata['journal_no'] = $body['journalNo'];
                $senddata['uuid'] = $body['uuid'];
                $senddata['transferid'] = $body['transferid'];
                $senddata['system_date'] = $body['systemDate'];
                $senddata['statusid'] = 1;
            }
        } else {
            $body = $response->body();
        }
        $this->createTransaction($senddata);
        return getSystemResp($body, $response->status());
    }

    public function createTransaction($data) {
        $tran = new CorpTransaction();
        $user = Auth::user();
        foreach ($tran->fillable as $field) {
            if (array_key_exists($field, $data)) {
                $tran->$field = $data[$field];
            }
        }
        $tran->statusid = 0;
        $tran->instid = $user->userid;
        $tran->created_by = $user->userid;
        $tran->created_at = Carbon::now();
        $tran->save();
    }

    /**
     * @example  $data {
     *      "record": 2,
     *      "tranDate": "2022-02-08",
     *      "postDate": "2022-02-08",
     *      "time": "14082902",
     *      "branch": "5000",
     *      "teller": "99944",
     *      "journal": 8393028,
     *      "code": 4045,
     *      "amount": 100,
     *      "balance": 100,
     *      "debit": 0,
     *      "correction": 0,
     *      "description": "test ikhee",
     *      "relatedAccount": "5337040310"
     * }
     */
    public function checkStatement($data)
    {
        if ($data && $data['debit'] == 0) {
            $tran = CorpStatement::where('channel', 'CGW')->where('txn_jrno', $data['journal'])->where('statusid', '<>', -1)->get();
            if (count($tran) < 1) {
                $storeData = [
                    "inst" => 4,
                    "channel" => 'CGW',
                    "txnacntno" => $data['relatedAccount'],
                    "txntype" => 1,
                    "txn_jrno" => $data['journal'],
                    "txnamount" => $data['amount'],
                    "txncurcode" => 'MNT',
                    "txndesc" => $data['description']
                ];
                //Push тайбл рүү бүртгэх
                $this->storePush($storeData);

            }
        }
    }

    public function storePush($data)
    {
        $push = new CorpStatement();
        foreach ($push->fillable as $field) {
            if (array_key_exists($field, $data)) {
                $push->$field = $data[$field];
            }
        }
        $push->statusid = 1;
        $push->txndate = Carbon::now();
        $push->save();
        return $push;
    }
}
