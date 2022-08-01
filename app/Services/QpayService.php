<?php

namespace App\Services;

use App\Models\BankSys;
use App\Models\ConnConf;
use App\Models\CustUser;
use App\Models\Provider;
use App\Models\Qpay;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class QpayService
{
    private $host_merchant_v2 = 'https://merchant.qpay.mn';
    private $username = '';
    private $password = '';
    private $invoice_code = '';
    private $internalAccount = '';
    private $txndesc = '';
    public $callbackUrl;
    public $token;
    private $instid;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct($instid)
    {
        // $pp = ProviderParam::where("name", 21)->first();
        $this->instid = $instid;
        $pp = Provider::where("code", "01")->where('instid', $instid)->where('statusid', 1)->first();
        if (empty($pp)) {
            return getSystemResp("QPAY provider not configured!", 500);
        }
        $qpay = json_decode($pp->config);
        if (!$qpay) {
            return getSystemResp("QPAY provider not configured!", 500);
        }
        $this->internalAccount = $qpay->internalAccount;
        $this->invoice_code = $qpay->invoice_code;
        $this->txndesc = $qpay->txndesc;
        $this->callbackUrl = $qpay->callbackUrl;
        $this->username = $qpay->username;
        $this->password = $qpay->password;
        $connConf = ConnConf::where("id", $pp->conn_conf_id)->first();
        if (!$connConf) {
            return getSystemResp("QPAY connection not configured!", 500);
        }
        if (!$conn = json_decode($connConf->config)) {
            return getSystemResp("QPAY connection not fully configured!", 500);
        }
        $this->host_merchant_v2 = $conn->url;
        $this->token = $this->getToken();
    }

    public function postApi($url, $params)
    {
        $response = Http::withHeaders(
            [
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json',
            ]
        )->post($this->host_merchant_v2 . $url, $params);

        if ($response->status() == 200) {
            $data = json_decode((string) $response->body(), true);
            return [
                'data' => $data,
                'status' => $response->status(),
            ];
        } else {
            $data = $response->body();
            return [
                'data' => $data,
                'status' => 500,
            ];
        }
    }

    public function getToken()
    {
        $response = Http::withHeaders(
            [
                'Authorization' => 'Basic ' . base64_encode("$this->username:$this->password"),
            ]
        )->post($this->host_merchant_v2 . '/v2/auth/token');
        $token = json_decode((string) $response->getBody(), true);
        return $token['access_token'];
    }

    /**
     * createInvoice - QPAY Нэхэмжлэх үүсгэх
     *
     * @param  mixed $data
     * @return void
     */
    public function createInvoice($data)
    {
        $data['invoice_code'] = $this->invoice_code;
        $data['sender_invoice_no'] = random_number();
        $data['callback_url'] = $this->callbackUrl . $data['instid'] . '/' . $data['sender_invoice_no'];
        $data['invoice_receiver_code'] = 'terminal';
        $data['invoice_description'] = $this->txndesc;
        $data['to_account'] = $data['contAcntCode'];
        $repsonse = $this->postApi('/v2/invoice', $data);
        if ($repsonse['status'] == 200) {
            $data['invoice_id'] = $repsonse['data']['invoice_id'];
            $data['qr_text'] = $repsonse['data']['qr_text'];
            $data['qpay_shorturl'] = $repsonse['data']['qPay_shortUrl'];
            $this->store($data);
        }
        return $repsonse;
    }

    public function getInvoice($invoice_id)
    {
        $response = Http::withHeaders(
            [
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json',
            ]
        )->get($this->host_merchant_v2 . '/v2/invoice/' . $invoice_id);

        return json_decode((string) $response->getBody(), true);
    }

    public function getPayment($invoice_id)
    {
        $response = Http::withHeaders(
            [
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json',
            ]
        )->get($this->host_merchant_v2 . '/v2/payment/' . $invoice_id);

        return json_decode((string) $response->getBody(), true);
    }

    public function checkPayment($data)
    {
        $response = $this->postApi('/v2/payment/check', $data);
        return $response;
    }

    public function cancelPayment($payment_id, $data)
    {
        $response = Http::withHeaders(
            [
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json',
            ]
        )->delete($this->host_merchant_v2 . '/v2/payment/cancel/' . $payment_id, $data);

        return json_decode((string) $response->getBody(), true);
    }

    public function refundPayment($payment_id, $data)
    {
        $response = Http::withHeaders(
            [
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json',
            ]
        )->delete($this->host_merchant_v2 . '/v2/payment/refund/' . $payment_id, $data);

        return json_decode((string) $response->getBody(), true);
    }

    public function createEbarimt($data)
    {
        return $this->postApi('/v2/ebarimt/create', $data);
    }

    public function store($data)
    {
        $user = auth()->user();
        $qpay = new Qpay();
        foreach ($qpay->fillable as $field) {
            if (array_key_exists($field, $data)) {
                $qpay->$field = $data[$field];
            }
        }
        $qpay->statusid = 0;
        $qpay->created_at = Carbon::now();
        $qpay->created_by = $user->userid;
        $qpay->save();
        return $qpay;
    }

    public function callBackUrl($invoiceno)
    {
        $qpay = Qpay::where('sender_invoice_no', $invoiceno)->first();
        if ($qpay) {
            $checkRequest = array(
                'object_type' => 'INVOICE',
                'object_id' => $qpay->invoice_id
            );
            $qpay->callbacked_at = Carbon::now();
            $responseData = $this->checkPayment($checkRequest);
            if ($responseData['status'] == 200) {
                $responseData = $responseData['data'];
            } else {
                return;
            }
            $qpay->checked_rows = json_encode($responseData['rows']);
            $qpay->checked_count = $responseData['count'];
            $qpay->checked_date = Carbon::now();
            // QPAY гүйлгээ амжилттай болох
            if ($responseData['count'] > 0 && $qpay->statusid != 1) {
                $qpay->checked_paid_amount = $responseData['paid_amount'];
                $lnService = new LoanService();
                // Зээл хаах болон төлөлт хийх
                $respdata = $lnService->paymentLoan($this->instid, $qpay);
                $notif = new NotificationService();
                if (!empty($qpay->created_by)) {
                    $user = CustUser::select('userid', 'device_token')->where('userid', $qpay->created_by)->first();
                } else {
                    return;
                }
                if ($respdata['status'] == 200) {
                    $nesresp = $respdata['data'];
                    $qpay->jrno = $nesresp['txnJrno'] ?? 0;
                    $qpay->statusid = 1;
                    $msg = 'Зээл төлөлт амжилттай.';
                } else {
                    // Эсрэг гүйлгээ хийх
                    // Log::info("Эсрэг гүйлгээ хийх хэсэгрүү орлоо.");
                    $qpay->statusid = 2;
                    $msg = 'Зээл төлөлт амжилтгүй. Суурь системд алдаа гарлаа.';
                }
                $mnotif = $notif->createMainNotif([
                    'title' => 'Зээл төлөлт',
                    'description' => $msg,
                    'instid' => $qpay->instid,
                    'is_all' => 0
                ]);
                $notif->store([
                    'cust_userid' => $user->userid,
                    'notification_id' => $mnotif->id,
                ]);
                $notif->sendNotification('Зээл төлөлт', $msg, [$user->device_token]);
            } else {
                // Log::info($invoiceno . ' Гүйлгээ аль хэдийн хийгдсэн эсвэл QPAY гүйлгээ хүлээгдэж байна.');
            }
            $qpay->save();
        }
    }

    public function chargeAccount($param)
    {
        /* гүйлгээ хийх Start*/
    }

    public function paymentInvoice($param)
    {
    }

    public function responseRef($param)
    {
        /* гүйлгээ хийх Start*/
    }
}
