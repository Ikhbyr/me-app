<?php

namespace App\Services;

use App\Models\ConnConf;
use App\Models\CorrSys;
use App\Models\LogRequest;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PolarisApiRequestService
{
    private $nessession = '';
    private $company = '';
    private $role = '';
    private $host = '';
    private $lang = '';
    public $internalAccount = '';
    public $repay_susp_accountno = '';
    public $brchCode = '';
    public $savingLoan;
    function __construct($instid = 0)
    {
        try {
            $this->initConnection($instid);
        } catch (Exception $exp) {
            Log::error($instid . " дугаартай байгууллага дээр суурь системийн тохиргоо буруу байна." . $exp);
            return [
                'data' => [],
                'status' => 200
            ];
        }
    }

    public function initConnection($instid = 0)
    {
        if ($instid == 0) {
            $pp = CorrSys::where('typeid', '01')->where('instid', auth()->user()->instid)->first();
        } else {
            $pp = CorrSys::where('typeid', '01')->where('instid', $instid)->first();
        }
        if (!$pp) {
            throw new Exception("Polaris ГСМ суурь системийн тохиргоо бүртгэгдээгүй байна!");
        }
        $corr_system = json_decode($pp->config);
        $this->nessession = $corr_system->cookie;
        $this->company = $corr_system->company;
        $this->role = $corr_system->role;
        $this->lang = $corr_system->lang;
        $this->internalAccount = $corr_system->internalAccount;
        $this->repay_susp_accountno = $corr_system->repay_susp_accountno;
        $this->brchCode = $corr_system->brchCode;
        $this->savingLoan = $corr_system->savingLoan;
        $connConf = ConnConf::where("id", $pp->conn_conf_id)->first();
        if (!$connConf) {
            throw new Exception("Polaris ГСМ суурь системд холболтын тохиргоо холбоно уу!");
        }
        if (!$conn = json_decode($connConf->config)) {
            throw new Exception("Холболтын тохиргоо буруу байна!");
        }
        $this->host = $conn->host;
    }

    public function sendRequest($operation, $params, $instid = 0)
    {
        if (empty($this->nessession)) {
            try {
                $this->initConnection($instid);
            } catch (Exception $exp) {
                return [
                    'data' => [],
                    'status' => 200
                ];
            }
        }
        $startTime = Carbon::now()->getTimestampMs();
        $user = auth()->user();
        $header = [
            'Content-Type' => 'application/json',
            // 'Content-Length' => 14,
            'Cookie' => $this->nessession,
            'op' => $operation,
            'company' => $this->company,
            'role' => $this->role,
            'lang' => $this->lang,
        ];
        $r = new LogRequest();
        $r->userid = $user ? $user->userid : 1;
        $r->url = $this->host;
        $r->method = 'POST';
        $r->request = json_encode([
            'operation' => $operation,
            'data' => $params,
            'header' => $header
        ], JSON_UNESCAPED_UNICODE);
        $r->save();
        try {
            $response = Http::withHeaders(
                $header
            )->timeout(20)->post($this->host, $params);
        } catch (Exception $ex) {
            Log::error('Polaris ERROR');
            Log::error($ex);
            $r->response = $ex->getMessage();
            $r->responseCode = 500;
            $r->responseTime = (Carbon::now()->getTimestampMs() - $startTime) / 1000;
            $r->save();
            return [
                'data' => [],
                'status' => 500
            ];
        }
        if ($response->status() == 200) {
            $data = json_decode((string) $response->body(), true);
        } else {
            $data = $response->body();
        }
        $r->response = @json_encode($data, JSON_UNESCAPED_UNICODE);
        $r->responseCode = $response->status();
        $r->responseTime = (Carbon::now()->getTimestampMs() - $startTime) / 1000;
        $r->save();
        // return response()->json($data, $response->status());
        return [
            'data' => $data,
            'status' => $response->status()
        ];
    }

    public function getDate($instid) {
        $respdata = $this->sendRequest(13619000, [], $instid);
        if ($respdata['status'] == 200) {
            return getSystemResp($respdata['data']);
        } else {
            return getSystemResp('Уучлаарай, системийн цаг авч чадсангүй.', 500);
        }
    }
}
