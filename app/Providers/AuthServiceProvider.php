<?php

namespace App\Providers;

use App\Models\UserAccessToken;
use App\Services\DicPassPolicyService;
use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        $this->app['auth']->viaRequest('api', function ($request) {
            $token = $request->bearerToken();
            if ($token) {
                $passpolicy = new DicPassPolicyService();
                // $limit = env('WRONG_PASS_COUNT', 5);
                $token_lifetime = (int) $passpolicy->getPolicyValue("TOKEN_LIFETIME");
                if ($request->is('back-api/*')) {
                    $uat = UserAccessToken::selectRaw('user_access_tokens.*, case when (sysdate - last_used_at) * 24 * 60 > ' . $token_lifetime . ' then 1 else 0 end expired ')
                        ->where('token', $token)->where('channel', 'WEB')->first();
                    if ($uat && $uat->expired == 0) {
                        $uat->last_used_at = Carbon::now();
                        $uat->save();
                        return $uat->backUser;
                    }
                } else {
                    $uat = UserAccessToken::selectRaw('user_access_tokens.*, case when (sysdate - last_used_at) * 24 * 60 > ' . $token_lifetime . ' then 1 else 0 end expired ')
                        ->where('token', $token)->where('channel', 'APP')->first();

                    if ($uat && $uat->expired == 0) {
                        $uat->last_used_at = Carbon::now();
                        $uat->save();
                        return $uat->user;
                    }
                }
            }
            return null;
        });
    }
}
