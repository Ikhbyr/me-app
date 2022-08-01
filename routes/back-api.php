<?php

/** @var \Laravel\Lumen\Routing\Router $router */

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

// $router->get('/test', [function (Request $request) use ($router) {
//     return Carbon::now()->getTimestampMs();
//     return base64_encode(file_get_contents('https://i.ibb.co/kHxcrrM/titan-logo.jpg'));
//     return hash_hmac('sha256', 'message', 'secret');
// }]);

Route::group(['prefix' => '/back-api', 'middleware' => ['throttle:5,1']], function ($router) {
    // Google Authenticator - Begin
    $router->post('/auth/login/google', ['uses' => 'Back\AuthController@loginWithGoogle', 'middleware' => 'check_perm:']);
    // Google Authenticator - End
});

Route::group(['prefix' => '/back-api', 'middleware' => ['throttle:70,1']], function ($router) {
    // Test
    $router->post('/polaris/test', ['uses' => 'Back\PolarisApiController@test', 'middleware' => 'check_perm:']);

    $router->post('/inst-user/login', ['uses' => 'Back\AuthController@login', 'middleware' => 'check_perm:']);
    $router->post('/inst-user/forgotPassword', ['uses' => 'Back\InstUserController@forgotPassword', 'middleware' => 'check_perm:']);
    $router->post('/inst-user/resetPassword', ['uses' => 'Back\InstUserController@resetPassword', 'middleware' => 'check_perm:']);
    $router->post('/inst-user/confirm/{token}', ['uses' => 'Back\InstUserController@confirmRegister', 'middleware' => 'check_perm:']);
    $router->get('/inst-user/confirm/{token}', ['uses' => 'Back\InstUserController@confirmRegister', 'middleware' => 'check_perm:']);
    $router->post('/passpolicy/get', ['uses' => 'Back\InstUserController@getPassPolicy', 'middleware' => 'check_perm:']);
    $router->get('/confirm-device/{token}', 'Back\AuthController@confirmDevice');

    Route::group(['middleware' => ['auth']], function ($router) {
        // check Login
        $router->post("/auth/check", ['uses' => "Back\AuthController@check", 'middleware' => 'check_perm:']);
        $router->post("/auth/logout", ['uses' => "Back\AuthController@logout", 'middleware' => 'check_perm:']);

        // USER change pass
        $router->post('/inst-user/changePassword', ['uses' => 'Back\InstUserController@changePassword', 'middleware' => 'check_perm:']);

        // Upload photo
        $router->post('/upload/image', 'FileUploadController@uploadImage');

        $router->post('/google/generateqr', ['uses' => 'GoogleAuthenticatorController@getAuthQrCode', 'middleware' => 'check_perm:']);
        $router->post('/google/checkcode', ['uses' => 'GoogleAuthenticatorController@getAuthCheckCode', 'middleware' => 'check_perm:']);
        $router->post('/google/useGoogleAuth', ['uses' => 'GoogleAuthenticatorController@updateUseGoogleAuth', 'middleware' => 'check_perm:']);

        Route::group(['middleware' => ['password_expired']], function ($router) {
            $router->post("/auth/checkperm", function (Request $request) {
                return ['permid' => $request->perm, 'change' => true, 'create' => true, 'delete' => true, 'read' => true, 'update' => true];
            });
            $router->post("/auth/checkpermemp", function (Request $request) {
                return ['permid' => $request->perm, 'change' => true, 'create' => true, 'delete' => true, 'read' => true, 'update' => true];
            });

            // MODULE API - Begin
            $router->post('/module/get', ['uses' => 'Back\ModuleController@index', 'middleware' => 'check_perm:']);
            $router->post('/module/getPerms', ['uses' => 'Back\ModuleController@getPerms', 'middleware' => 'check_perm:']);
            $router->post('/mobile-module/get', ['uses' => 'Back\ModuleController@getMobileModules', 'middleware' => 'check_perm:']);
            $router->post('/mobile-module/getPerms', ['uses' => 'Back\ModuleController@getMobilePerms', 'middleware' => 'check_perm:']);
            $router->post('/inst/own', ['uses' => 'Back\InstController@getOwnInst', 'middleware' => 'check_perm:']);


            // Customer
            $router->post('/cust/get', ['uses' => 'Back\CustController@index', 'middleware' => 'check_perm:cu0100']);
            $router->post('/cust/getDetail', ['uses' => 'Back\CustController@getDetail', 'middleware' => 'check_perm:cu0110']);

            // cust user
            $router->post('/cust-user/get-list-sm', ['uses' => 'Back\CustUserController@indexSmall', 'middleware' => 'check_perm:cu0200']);
            $router->post('/cust-user/get', ['uses' => 'Back\CustUserController@index', 'middleware' => 'check_perm:cu0200']);
            $router->post('/cust-user/create', ['uses' => 'Back\CustUserController@store', 'middleware' => 'check_perm:cu0220']);
            $router->post('/cust-user/directory', ['uses' => 'Back\CustUserController@directory', 'middleware' => 'check_perm:cu0200']);
            $router->post('/cust-user/update', ['uses' => 'Back\CustUserController@update', 'middleware' => 'check_perm:cu0230']);
            $router->post('/cust-user/delete', ['uses' => 'Back\CustUserController@destroy', 'middleware' => 'check_perm:cu0240']);
            $router->post('/cust-user/getDetail', ['uses' => 'Back\CustUserController@getDetail', 'middleware' => 'check_perm:cu0200']);
            $router->post('/cust-user/show', ['uses' => 'Back\CustUserController@show', 'middleware' => 'check_perm:cu0200']);

            // ADMIN & INST USER
            $router->post('/inst-user/get', ['uses' => 'Back\InstUserController@index', 'middleware' => 'check_perm:se2400']);
            $router->post('/inst-user/getadmin', ['uses' => 'Back\InstUserController@getadmin', 'middleware' => 'check_perm:ad3200']);
            $router->post('/inst-user/show', ['uses' => 'Back\InstUserController@show', 'middleware' => 'check_perm:se2400']);
            $router->post('/inst-user/showadmin', ['uses' => 'Back\InstUserController@showadmin', 'middleware' => 'check_perm:ad3200']);
            $router->post('/inst-user/create', ['uses' => 'Back\InstUserController@store', 'middleware' => 'check_perm:se2420']);
            $router->post('/inst-user/createadmin', ['uses' => 'Back\InstUserController@storeAdmin', 'middleware' => 'check_perm:ad3220']);
            $router->post('/inst-user/update', ['uses' => 'Back\InstUserController@update', 'middleware' => 'check_perm:se2430']);
            $router->post('/inst-user/updateadmin', ['uses' => 'Back\InstUserController@updateAdmin', 'middleware' => 'check_perm:ad3230']);
            $router->post('/inst-user/delete', ['uses' => 'Back\InstUserController@destroy', 'middleware' => 'check_perm:se2440']);
            $router->post('/inst-user/deleteadmin', ['uses' => 'Back\InstUserController@destroyAdmin', 'middleware' => 'check_perm:ad3240']);
            $router->post('/inst-user/profile', ['uses' => 'Back\InstUserController@getProfileFront', 'middleware' => 'check_perm:']);
            // USER API - End

            // Institution
            $router->post('/inst/get', ['uses' => 'Back\InstController@index', 'middleware' => 'check_perm:ad3000']);
            $router->post('/inst/show', ['uses' => 'Back\InstController@show', 'middleware' => 'check_perm:ad3000']);
            $router->post('/inst/store', ['uses' => 'Back\InstController@store', 'middleware' => 'check_perm:ad3020']);
            $router->post('/inst/update', ['uses' => 'Back\InstController@update', 'middleware' => 'check_perm:ad3030']);
            $router->post('/inst/delete', ['uses' => 'Back\InstController@destroy', 'middleware' => 'check_perm:ad3040']);
            $router->post('/inst/getPerms', ['uses' => 'Back\InstPermController@getPerms', 'middleware' => 'check_perm:se2600']);
            $router->post('/inst/setPerms', ['uses' => 'Back\InstPermController@setPerms', 'middleware' => 'check_perm:se2620.se2640']);

            // ROLE API - Begin
            $router->post('/role/get', ['uses' => 'Back\InstRoleController@index', 'middleware' => 'check_perm:se2500']);
            $router->post('/role/show', ['uses' => 'Back\InstRoleController@show', 'middleware' => 'check_perm:se2500']);
            $router->post('/role/store', ['uses' => 'Back\InstRoleController@store', 'middleware' => 'check_perm:se2520']);
            $router->post('/role/update', ['uses' => 'Back\InstRoleController@update', 'middleware' => 'check_perm:se2530']);
            $router->post('/role/delete', ['uses' => 'Back\InstRoleController@destroy', 'middleware' => 'check_perm:se2540']);

            $router->post('/admin-role/get', ['uses' => 'Back\InstRoleController@indexAdmin', 'middleware' => 'check_perm:ad3300']);
            $router->post('/admin-role/show', ['uses' => 'Back\InstRoleController@showAdmin', 'middleware' => 'check_perm:ad3300']);
            $router->post('/admin-role/store', ['uses' => 'Back\InstRoleController@storeAdmin', 'middleware' => 'check_perm:ad3320']);
            $router->post('/admin-role/update', ['uses' => 'Back\InstRoleController@updateAdmin', 'middleware' => 'check_perm:ad3330']);
            $router->post('/admin-role/delete', ['uses' => 'Back\InstRoleController@destroyAdmin', 'middleware' => 'check_perm:ad3340']);

            $router->post('/cust-role/get', ['uses' => 'Back\CustRoleController@index', 'middleware' => 'check_perm:cu0300']);
            $router->post('/cust-role/show', ['uses' => 'Back\CustRoleController@show', 'middleware' => 'check_perm:cu0300']);
            $router->post('/cust-role/store', ['uses' => 'Back\CustRoleController@store', 'middleware' => 'check_perm:cu0320']);
            $router->post('/cust-role/update', ['uses' => 'Back\CustRoleController@update', 'middleware' => 'check_perm:cu0330']);
            $router->post('/cust-role/delete', ['uses' => 'Back\CustRoleController@destroy', 'middleware' => 'check_perm:cu0340']);
            // ROLE API - End

            // DIC API - Begin
            $router->post('/dic/get', ['uses' => 'DicController@get', 'middleware' => 'check_perm:se2000']);
            $router->post('/dic/add', ['uses' => 'DicController@add', 'middleware' => 'check_perm:se2020']);
            $router->post('/dic/show', ['uses' => 'DicController@getDic', 'middleware' => 'check_perm:se2010']);
            $router->post('/dic/showbyparent', ['uses' => 'DicController@getDicByParent', 'middleware' => 'check_perm:']);
            $router->post('/dic/getDicWithChildren', ['uses' => 'DicController@getDicWithChildren', 'middleware' => 'check_perm:']);
            $router->post('/dic/delete', ['uses' => 'DicController@delete', 'middleware' => 'check_perm:se2040']);

            $router->post('/dic/setItem', ['uses' => 'DicController@setItem', 'middleware' => 'check_perm:se2030']);
            $router->post('/dic/addItem', ['uses' => 'DicController@addItem', 'middleware' => 'check_perm:se2020']);
            $router->post('/dic/deleteItem', ['uses' => 'DicController@deleteItem', 'middleware' => 'check_perm:se2040']);

            // INST CONTACT API - Begin
            $router->post('/inst/contact/get', ['uses' =>  'Back\InstContactController@index', 'middleware' => 'check_perm:ad3100']);
            $router->post('/inst/contact/show', ['uses' =>  'Back\InstContactController@show', 'middleware' => 'check_perm:ad3100']);
            $router->post('/inst/contact/store', ['uses' =>  'Back\InstContactController@store', 'middleware' => 'check_perm:ad3120']);
            $router->post('/inst/contact/update', ['uses' =>  'Back\InstContactController@update', 'middleware' => 'check_perm:ad3130']);
            $router->post('/inst/contact/delete', ['uses' =>  'Back\InstContactController@delete', 'middleware' => 'check_perm:ad3140']);

            // CUST ACCOUNTS
            $router->post('/inst/account/transaction', ['uses' =>  'Back\AccountController@getTransaction', 'middleware' => 'check_perm:cu0404']);
            $router->post('/inst/account/get-loan', ['uses' =>  'Back\AccountController@getLoanAcntList', 'middleware' => 'check_perm:cu0402']);
            $router->post('/inst/account/get-casa', ['uses' =>  'Back\AccountController@getCasaAcntList', 'middleware' => 'check_perm:cu0401']);
            $router->post('/inst/account/get-td', ['uses' =>  'Back\AccountController@getTdAcntList', 'middleware' => 'check_perm:cu0400']);
            $router->post('/inst/account/get-cca', ['uses' =>  'Back\AccountController@getCcaAcntList', 'middleware' => 'check_perm:cu0403']);
            $router->post('/inst/account/get-detail', ['uses' =>  'Back\AccountController@getAccountDetail', 'middleware' => 'check_perm:cu0410.cu0411.cu0412.cu0413']);

            // COMMON ROLE
            // $router->post('/commonrole/get', ['uses' => 'Back\CommonRoleController@index', 'middleware' => 'check_perm:']);
            // $router->post('/commonrole/show', ['uses' => 'Back\CommonRoleController@show', 'middleware' => 'check_perm:']);
            // $router->post('/commonrole/store', ['uses' => 'Back\CommonRoleController@store', 'middleware' => 'check_perm:']);
            // $router->post('/commonrole/update', ['uses' => 'Back\CommonRoleController@update', 'middleware' => 'check_perm:']);
            // $router->post('/commonrole/delete', ['uses' => 'Back\CommonRoleController@destroy', 'middleware' => 'check_perm:']);

            $router->post('/conf/conn/get', ['uses' => 'Back\ConnConfController@index', 'middleware' => 'check_perm:ad3500.ad3510']);
            $router->post('/conf/conn/store', ['uses' => 'Back\ConnConfController@store', 'middleware' => 'check_perm:ad3520']);
            $router->post('/conf/conn/update', ['uses' => 'Back\ConnConfController@update', 'middleware' => 'check_perm:ad3530']);
            $router->post('/conf/conn/delete', ['uses' => 'Back\ConnConfController@delete', 'middleware' => 'check_perm:ad3540']);

            $router->post('/conf/sys/get', ['uses' => 'Back\SysConfController@index', 'middleware' => 'check_perm:ad3400.ad3410']);
            $router->post('/conf/sys/store', ['uses' => 'Back\SysConfController@store', 'middleware' => 'check_perm:ad3420']);
            $router->post('/conf/sys/update', ['uses' => 'Back\SysConfController@update', 'middleware' => 'check_perm:ad3430']);
            $router->post('/conf/sys/delete', ['uses' => 'Back\SysConfController@delete', 'middleware' => 'check_perm:ad3440']);

            $router->post('/conf/provider/get', ['uses' => 'Back\ProviderParamController@index', 'middleware' => 'check_perm:se2300.se2310']);
            $router->post('/conf/provider/store', ['uses' => 'Back\ProviderParamController@store', 'middleware' => 'check_perm:se2320']);
            $router->post('/conf/provider/update', ['uses' => 'Back\ProviderParamController@update', 'middleware' => 'check_perm:se2330']);
            $router->post('/conf/provider/delete', ['uses' => 'Back\ProviderParamController@delete', 'middleware' => 'check_perm:se2340']);

            $router->post('/conf/corrsys/get', ['uses' => 'Back\CorrSysController@index', 'middleware' => 'check_perm:se2100.se2110']);
            $router->post('/conf/corrsys/store', ['uses' => 'Back\CorrSysController@store', 'middleware' => 'check_perm:se2120']);
            $router->post('/conf/corrsys/update', ['uses' => 'Back\CorrSysController@update', 'middleware' => 'check_perm:se2130']);
            $router->post('/conf/corrsys/delete', ['uses' => 'Back\CorrSysController@delete', 'middleware' => 'check_perm:se2140']);

            $router->post('/conf/banksys/get', ['uses' => 'Back\BankSysController@index', 'middleware' => 'check_perm:se2200.se2210']);
            $router->post('/conf/banksys/store', ['uses' => 'Back\BankSysController@store', 'middleware' => 'check_perm:se2220']);
            $router->post('/conf/banksys/update', ['uses' => 'Back\BankSysController@update', 'middleware' => 'check_perm:se2230']);
            $router->post('/conf/banksys/delete', ['uses' => 'Back\BankSysController@delete', 'middleware' => 'check_perm:se2240']);

            // Notification
            $router->post('/send-notification', ['uses' => 'Back\NotificationController@sendNotification', 'middleware' => 'check_perm:cu0520']);
            $router->post('/get-send-notification', ['uses' => 'Back\NotificationController@getSendNotification', 'middleware' => 'check_perm:cu0500']);
            $router->post('/get-detail-notification', ['uses' => 'Back\NotificationController@getDetailNotification', 'middleware' => 'check_perm:cu0510']);
            $router->post('/get-sent-notif-users', ['uses' => 'Back\NotificationController@getSentNotifUser', 'middleware' => 'check_perm:cu0510']);

            // Contract
            $router->post('/contract/get', ['uses' => 'Back\ContactController@index', 'middleware' => 'check_perm:se2700']);
            $router->post('/contract/show', ['uses' => 'Back\ContactController@show', 'middleware' => 'check_perm:se2710']);
            $router->post('/contract/store', ['uses' => 'Back\ContactController@store', 'middleware' => 'check_perm:se2720']);
            $router->post('/contract/update', ['uses' => 'Back\ContactController@update', 'middleware' => 'check_perm:se2730']);
            $router->post('/contract/delete', ['uses' => 'Back\ContactController@delete', 'middleware' => 'check_perm:se2740']);

            // Зээлийн гүйлгээний лавлагаа авах
            $router->post('/loan-transaction/get', ['uses' => 'Back\LoanTranController@index', 'middleware' => 'check_perm:cu0405']);
            $router->post('/loan-transaction/show', ['uses' => 'Back\LoanTranController@show', 'middleware' => 'check_perm:cu0415']);

        });
    });
});
