<?php

namespace App\Http\Controllers\Back;

use App\Models\SysConf;
use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\LoanTransService;

class LoanTranController extends Controller
{
    protected $service;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(LoanTransService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request){
        $validated = $this->validate($request, [
            'filters' => 'nullable|array',
            'filters.*.field' => 'required|max:60',
            'filters.*.value' => 'nullable|max:60',
            'filters.*.cond' => 'nullable|max:10',
            'orders' => 'nullable|array',
            'orders.*.field' => 'nullable|max:60',
            'orders.*.dir' => 'nullable|max:5',
            'perPage' => 'nullable|numeric',
            'page' => 'nullable|numeric',
        ]);

        $withs = [];

        $SysConf = $this->service->list($withs)->selectRaw(
            "(CASE WHEN (txn_type = 1) THEN 'Зарлага' ELSE 'Орлого' END) as txn_type_name,
            (CASE WHEN (loan_transaction.statusid = 0) THEN 'Хүлээгдэж буй' ELSE
                CASE WHEN (loan_transaction.statusid = 1) THEN 'Амжилттай' ELSE
                    CASE WHEN (loan_transaction.statusid = 2) THEN 'Алдаатай (Буцаах шаардлагатай)' ELSE
                        CASE WHEN (loan_transaction.statusid = 3) THEN 'Буцааалт хийгдсэн' ELSE
                            CASE WHEN (loan_transaction.statusid = -1) THEN 'Устгагдсан' ELSE 'Төлөв тодорхойгүй' END
                        END
                    END
                END
            END) as status_name",
        );
        $SysConf = $this->applyFilters($SysConf, @$validated['filters']);
        $SysConf = $this->applyOrders($SysConf, @$validated['orders']);
        $SysConf = $this->applyPaginate($SysConf, @$validated['perPage'], @$validated['page']);

        return response()->json($SysConf);
    }

    public function show(Request $request) {
        $validated = $this->validate($request, [
            'id' => 'required'
        ]);
        $resp = $this->service->get($validated['id'])->selectRaw(
            "(CASE WHEN (txn_type = 1) THEN 'Зарлага' ELSE 'Орлого' END) as txn_type_name,
            (CASE WHEN (loan_transaction.statusid = 0) THEN 'Хүлээгдэж буй' ELSE
                CASE WHEN (loan_transaction.statusid = 1) THEN 'Амжилттай' ELSE
                    CASE WHEN (loan_transaction.statusid = 2) THEN 'Алдаатай (Буцаах шаардлагатай)' ELSE
                        CASE WHEN (loan_transaction.statusid = 3) THEN 'Буцааалт хийгдсэн' ELSE
                            CASE WHEN (loan_transaction.statusid = -1) THEN 'Устгагдсан' ELSE 'Төлөв тодорхойгүй' END
                        END
                    END
                END
            END) as status_name",
        )->first();
        return response()->json($resp);
    }

    public function loanBackTransaction(Request $request) {
        $validated = $this->validate($request, [
            'jrno' => 'required'
        ]);
        $resp = $this->service->loanBackTransaction($validated['jrno']);
        return response()->json($resp['data'], $resp['status']);
    }
}
