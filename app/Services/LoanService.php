<?php

namespace App\Services;

use App\Models\Cust;
use App\Models\CustUser;
use App\Models\LoanAcnt;
use App\Models\LoanTransaction;
use App\Models\SysConf;
use App\Models\TdAcnt;
use Carbon\Carbon;
use Illuminate\Support\Str;

class LoanService
{
    public function getLoanSaving($data)
    {
        $polaris = new PolarisApiRequestService($data['instid']);
        $acntService = new AcntService();
        $intRate = 0;
        $cgwBank = $this->getCallCgwBankCode($data);
        if ($cgwBank['status'] == 200) {
            $cgwBankCode = $cgwBank['data'];
        } else {
            return getSystemResp($cgwBank['data'], $cgwBank['status']);
        }
        $tddata = $acntService->getTdAccountDetail($data['txnAcntCode'], $data['instid']);
        if ($tddata['status'] == 200) {
            $intdetail = $acntService->getAccountInt($data['txnAcntCode'], $data['instid']);
            if ($intdetail['status'] == 200) {
                foreach ($intdetail['data'] as $key => $value) {
                    $value = json_decode(json_encode($value));
                    if ($value->intTypeCode == 'SIMPLE_INT') {
                        if (empty($value->intRate)) {
                            return getSystemResp('Хадгаламжийн дасны хүүний мэдээлэл алдаатай байна.', 500);
                        }
                        $intRate = $value->intRate;
                        break;
                    }
                }
            } else {
                return getSystemResp($intdetail['data'], $intdetail['status']);
            }
        } else {
            return getSystemResp($tddata['data'], $tddata['status']);
        }

        $acnt = TdAcnt::where('acnt_code', $data['txnAcntCode'])->where('instid', $data['instid'])->first();
        if (empty($acnt)) {
            return getSystemResp($data['txnAcntCode'] . ' дугаартай хадгаламжийн данс олдсонгүй.', 500);
        }
        if ($acnt->cur_code != 'MNT') {
            return getSystemResp($acnt->cur_code . ' системд зөвшөөрөгдөөгүй валют.', 500);
        }
        $user = auth()->user();
        $cust = Cust::where('instid', $data['instid'])->where('regno', $user->registernum)->where('statusid', '1')->first();
        if (empty($cust)) {
            return getSystemResp('Харилцагчийн мэдээлэл системд бүртгэлгүй байна.', 500);
        }

        // Зээлийн гүйлгээний мэдээлэл үүсгэх
        $lnService = new LoanTransaction();
        $lnService->txn_acnt_code = $data['txnAcntCode'];
        $lnService->cur_code = $acnt->cur_code;
        $lnService->tran_amt = $data['amount'];
        $lnService->tran_cur_code = $acnt->cur_code;
        $lnService->identity_type = "MANUAL";
        $lnService->rate = 1;
        $lnService->internal_cont_acnt_code = $polaris->internalAccount;
        $lnService->cont_amount = $data['amount'];
        $lnService->txn_amount = $data['amount'];
        $lnService->cont_cur_code = 'MNT';
        $lnService->cont_rate = 1;
        $lnService->txn_desc = $polaris->savingLoan->txnDesc;
        $lnService->tcust_name = $cust->fname;
        $lnService->tcust_addr = $cust->address ?? "";
        $lnService->tcust_register = $cust->regno;
        $lnService->tcust_register_mask = $cust->register_mask_code;
        $lnService->tcust_contact = $cust->phone;
        $lnService->source_type = "OI";
        $lnService->is_tmw = 1;
        $lnService->is_preview = 0;
        $lnService->is_preview_fee = 0;
        $lnService->cont_acnt_code = $data['contAcntCode'];
        $lnService->cont_bank_code = $data['contBankCode'];
        $lnService->created_at = Carbon::now();
        $lnService->created_by = $user->userid;
        $lnService->statusid = 0;
        $lnService->txn_type = 1;
        $lnService->instid = $data['instid'];
        $lnService->save();
        $sysDate = formatDate(Carbon::now());
        $dateResp = $polaris->getDate($data['instid']);
        if ($dateResp['status'] == 200) {
            $sysDate = $dateResp['data'];
        }
        $req_data = [
            "txnAmount" => $data['amount'],
            "curCode" => $acnt->cur_code,
            "rate" => 1,
            "contAcntCode" => $polaris->internalAccount,
            "contAmount" => $data['amount'],
            "contCurCode" => "MNT",
            "contRate" => 1,
            // "rateTypeId" => 5,
            "txnDesc" => $lnService->txn_desc,
            "sourceType" => "OI",
            "isPreview" => 0,
            "isPreviewFee" => 0,
            "isBlockInt" => 0,
            "collAcnt" => [
                "name" => $polaris->savingLoan->collAcnt->name,
                "name2" => $polaris->savingLoan->collAcnt->name2,
                "custCode" => $cust->cif,
                "prodCode" => $polaris->savingLoan->collAcnt->prodCode,
                "prodType" => "COLL",
                "collType" => "4",
                "brchCode" => $polaris->brchCode,
                "status" => "N",
                "key2SysNo" => "1306",
                "key2" => $data['txnAcntCode'], // Барьцаалж байгаа хадгаламжийн данс
                "price" => $data['amount'],
                "curCode" => "MNT"
            ],
            "loanAcnt" => [
                "custCode" => $cust->cif,
                "name" => empty($cust->shortname) ? $cust->fname : $cust->shortname,
                "name2" => empty($cust->shortname2) ? $cust->fname2 : $cust->shortname2,
                "prodCode" => $polaris->savingLoan->loanAcnt->prodCode,
                "curCode" => "MNT",
                "approvAmount" => $data['amount'],
                "approvDate" => $sysDate,
                "startDate" => $sysDate,
                "termLen" => 6,
                "endDate" => $acnt->maturity_date, // Хадгаламжийн дансны дуусах хугацаа
                "purpose" => $polaris->savingLoan->loanAcnt->purpose,
                "subPurpose" => $polaris->savingLoan->loanAcnt->subPurpose,
                "isNotAutoClass" => 0,
                "comRevolving" => 0,
                "dailyBasisCode" => $polaris->savingLoan->loanAcnt->dailyBasisCode,
                "acntManager" => $polaris->savingLoan->loanAcnt->acntManager,
                "brchCode" => $polaris->brchCode,
                "segCode" => $cust->segment,
                "status" => "N",
                "slevel" => 1,
                "classNoTrm" => 1,
                "classNoQlt" => 1,
                "classNo" => 1,
                "repayAcntCode" => null,
                "isBrowseAcntOtherCom" => 0,
                "repayPriority" => 0,
                "losMultiAcnt" => 0,
                "impairmentPer" => 0,
                "validLosAcnt" => 1,
                "prodType" => "LOAN",
                "secType" => 0
            ],
            "acntNrs" => [
                "startDate" => $sysDate, // now date
                "calcAmt" => $data['amount'],
                "payType" => "1",
                "payFreq" => "E",
                "payDay1" => 20,
                "holidayOption" => "2",
                "shiftPartialPay" => 0,
                "termFreeTimes" => 0,
                "intTypeCode" => "SIMPLE_INT",
                "endDate" => $acnt->maturity_date // Хадгаламжийн дансны дуусан хугацаа
            ],
            "acntInt" => [
                "intTypeCode" => "SIMPLE_INT",
                "intRate" => $polaris->savingLoan->loanAcnt->marginRate + $intRate
            ]
        ];
        // return getSystemResp($req_data, 200);

        $respdata = $polaris->sendRequest(13610265, [$req_data], $data['instid']);
        if ($respdata['status'] == 200) {
            $nesresp = $respdata['data'];
            $lnService->statusid = 2;
            $lnService->core_jrno = $nesresp['txnJrno'];
            $lnService->is_supervisor = $nesresp['isSupervisor'] ?? 0;
            $lnService->jr_item_no_and_incr = $nesresp['jrItemNoAndIncr'] ?? 0;
            $lnService->err_desc = "Банк дээрх гүйлгээг шалгах хэрэгтэй.";
            $lnService->save();
            // Corprate gateway дээр хүсэлт илгээх хэсэг
            // return getSystemResp('Зээл амжилттай.', 200);
            $resp = $this->corporateTransaction($cgwBankCode, $data, $acnt);
            $lnService->err_desc = "";
            if ($resp['status'] == 200) {
                $lnService->txn_jrno = $resp['journal_no'] ?? 0;
                $lnService->statusid = 1;
                $lnService->save();
                return getSystemResp("Зээл олголт амжилттай хийгдлээ.");
            } else {
                // Corprate gateway гүйлгээ амжилтгүй болсон учир буцаалт хийнэ.
                $req_data = [
                    'orgJrno' => $nesresp['txnJrno'],
                    'txnDesc' => 'Буцаалт - Зээл авах хүсэлт амжилтгүй болов.'
                ];
                $respdata = $polaris->sendRequest(13619998, [$req_data], $data['instid']);
                if ($respdata['status'] == 200) {
                    $lnService->statusid = 3;
                    $lnService->core_corr_jrno = $respdata['data'];
                    $lnService->err_desc = $respdata['data'];
                    $lnService->save();
                } else {
                    $lnService->err_desc = 'Банк дээрх гүйлгээ амжилтгүй болсон үед Core системийн гүйлгээний
                    буцаалт амжилтгүй болов. Core системийн гүйлгээний дугаар: ' . $nesresp['txnJrno'];
                    $lnService->save();
                    return getSystemResp('Уучлаарай, зээл олголт амжилтгүй боллоо.', 500);
                }
                return $resp;
            }
        } else {
            $lnService->err_desc = $respdata['data'];
            $lnService->save();
            return getSystemResp('Уучлаарай, зээл олголт амжилтгүй боллоо.', 500);
        }
    }

    public function getCallCgwBankCode($data)
    {
        $sysConf = SysConf::where('typeid', '01')->where('instid', $data['instid'])->first();
        if ($sysConf) {
            $sysConfCgw = json_decode($sysConf->config);
            $cgwBankCode = '';
            if (empty($sysConfCgw->cgw)) {
                return getSystemResp('Системийн ерөнхий тохиргоо дээр cgw тохиргоо хийгдээгүй байна.', 500);
            } else {
                $cgw = $sysConfCgw->cgw;
                if (isset($cgw->configAll) && isset($cgw->configAll->isUse)) {
                    if ($cgw->configAll->isUse) {
                        $cgwBankCode = $cgw->configAll->bankCode;
                    } else {
                        if (isset($cgw->generalConfig)) {
                            foreach ($cgw->generalConfig as $key => $value) {
                                if ($key == $data['contBankCode']) {
                                    $cgwBankCode = $value;
                                    break;
                                }
                            }
                            if ($cgwBankCode == '') {
                                if (isset($cgw->generalConfig->default)) {
                                    $cgwBankCode = $cgw->generalConfig->default;
                                } else {
                                    // Системийн ерөнхий тохиргоо дээр generalConfig default тохиргоо хийгдээгүй байна.
                                    return getSystemResp('Cgw generalConfig default тохиргоо хийгдээгүй байна.', 500);
                                }
                            }
                            // if (isset()) {}
                        } else {
                            // Системийн ерөнхий тохиргоо дээр configAll.isUse = false үед generalConfig тохиргоо хийгдээгүй байна.
                            return getSystemResp('Системийн ерөнхий тохиргоо хийгдээгүй байна.', 500);
                        }
                    }
                    return getSystemResp($cgwBankCode);
                } else {
                    return getSystemResp('Cgw configAll тохиргоо хийгдээгүй байна.', 500);
                }
            }
        } else {
            return getSystemResp('Системийн ерөнхий тохиргоо хийгдээгүй байна.', 500);
        }
    }

    public function giveLoan($data)
    {
        $polaris = new PolarisApiRequestService($data['instid']);
        $acnt = LoanAcnt::where('acnt_code', $data['txnAcntCode'])->where('instid', $data['instid'])->first();
        $cgwBank = $this->getCallCgwBankCode($data);
        if ($cgwBank['status'] == 200) {
            $cgwBankCode = $cgwBank['data'];
        } else {
            return getSystemResp($cgwBank['data'], $cgwBank['status']);
        }
        if (empty($acnt)) {
            return getSystemResp($data['txnAcntCode'] . ' дугаартай данс олдсонгүй.', 500);
        }
        if ($acnt->cur_code != 'MNT') {
            return getSystemResp($acnt->cur_code . ' системд зөвшөөрөгдөөгүй валют.', 500);
        }
        $user = auth()->user();
        $cust = Cust::where('instid', $data['instid'])->where('regno', $user->registernum)->where('statusid', '1')->first();
        if (empty($cust)) {
            return getSystemResp('Харилцагчийн мэдээлэл системд бүртгэлгүй байна.', 500);
        }
        // Зээлийн гүйлгээний мэдээлэл үүсгэх
        $lnService = new LoanTransaction();
        $lnService->txn_acnt_code = $data['txnAcntCode'];
        $lnService->cur_code = $acnt->cur_code;
        $lnService->tran_amt = $data['amount'];
        $lnService->tran_cur_code = $acnt->cur_code;
        $lnService->identity_type = "MANUAL";
        $lnService->rate = 1;
        $lnService->internal_cont_acnt_code = $polaris->internalAccount;
        $lnService->cont_amount = $data['amount'];
        $lnService->txn_amount = $data['amount'];
        $lnService->cont_cur_code = 'MNT';
        $lnService->cont_rate = 1;
        $lnService->txn_desc = "Зээл олголтын гүйлгээ";
        $lnService->tcust_name = $cust->fname;
        $lnService->tcust_addr = $cust->address ?? "";
        $lnService->tcust_register = $cust->regno;
        $lnService->tcust_register_mask = $cust->register_mask_code;
        $lnService->tcust_contact = $cust->phone;
        $lnService->source_type = "TLLR";
        $lnService->is_tmw = 1;
        $lnService->is_preview = 0;
        $lnService->is_preview_fee = 0;
        $lnService->cont_acnt_code = $data['contAcntCode'];
        $lnService->cont_bank_code = $data['contBankCode'];
        $lnService->created_at = Carbon::now();
        $lnService->created_by = $user->userid;
        $lnService->statusid = 0;
        $lnService->txn_type = 1;
        $lnService->instid = $data['instid'];
        $lnService->save();

        $req_data = [
            "txnAcntCode" => $data['txnAcntCode'],
            "txnAmount" => $data['amount'],
            "curCode" => $acnt->cur_code,
            "tranAmt" => $data['amount'],
            "tranCurCode" => $acnt->cur_code,
            "identityType" => $lnService->identity_type,
            "rate" => 1,
            "contAcntCode" => $polaris->internalAccount,
            "contAmount" => $data['amount'],
            "contCurCode" => "MNT",
            "contRate" => 1,
            // "rateTypeId" => 5,
            "txnDesc" => $lnService->txn_desc,
            "tcustName" => $cust->fname,
            "tcustAddr" => $cust->address ?? "",
            "tcustRegister" => $cust->regno,
            "tcustRegisterMask" => $cust->register_mask_code,
            "tcustContact" => $cust->phone,
            "sourceType" => "TLLR",
            "isTmw" => 1,
            "isPreview" => 0,
            "isPreviewFee" => 0
        ];

        $respdata = $polaris->sendRequest(13610262, [$req_data], $data['instid']);
        if ($respdata['status'] == 200) {
            $nesresp = $respdata['data'];
            $lnService->statusid = 2;
            $lnService->core_jrno = $nesresp['txnJrno'];
            $lnService->is_supervisor = $nesresp['isSupervisor'] ?? 0;
            $lnService->jr_item_no_and_incr = $nesresp['jrItemNoAndIncr'] ?? 0;
            $lnService->err_desc = "Банк дээрх гүйлгээг шалгах хэрэгтэй.";
            $lnService->save();
            // Corprate gateway дээр хүсэлт илгээх хэсэг
            // return getSystemResp('Зээл амжилттай.', 200);
            $resp = $this->corporateTransaction($cgwBankCode, $data, $acnt);
            $lnService->err_desc = "";
            if ($resp['status'] == 200) {
                $lnService->txn_jrno = $resp['journal_no'] ?? 0;
                $lnService->statusid = 1;
                $lnService->save();
                return getSystemResp("Зээл олголт амжилттай хийгдлээ.");
            } else {
                // Corprate gateway гүйлгээ амжилтгүй болсон учир буцаалт хийнэ.
                $req_data = [
                    'orgJrno' => $nesresp['txnJrno'],
                    'txnDesc' => 'Буцаалт - Зээл авах хүсэлт амжилтгүй болов.'
                ];
                $respdata = $polaris->sendRequest(13619998, [$req_data], $data['instid']);
                if ($respdata['status'] == 200) {
                    $lnService->statusid = 3;
                    $lnService->core_corr_jrno = $respdata['data'];
                    $lnService->err_desc = $resp['data'];
                    $lnService->save();
                } else {
                    $lnService->err_desc = 'Банк дээрх гүйлгээ амжилтгүй болсон үед Core системийн гүйлгээний
                    буцаалт амжилтгүй болов. Core системийн гүйлгээний дугаар: ' . $nesresp['txnJrno'] . ' Буцаалтын гүйлгээ: ' . $respdata['data'];
                    $lnService->save();
                    return getSystemResp('Уучлаарай, зээл олголт амжилтгүй боллоо.', 500);
                }
                return $resp;
            }
        } else {
            $lnService->err_desc = $respdata['data'];
            $lnService->save();
        }
        return $respdata;
    }

    public function corporateTransaction($bankCode, $data, $acnt)
    {
        switch ($bankCode) {
            case '04':
                $corp = new CorpGatewayTdbService($data['instid']);
                break;
            case '05':
                $corp = new CorpGatewayService($data['instid']);
                break;

            default:
                $corp = new CorpGatewayTdbService($data['instid']);
                break;
        }
        if (Str::startsWith($data['contBankCode'], $bankCode)) {
            // банк доторх гүйлгээ
            $resp = $corp->transactionDemostic(
                [
                    "fromAccount" => "string",
                    "toAccount" => $data['contAcntCode'],
                    "toCurrency" => $acnt->cur_code,
                    "toAccountName" => $data['contAcntName'],
                    "toBank" => $data['contBankCode'],
                    "amount" => $data['amount'],
                    "description" => "Зээл олголтын гүйлгээ. Данс: " . $data['txnAcntCode'],
                    "currency" => $acnt->cur_code,
                    "transferid" => random_number()
                ]
            );
        } else {
            // Бусад банкны данс руу гүйлгээ
            $resp = $corp->transInterBank(
                [
                    "fromAccount" => "string",
                    "toAccount" => $data['contAcntCode'],
                    "toCurrency" => $acnt->cur_code,
                    "toAccountName" => $data['contAcntName'],
                    "toBank" => $data['contBankCode'],
                    "amount" => $data['amount'],
                    "description" => "Зээл олголтын гүйлгээ. Данс: " . $data['txnAcntCode'],
                    "currency" => $acnt->cur_code,
                    "transferid" => random_number()
                ]
            );
        }
        return $resp;
    }

    public function paymentLoan($instid, $qpay)
    {
        $polaris = new PolarisApiRequestService($instid);
        $cuser = CustUser::where('userid', $qpay->created_by)->first();
        $lnService = new LoanTransaction();
        $lnService->txn_acnt_code = $polaris->repay_susp_accountno;
        $lnService->cur_code = $qpay->cur_code ?? 'MNT';
        $lnService->tran_amt = $qpay->amount;
        $lnService->tran_cur_code = $qpay->cur_code ?? 'MNT';
        $lnService->identity_type = "MANUAL";
        $lnService->rate = 1;
        $lnService->cont_amount = $qpay->amount;
        $lnService->txn_amount = $qpay->amount;
        $lnService->cont_cur_code = 'MNT';
        $lnService->cont_rate = 1;
        $lnService->txn_desc = "Зээл төлөлтийн гүйлгээ";
        $lnService->source_type = "TLLR";
        $lnService->is_tmw = 1;
        $lnService->is_preview = 0;
        $lnService->is_preview_fee = 0;
        $lnService->cont_acnt_code = $qpay->to_account;
        $lnService->tcust_name = $cuser->firstname;
        $lnService->created_at = Carbon::now();
        $lnService->created_by = $qpay->created_by;
        $lnService->statusid = 0;
        $lnService->instid = $instid;
        $lnService->txn_type = 0;
        $lnService->save();
        $req_data = [
            "txnAcntCode" => $qpay->to_account,
            "txnAmount" => $qpay->amount,
            "curCode" => "MNT",
            "rate" => 1,
            "contAcntCode" => $polaris->repay_susp_accountno,
            "contAmount" => $qpay->amount,
            "contRate" => 1,
            "contCurCode" => "MNT",
            "txnDesc" => $lnService->txn_desc,
            "sourceType" => "OI",
            "isPreview" => 0,
            "isPreviewFee" => 0,
            "isTmw" => 1
        ];

        if ($qpay->typeid == 1 || $qpay->typeid == '1') {
            // Зээлийн данс хаах(бэлэн бус)
            $req_data['addParams'] = [
                [
                    'contAcntType' => 'CASA',
                    'chkAcntInt' => 'Y'
                ]
            ];
            $respdata = $polaris->sendRequest(13610267, [$req_data], $instid);
        } else {
            // NES зээлийн дансны төлөлт хийх хүсэлт илгээх
            $respdata = $polaris->sendRequest(13610250, [$req_data], $instid);
        }

        if ($respdata['status'] == 200) {
            $nesresp = $respdata['data'];
            $lnService->statusid = 1;
            // $lnService->txn_jrno = $nesresp['txnJrno'];
            $lnService->core_jrno = $nesresp['txnJrno'];
            $lnService->is_supervisor = $nesresp['isSupervisor'] ?? 0;
            $lnService->jr_item_no_and_incr = $nesresp['jrItemNoAndIncr'] ?? 0;
            $lnService->save();
        } else {
            $lnService->statusid = 2;
            $lnService->err_desc = $respdata['data'];
            $lnService->save();
        }
        return $respdata;
    }

    public function getCalcAvialBalTd($data, $polaris)
    {
        $acntService = new AcntService();
        $maxAmount = 0;
        $tddata = $acntService->getTdAccountDetail($data['txnAcntCode'], $data['instid']);
        if ($tddata['status'] == 200) {
            $tddata = json_decode(json_encode($tddata['data']));
            $maxAmount = $tddata->currentBal * $polaris->savingLoan->giveLoanMaxRate / 100;
        }
        return $maxAmount;
    }

    public function getLoanInfoTdAcnt($data)
    {

        $polaris = new PolarisApiRequestService($data['instid']);
        $acntService = new AcntService();
        $intRate = 0;
        $maxAmount = 0;

        $tddata = $acntService->getTdCollInfo($data['txnAcntCode'], $data['instid']);
        if ($tddata['status'] == 200) {
            $tddata = json_decode(json_encode($tddata['data']));
            $maxAmount = $this->getCalcAvialBalTd($data, $polaris) - $tddata->utilized;
        } else {
            if ($tddata['status'] == 404) {
                return getSystemResp('Хадгаламжийн данс олдсонгүй', 500);
            }
            $maxAmount = $this->getCalcAvialBalTd($data, $polaris);
        }

        $intdetail = $acntService->getAccountInt($data['txnAcntCode'], $data['instid']);
        if ($intdetail['status'] == 200) {
            foreach ($intdetail['data'] as $key => $value) {
                $value = json_decode(json_encode($value));
                if ($value->intTypeCode == 'SIMPLE_INT') {
                    if (empty($value->intRate)) {
                        return getSystemResp('Хадгаламжийн дансны хүүний мэдээлэл алдаатай байна.', 500);
                    }
                    $intRate = $value->intRate;
                    break;
                }
            }
            return getSystemResp([
                'maxAmount' => $maxAmount,
                'intRate' => round($polaris->savingLoan->loanAcnt->marginRate + $intRate, 2)
            ]);
        } else {
            return getSystemResp($intdetail['data'], $intdetail['status']);
        }
    }

    /**
     * Зээлийн данс хаах(бэлэн бус)
     *
     * @param  mixed $data
     * @return void
     */
    public function closeLoanAcnt($data) {
        $polaris = new PolarisApiRequestService($data['instid']);
        $req_data = [
            "txnAcntCode" => "110013000058",
            "txnAmount" => 116321.03,
            "curCode" => "MNT",
            "rate" => 1,
            "rateTypeId" => "4",
            "contAcntCode" => "1100CA000016",
            "contAmount" => 116321.03,
            "contRate" => 1,
            "contCurCode" => "MNT",
            "txnDesc" => "loan closing",
            "sourceType" => "OI",
            "isPreview" => 0,
            "isPreviewFee" => null,
            "isTmw" => 1
        ];
        return $polaris->sendRequest(13610267, [$req_data], $data['instid']);
    }
}
