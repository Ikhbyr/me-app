<?php

namespace App\Services;

use App\Models\LoanTransaction;
use Illuminate\Support\Facades\DB;

class LoanTransService
{
    public function list($withs = [], $deleted = false)
    {
        $user = auth()->user();
        if ($user->isadmin != '1') {
            $result = LoanTransaction::select(
                "tcust_name",
                "tran_amt",
                "cur_code",
                "txn_desc",
                "statusid",
                "txn_type",
                "id"
            )->with($withs)->where('loan_transaction.statusid', '<>', -1)->where('instid', $user->instid);
        } else {
            $result = LoanTransaction::select(
                "tcust_name",
                "tran_amt",
                "cur_code",
                "txn_desc",
                "statusid",
                "txn_type",
                "id"
            )->with($withs)->where('loan_transaction.statusid', '<>', -1);
        }
        return $result;
    }

    public function get($id, $withs = [])
    {
        return $this->list($withs)->where('loan_transaction.id', $id)->select("loan_transaction.*", "dic_main.name as cont_bank_name")
        ->leftJoin('dic_main', function($join) {
            $join->on('dic_main.value', '=', 'loan_transaction.cont_bank_code');
            $join->on('dic_main.parentid', '=', DB::raw('418'));
        });
    }

    public function loanBackTransaction($jrno)
    {
        $user = auth()->user();
        $tran = LoanTransaction::where('instid', $user->instid)->where('txn_jrno', $jrno)->first();
        if (empty($tran)) {
            return getSystemResp('Гүйлгээний мэдээлэл олдсонгүй.', 500);
        }
        $req_data = [
            'orgJrno' => $jrno,
            'txnDesc' => 'Буцаалт - Зээл авах хүсэлт амжилтгүй болов.'
        ];
        $polaris = new PolarisApiRequestService($user->instid);
        $respdata = $polaris->sendRequest(13619998, [$req_data], $user->instid);
        if ($respdata['status'] == 200) {
            $tran->statusid = 3;
            $tran->save();
            return getSystemResp('Буцаалт амжилттай боллоо.');
        } else {
            return getSystemResp('Уучлаарай, буцаалтын гүйлгээ амжилтгүй боллоо.', 500);
        }
    }
}
