<?php

namespace App\Services;

use App\Models\Cust;
use App\Models\CustUser;
use App\Models\CustUserRole;
use Carbon\Carbon;

class UserService
{
    public function __construct()
    {
    }

    public static function checkToken($token)
    {
        $resp = array();
        if ($token == "4") {
            $resp['code'] = true;
        } else {
            $resp['code'] = false;
            switch ($token) {
                case "1":
                    $msg = "Reset request not created!";
                    break;
                case "2":
                    $msg = "Password changed previously by this token";
                    break;
                case "3":
                    $msg = "Token expired!";
                    break;
            }
            $resp['msg'] = $msg;
        }
        return $resp;
    }

    public function createCustomerInfo($regno)
    {
        $polaris = new PolarisApiRequestService();
        $respdata = $polaris->sendRequest(13610335, [$regno]);
        if ($respdata['status'] == 200) {
            $custdata = $respdata['data'];
            $respdata = $polaris->sendRequest(13610310, [$custdata['custCode']]);

            if ($respdata['status'] == 200) {
                $user = auth()->user();
                $custdata = $respdata['data'];
                $cust = new Cust();
                $cust->instid = $user->instid;
                $cust->corrid = $custdata['id'] ?? null; //
                $cust->cif = $custdata['custCode'] ?? null;
                $cust->familyname = $custdata['familyName'] ?? null;
                $cust->familyname2 = $custdata['familyName2'] ?? null;
                $cust->lname = $custdata['lastName'] ?? null;
                $cust->lname2 = $custdata['lastName2'] ?? null;
                $cust->fname = $custdata['firstName'] ?? null;
                $cust->fname2 = $custdata['firstName2'] ?? null;
                $cust->gender = $custdata['sexName'] ?? null;
                $cust->regno = $custdata['registerCode'] ?? null;
                $cust->register_mask_code = $custdata['registerMaskCode'] ?? null;
                $cust->nationality = $custdata['nationalityName'] ?? null;
                $cust->birthday = new Carbon($custdata['birthDate'] ?? null);
                $cust->lang = $custdata['langCode'] ?? null;
                $cust->ethnicity = $custdata['ethnicGrpName'] ?? null;
                $cust->citizenship = $custdata['countryName'] ?? null; //
                $cust->birthplace = $custdata['birthPlaceName'] ?? null;
                $cust->segment = $custdata['custSegCode'] ?? null; //
                $cust->employment = $custdata['employmentId'] ?? null;
                // $cust->categories = $custdata['name'] ?? null;
                $cust->education = $custdata['eduName'] ?? null;
                $cust->maritalstatus = $custdata['maritalStatusName'] ?? null;
                $cust->phone = $custdata['mobile'] ?? null;
                $cust->phone2 = $custdata['phone'] ?? null;
                $cust->email = $custdata['email'] ?? null; //
                $cust->fax = $custdata['fax'] ?? null; //
                $cust->familysize = $custdata['familyCnt'] ?? null; //
                $cust->industry = $custdata['industryName'] ?? null; //
                $cust->shortname = $custdata['shortName'] ?? null; //
                $cust->shortname2 = $custdata['shortName2'] ?? null; //
                $cust->isbl = $custdata['isBl'] ?? null;
                $cust->iscompanycustomer = $custdata['isCompanyCustomer'] ?? null;
                $cust->ispolitical = $custdata['isPolitical'] ?? null;
                $cust->isvatpayer = $custdata['isVatPayer'] ?? null;
                $cust->monthlyincome = $custdata['monthlyIncome'] ?? null;
                $cust->immovabletype = $custdata['immovableType'] ?? null;
                $cust->ownership = $custdata['ownerShip'] ?? null;
                $cust->region = $custdata['name'] ?? null; //
                $cust->subregion = $custdata['name'] ?? null; //
                $cust->address = $custdata['name'] ?? null; //
                $cust->statusid = 1;
                $cust->created_at = Carbon::now();
                $cust->created_by = $user->userid;
                $cust->updated_at = Carbon::now();
                $cust->updated_by = $user->userid;
                $cust->save();
                return [
                    'data' => $cust,
                    'status' => 200
                ];
            }
        }
        return $respdata;
    }

    public function getCustAccounts($custCode, $instid, $isAll = false, $isread = true)
    {
        $respdata = $this->getNesCustAccounts($custCode, $instid);
        $casa = new AcntService();
        if ($respdata['status'] == 200) {
            $respdata['data'] = $casa->createCasaAcntList($respdata['data'], [], $instid);
        }
        if ($isread) {
            $respdata['data'] = $casa->getAccounts([], $instid, $isAll);
        } else {
            $respdata['data'] = [];
        }
        $respdata['status'] == 200;
        return $respdata;
    }

    public function getNesCustAccounts($custCode, $instid)
    {
        $polaris = new PolarisApiRequestService($instid);
        return $polaris->sendRequest(13610312, [$custCode, 0, -1], $instid);
    }

    public function getInstAccounts($instid, $isread = true)
    {
        $user = auth()->user();
        $cust = Cust::select('cif')->where('instid', $instid)->where('regno', $user->registernum)->first();
        $data = $this->getCustAccounts($cust->cif, $instid, false, $isread);
        return json_decode(json_encode($data['data']), true);
    }

    public function deleteCustUserRoleInst($userid, $instid)
    {
        CustUserRole::where('userid', $userid)->where('instid', $instid)->delete();
    }

    public function getCustAccountAllPolaris()
    {
        $user = auth()->user();
        $custuser = CustUser::find($user->userid);
        if ($custuser && $custuser->status != -1) {
            if ($custuser->insts) {
                for ($i = 0; $i < count($custuser->insts); $i++) {
                    $elem = $custuser->insts[$i];
                    $this->getInstAccounts($elem['id'], false);
                }
            }
        }
    }
}
