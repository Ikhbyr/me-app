<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\CustUserAccount;
use App\Models\LoanAcnt;
use App\Models\TdAcnt;
use App\Services\LoanService;
use App\Services\QpayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class LoanController extends Controller
{
    protected $service;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(LoanService $service)
    {
        $this->service = $service;
    }

    /**
     * getLoan - Зээл авах
     *
     * @param  mixed $request
     * @return void
     */
    public function getLoan(Request $request)
    {
        $validated = $this->validate($request, [
            'instid' => 'required|numeric',
            'txnAcntCode' => 'required',
            'contId' => 'required',
            'amount' => 'required|numeric'
        ]);
        if (!checkInstPerm('lo0120', $validated['instid'])) {
            return response()->json('[lo0120] эрх олгогдоогүй байна.', 500);
        }
        $contAcnt = CustUserAccount::where('id', $validated['contId'])->where('statusid', 1)->where('cust_user_id', auth()->user()->userid)->first();
        if (empty($contAcnt)) {
            return response()->json('Хүлээн авах дансны мэдээлэл олдсонгүй.', 500);
        }
        $validated['contAcntCode'] = $contAcnt->acnt_code;
        $validated['contAcntName'] = $contAcnt->acnt_name;
        $validated['contBankCode'] = $contAcnt->bank_code;
        $data = $this->service->giveLoan($validated);
        return response()->json($data['data'], $data['status']);
    }

    /**
     * getLoanSaving - Хадгаламжийн данс барьцаалж зээл олгох - Бүх үйлдлийг нэгтгэсэн
     *
     * @param  mixed $instid Зээл авах гэж байгаа байгууллагын дугаар
     * @param  mixed $txnAcntCode Барьцаалж буй хадгаламжийн данс
     * @param  mixed $contId Өөрийн бүртгэлтэй дансны дугаар
     * @param  mixed $amount Хүсэж буй зээлийн дүн
     * @return void
     */
    public function getLoanSaving(Request $request)
    {
        $validated = $this->validate($request, [
            'instid' => 'required|numeric',
            'txnAcntCode' => 'required',
            'contId' => 'required',
            'amount' => 'required|numeric'
        ]);
        // if (!checkInstPerm('lo0120', $validated['instid'])) {
        //     return response()->json('[lo0120] эрх олгогдоогүй байна.', 500);
        // }
        $contAcnt = CustUserAccount::where('id', $validated['contId'])->where('statusid', 1)->where('cust_user_id', auth()->user()->userid)->first();
        if (empty($contAcnt)) {
            return response()->json('Хүлээн авах дансны мэдээлэл олдсонгүй.', 500);
        }
        // Зээлийн орлого хүлээж авах өөрийн дансны мэдээлэл
        $validated['contAcntCode'] = $contAcnt->acnt_code;
        $validated['contAcntName'] = $contAcnt->acnt_name;
        $validated['contBankCode'] = $contAcnt->bank_code;
        $data = $this->service->getLoanSaving($validated);
        return response()->json($data['data'], $data['status']);
    }

    /**
     * paymentLoanQpay - Зээл төлөлтийн нэхэмжлэх үүсгэх
     *
     * @param  mixed $request
     * @return void
     */
    public function paymentLoanQpay(Request $request)
    {
        $validated = $this->validate($request, [
            'instid' => 'required|numeric',
            'contAcntCode' => 'required',
            'amount' => 'required|numeric',
            'typeid' => 'in:0,1'
        ]);
        $qpay = new QpayService($validated['instid']);
        if (@$validated['typeid'] != 1) {
            if ($validated['amount'] < 20) {
                return response()->json('QPAY төлбөрийн хэрэгслийн шаардлагын дагуу хамгийн багадаа 20 төгрөгөөр гүйлгээ хийгдэнэ.', 500);
            }
        } else {
            if ($validated['amount'] == 0) {
                $validated['to_account'] = $validated['contAcntCode'];
                $validated['sender_invoice_no'] = random_number();
                $dqpay = $qpay->store($validated);
                $loanService = new LoanService();
                $resp = $loanService->paymentLoan($validated['instid'], $dqpay);
                if ($resp['status'] == 200) {
                    return response()->json('Зээл хаах хүсэлт амжилттай боллоо.');
                } else {
                    return response()->json($resp['data'], $resp['status']);
                }
            } else if ($validated['amount'] < 20) {
                return response()->json('QPAY төлбөрийн хэрэгслийн шаардлагын дагуу хамгийн багадаа 20 төгрөгөөр гүйлгээ хийгдэнэ.', 500);
            }
        }
        $user = Auth::user();
        if (!checkInstPerm('pa0120', $validated['instid'], $user)) {
            return response()->json('[pa0120] эрх олгогдоогүй байна.', 500);
        }
        $lnAcnt = LoanAcnt::where('acnt_code', $validated['contAcntCode'])->where('userid', $user->userid)->where('instid', $validated['instid'])->first();
        if (empty($lnAcnt)) {
            response()->json('Зээлийн данс бүртгэлгүй байна.', 500);
        }
        $validated['cur_code'] = $lnAcnt->cur_code;
        $resp = $qpay->createInvoice($validated);
        return response()->json($resp['data'], $resp['status']);
    }

    /**
     * getLoanInfoTdAcnt - Хадгаламж барьцаалсан зээлийн мэдээлэл авах
     *
     * @param  mixed $request
     * @return void
     */
    public function getLoanInfoTdAcnt(Request $request)
    {
        $validated = $this->validate($request, [
            'instid' => 'required|numeric',
            'txnAcntCode' => 'required'
        ]);
        $data = $this->service->getLoanInfoTdAcnt($validated);
        return response()->json($data['data'], $data['status']);
    }

    /**
     * paymentLoanQpay - QPAY гүйлгээний callback дуудалт
     *
     * @param  mixed $request
     * @return void
     */
    public function paymentCheckLoanQpay($instid, $invoiceno)
    {
        $qpay = new QpayService($instid);
        $qpay->callBackUrl($invoiceno);
    }
}
