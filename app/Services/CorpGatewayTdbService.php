<?php

namespace App\Services;

use App\Models\BankSys;
use App\Models\CorpStatement;
use App\Models\CorpTransaction;
use App\Models\LogRequest;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

class CorpGatewayTdbService
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public $providerAcc;
    protected $invoiceservice;

    public function __construct($instid)
    {
        // $provider = ProviderParam::where('name', 'PUSH')->first();
        $pp = BankSys::where("typeid", "04")->where('statusid', 1)->where('instid', $instid)->first();
        if (empty($pp)) {
            Log::error('TDB CorpGateway: Corprate provider not configured! InstID: ' . $instid);
            throw new Exception("Corprate TDB provider not configured!");
        }
        $this->providerAcc = json_decode($pp->config, true);
        $this->providerAcc['password1'] = safeDecrypt($pp->sec1);
        $this->providerAcc['password2'] = safeDecrypt($pp->sec2);
    }

    /**
     * TDB corporate gateway Header хэсгийн мэдээлэл бэлдэх
     *
     * @param  mixed $data = [
     *     transferid = '1', Давхардашгүй дугаарлалт
     *     TxsCd = '1005', үйлдлийн дугаар
     *     amount = '1', Үнийн дүн
     * ]
     * @return void
     */
    public function getHeader($data)
    {
        $amount = $data["amount"] ?? 0;
        $header = "<GrpHdr>
            <MsgId>" . $data['transferid'] . "</MsgId>
            <CreDtTm>" . Carbon::now() . "</CreDtTm>
            <TxsCd>" . $data["TxsCd"] . "</TxsCd>
            <NbOfTxs>1</NbOfTxs>
            <CtrlSum>" . $amount . "</CtrlSum>
            <InitgPty>
                <Id>
                    <OrgId>
                        <AnyBIC>" . $this->providerAcc["anyBic"] . "</AnyBIC>
                    </OrgId>
                </Id>
            </InitgPty>
            <Crdtl>
                <Lang>0</Lang>
                <LoginID>" . $this->providerAcc["loginId"] . "</LoginID>
                <RoleID>" . $this->providerAcc["roleId"] . "</RoleID>
                <Pwds>
                    <PwdType>1</PwdType>
                    <Pwd>" . $this->providerAcc["password1"] . "</Pwd>
                </Pwds>
                <Pwds>
                    <PwdType>2</PwdType>
                    <Pwd>" . $this->providerAcc["password2"] . "</Pwd>
                </Pwds>
            </Crdtl>
        </GrpHdr>";

        return $header;
    }

    /**
     * TDB БАНКНЫ ДАНС РУУ ГҮЙЛГЭЭ ХИЙХ
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
        $senddata['TxsCd'] = 1001;
        return $this->transService($senddata);
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
        $senddata['TxsCd'] = 1002;
        return $this->transService($senddata);
    }

    public function transService($senddata)
    {
        $xml = "<?xml version='1.0' encoding='UTF-8'?>
        <Document>
            " . $this->getHeader($senddata) . "
            <PmtInf>
                <NbOfTxs>1</NbOfTxs>
                <CtrlSum>" . $senddata['amount'] . "</CtrlSum>
                <ForT>F</ForT>
                <Dbtr>
                    <Nm>" . $this->providerAcc['accountName'] . "</Nm>
                </Dbtr>
                <DbtrAcct>
                    <Id>
                        <IBAN>" . $this->providerAcc['accountNo'] . "</IBAN>
                    </Id>
                    <Ccy>".$this->providerAcc['acntCurcode']."</Ccy>
                </DbtrAcct>
                <CdtTrfTxInf>
                    <Amt>
                        <InstdAmt>" . $senddata['amount'] . "</InstdAmt>
                        <EqvtAmt>" . $senddata['amount'] . "</EqvtAmt>
                    </Amt>
                    <Cdtr>
                        <Nm>" . $senddata['toAccountName'] . "</Nm>
                    </Cdtr>
                    <CdtrAcct>
                        <Id>
                            <IBAN>" . $senddata['toAccount'] . "</IBAN>
                        </Id>
                        <Ccy>MNT</Ccy>
                    </CdtrAcct>
                    <CdtrAgt>
                        <FinInstnId>
                            <BICFI>" . $senddata['toBank'] . "</BICFI>
                        </FinInstnId>
                    </CdtrAgt>
                    <RmtInf>
                        <AddtlRmtInf>" . $this->providerAcc['txndesc'] . "</AddtlRmtInf>
                    </RmtInf>
                </CdtTrfTxInf>
            </PmtInf>
        </Document>";
        $senddata['loginName'] = $this->providerAcc['loginId'];
        $senddata['tranPassword'] = $this->providerAcc['password1'];
        // Ямар нэгэн тохиолдлоор алдаатай болчихвол
        $senddata['statusid'] = 2;
        $json = $this->sendToTdbCgw($xml);
        $response = $this->getTransRespInfo($json);


        if ($response['status'] == 200) {
            $body = $response['data'];
            if (!empty($body['jrno'])) {
                $senddata['journal_no'] = $body['jrno'];
                // $senddata['uuid'] = $body['uuid'];
                $senddata['transferid'] = $body['transferid'];
                $senddata['system_date'] = $body['systemDate'];
                $senddata['statusid'] = 1;
            }
        }
        $this->createTransaction($senddata);
        return getSystemResp($response['data'], $response['status']);
    }

    public function getTransRespInfo($json) {
        if (!empty($json['OrgnlPmtInfAndSts'])) {
            $info = $json['OrgnlPmtInfAndSts']['TxnInfAndSts'] ?? '';
            if (!empty($info)) {
                if (isset($info['Rst'])) {
                    if (isset($info['Rst']['JrNo'])) {
                        return getSystemResp([
                            'jrno' => $info['Rst']['JrNo'],
                            'TxDbRate' => $info['Rst']['TxDbRate'],
                            'TxCrRate' => $info['Rst']['TxCrRate'],
                            'transferid' => $json['GrpHdr']['MsgId'],
                            'systemDate' => $json['GrpHdr']['CreDtTm'],
                        ]);
                    }
                }
            }
        }
        return $this->getGrpHdrInfo($json);
    }

    public function createTransaction($data)
    {
        $tran = new CorpTransaction();
        $user = Auth::user();
        if (isset($data['toBank'])) {
            $data['to_bank'] = $data['to_bank'] ?? $data['toBank'];
        }

        if (isset($data['toAccountName'])) {
            $data['to_account_name'] = $data['to_account_name'] ?? $data['toAccountName'];
        }

        if (isset($data['toAccount'])) {
            $data['to_account'] = $data['to_account'] ?? $data['toAccount'];
        }
        foreach ($tran->fillable as $field) {
            if (array_key_exists($field, $data)) {
                $tran->$field = $data[$field];
            }
        }
        $tran->from_account = $this->providerAcc['accountNo'];
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

    public function sendToTdbCgw($xmlData)
    {

        $user = auth()->user();
        $startTime = Carbon::now()->getTimestampMs();
        $curl = curl_init();
        // Variables
        $apiUrl = $this->providerAcc['corp_url'];   // Corp url
        $certFile = $this->providerAcc['certPath']; // Private Cert
        Log::debug($certFile);
        $certPassword = '1234';                     // Cert Password
        $apiHeader = array();
        // $header Content Type
        $apiHeader[] = 'Content-type: text/xml';
        $apiHeader[] = 'Accept: text/xml';

        $options = array(
            CURLOPT_URL                 => $apiUrl,
            CURLOPT_RETURNTRANSFER      => true,
            CURLOPT_HEADER              => false, // true to show header information
            CURLINFO_HEADER_OUT         => true,
            CURLOPT_HTTPGET             => false,
            CURLOPT_POST                => true,
            CURLOPT_FOLLOWLOCATION      => false,
            CURLOPT_VERBOSE             => true,
            CURLOPT_FOLLOWLOCATION      => true,
            CURLOPT_ENCODING            => "UTF-8",

            CURLOPT_SSL_VERIFYHOST      => false, // true in production
            CURLOPT_SSL_VERIFYPEER      => false, // true in production

            CURLOPT_TIMEOUT             => 60,
            CURLOPT_MAXREDIRS           => 2,

            CURLOPT_HTTPHEADER          => $apiHeader,
            CURLOPT_USERAGENT           => 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)',
            CURLOPT_HEADER              => "Content-Type:application/xml",
            CURLOPT_HTTPAUTH            => CURLAUTH_ANYSAFE, // CURLAUTH_BASIC
            CURLOPT_POSTFIELDS          => $xmlData,

            // CURLOPT_USERPWD             => $certUserPwd,
            CURLOPT_SSLCERTTYPE         => 'PEM',
            CURLOPT_SSLCERT             => $certFile,
            CURLOPT_SSLCERTPASSWD       => $certPassword
        );
        try {
            $r = new LogRequest();
            $r->userid = $user ? $user->userid : 1;
            $r->url = $this->providerAcc['corp_url'];
            $r->method = 'POST';
            $r->request = $xmlData;
            $r->save();
            curl_setopt_array($curl, $options);
            $output = curl_exec($curl);
            curl_close($curl);
        } catch (Exception $ex) {
            Log::error('TDB банк гүйлгээ хийхэд алдаа гарлаа.');
            Log::error($ex);
            $r->response = $ex->getMessage();
            $r->responseCode = 500;
            $r->responseTime = (Carbon::now()->getTimestampMs() - $startTime) / 1000;
            $r->save();
            return getSystemResp($ex->getMessage(), 500);
        }
        $json = xmlToJson($output);
        $r->response = $output;
        $r->responseCode = 200;
        $r->responseTime = (Carbon::now()->getTimestampMs() - $startTime) / 1000;
        $r->save();
        return $json;

    }

    public function getAccountBalance() {
        $senddata = [
            'transferid' => random_number(),
            'TxsCd' => '5003',
            'amount' => '0',
        ];
        $xml = "<?xml version='1.0' encoding='UTF-8'?>
        <Document>
            " . $this->getHeader($senddata) . "
            <EnqInf>
                <IBAN>".$this->providerAcc['accountNo']."</IBAN>
                <Ccy>".$this->providerAcc['acntCurcode']."</Ccy>
            </EnqInf>
        </Document>";
        $json = $this->sendToTdbCgw($xml);
        if (isset($json['EnqRsp'])) {
            return getSystemResp([
                'date' => $json['RptDt'],
                'aBalance' => $json['ABal'],
                'balance' => $json['Bal'],
            ]);
        }
        return $this->getGrpHdrInfo($json);
    }


    /**
     * Алдаа гарсан үед Ирсэн хариуны толгой мэдээллийн тайлбар авах.
     *
     * @param  mixed $json
     * @return void
     */
    public function getGrpHdrInfo($json)
    {
        if (!empty($json['GrpHdr'])) {
            return getSystemResp($json['GrpHdr']['RspDesc'] ?? 'TDB банк дээр гүйлгээ хийх үед алдаа гарлаа.', 500);
        } else {
            return getSystemResp('TDB банк дээр гүйлгээ хийх үед алдаа гарлаа.', 500);
        }
    }
}
