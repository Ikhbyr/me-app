<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use App\Services\CorpGatewayTdbService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
 */

$router->get('/test', [function (Request $request) use ($router) {
    return date("Y-m-d", strtotime(Carbon::now()));
    echo checkInstPerm('cu0110', 23) ? 'nice' : 'lol';
}]);

$router->get('/check-tdb', [function (Request $request) use ($router) {
    $corp = new CorpGatewayTdbService(23);
    return $corp->getAccountBalance();
}]);

$router->get('/', function () use ($router) {
    return $router->app->version();
});

/**
 * ---------------------------REAL API URL------------------------------------
 */
Route::group(['prefix' => '/mobile-api', 'middleware' => ['throttle:5,1']], function ($router) {
    // Google Authenticator - Begin
    $router->post('/cust-user/login/google', 'Mobile\AuthController@loginWithGoogle');
    // Google Authenticator - End

    // QPAY гаднаас дуудагддаг function
    $router->get('/loan/payment/{instid}/{invoiceno}', ['uses' => 'Mobile\LoanController@paymentCheckLoanQpay']);
});

Route::group(['prefix' => '/mobile-api', 'middleware' => ['throttle:70,1']], function ($router) {
    $router->post('/cust-user/login', 'Mobile\AuthController@login');
    $router->post('/cust-user/getinst', 'Mobile\CustUserController@getCustInsts');
    $router->post('/cust-user/forgotPassword', 'Mobile\CustUserController@forgotPassword');
    $router->post('/cust-user/resetPassword', 'Mobile\CustUserController@resetPassword');
    $router->post('/cust-user/passTokenConfirm', 'Mobile\CustUserController@passTokenConfirm');
    $router->post('/cust-user/confirm/{token}', 'Mobile\CustUserController@confirmRegister');
    $router->get('/cust-user/confirm/{token}', 'Mobile\CustUserController@confirmRegister');
    $router->post('/passpolicy/get', 'Mobile\CustUserController@getPassPolicy');
    // Суурь системийн цаг авах
    $router->post('/polaris/get-system-time', 'CoreSystemController@getPolarisTime');

    // Upload photo
    $router->post('/upload/image', 'FileUploadController@uploadImage');
    Route::group(['middleware' => ['auth']], function ($router) {
        // check Login
        $router->post("/auth/check", "Mobile\AuthController@check");
        $router->post("/auth/logout", "Mobile\AuthController@logout");
        $router->post('/auth/activity-log', ['uses' => 'Mobile\AuthController@getLoginActivityLog']);

        // Notification
        $router->post('/notifications/get', ['uses' => 'Mobile\NotificationController@getNotifications']);
        $router->post('/notifications/read', ['uses' => 'Mobile\NotificationController@updateRead']);
        $router->post('/notifications/getUnreadCount', ['uses' => 'Mobile\NotificationController@getUnreadCount']);
        $router->post('/send-notification', ['uses' => 'Mobile\NotificationController@sendNotification']);

        // USER change pass
        $router->post('/cust-user/changePassword', ['uses' => 'Mobile\CustUserController@changePassword']);
        Route::group(['middleware' => ['password_expired']], function ($router) {
            $router->post("/auth/checkperm", function (Request $request) {
                return ['permid' => $request->perm, 'change' => true, 'create' => true, 'delete' => true, 'read' => true, 'update' => true];
            });
            $router->post("/auth/checkpermemp", function (Request $request) {
                return ['permid' => $request->perm, 'change' => true, 'create' => true, 'delete' => true, 'read' => true, 'update' => true];
            });

            // USER API - Begin
            $router->post('/cust-user/show', ['uses' => 'Mobile\CustUserController@show', 'middleware' => 'check_perm:']); // * cu0110
            $router->post('/cust-user/update', ['uses' => 'Mobile\CustUserController@update', 'middleware' => 'check_perm:']); // * cu0230
            $router->post('/cust-user/photo-update', ['uses' => 'Mobile\CustUserController@photoUpdate', 'middleware' => 'check_perm:']);  // * cu0330
            //$router->post('/cust-user/delete', ['uses' => 'Mobile\CustUserController@destroy', 'middleware' => 'check_perm:']);
            $router->post('/cust-user/getCustAccounts', ['uses' => 'Mobile\CustUserController@getCustAccounts', 'middleware' => 'check_perm:']); // done ac0100
            $router->post('/cust-user/getAllAccounts', ['uses' => 'Mobile\CustUserController@getAllAccounts', 'middleware' => 'check_perm:']); // done ac0100
            $router->post('/cust-user/connect-account', ['uses' => 'Mobile\CustUserAccountController@createAccount', 'middleware' => 'check_perm:']); // * ac0320
            $router->post('/cust-user/delete-connect-account', ['uses' => 'Mobile\CustUserAccountController@deleteAccount', 'middleware' => 'check_perm:']); // * ac0440
            $router->post('/cust-user/get-connect-account', ['uses' => 'Mobile\CustUserAccountController@getOwnAccount', 'middleware' => 'check_perm:']); // * ac0200

            $router->post('/account/getStatement', ['uses' => 'Mobile\AccountController@getAccountStatemnt']); // done ac0510
            $router->post('/account/detail', ['uses' => 'Mobile\AccountController@getAccountDetail']); // done ac0611.ac0612.ac0613.ac0614
            $router->post('/account/detailCasa', ['uses' => 'Mobile\AccountController@getCasaAccountDetail', 'middleware' => 'check_perm:']); // done ac0612
            $router->post('/account/detailTd', ['uses' => 'Mobile\AccountController@getTdAccountDetail', 'middleware' => 'check_perm:']); // done ac0611
            $router->post('/account/detailLoan', ['uses' => 'Mobile\AccountController@getLoanAccountDetail', 'middleware' => 'check_perm:']); // done ac0613
            $router->post('/account/detailCca', ['uses' => 'Mobile\AccountController@getCreditAccountDetail', 'middleware' => 'check_perm:']); // done ac0614
            $router->post('/account/int-detail', ['uses' => 'Mobile\AccountController@getAccountInt']);
            $router->post('/loan/repayment', ['uses' => 'Mobile\AccountController@getRepaymentSchedule', 'middleware' => 'check_perm:']); // done ac0710
            $router->post('/loan/get-loan', ['uses' => 'Mobile\LoanController@getLoan', 'middleware' => 'check_perm:']); // done lo0120
            $router->post('/loan/get-loan-saving', ['uses' => 'Mobile\LoanController@getLoanSaving', 'middleware' => 'check_perm:']); // done lo0120
            $router->post('/loan/get-loan-info-tdacnt', ['uses' => 'Mobile\LoanController@getLoanInfoTdAcnt', 'middleware' => 'check_perm:']); // done lo0120
            $router->post('/loan/payment-loan', ['uses' => 'Mobile\LoanController@paymentLoanQpay', 'middleware' => 'check_perm:']); // done pa0120

            $router->post('/cust/getDetail', ['uses' => 'Mobile\CustController@getDetail', 'middleware' => 'check_perm:cu0410']); // done cu0410

            // DIC
            $router->post('/dic/show', ['uses' => 'DicController@getDic', 'middleware' => 'check_perm:']);
            $router->post('/dic/showbyparent', ['uses' => 'DicController@getDicByParent', 'middleware' => 'check_perm:']);
            $router->post('/dic/getDicWithChildren', ['uses' => 'DicController@getDicWithChildren', 'middleware' => 'check_perm:']);
            // USER API - End

            $router->post('/contract/show', ['uses' => 'Mobile\ContactController@show', 'middleware' => 'check_perm:']);
        });
    });
});
