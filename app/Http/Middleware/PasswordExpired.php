<?php
namespace App\Http\Middleware;

use App\Services\DicPassPolicyService;
use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Log;

class PasswordExpired
{

    public function handle($request, Closure $next)
    {
        $user = auth()->user();
        if ($user->mustchgpass == '1') {
            return response('Нууц үг солих шаардлагатай.', 303);
        }
        $passpolicy = new DicPassPolicyService();
        $expiryDay = (int) $passpolicy->getPolicyValue("ExpirePassDay");
        $password_changed_at = new Carbon(($user->password_changed_at) ? $user->password_changed_at : $user->createdate);
        // Log::info(Carbon::now()->diffInDays($password_changed_at) . " " . $user->password_changed_at);
        if (Carbon::now()->diffInDays($password_changed_at) >= $expiryDay) {
            return response('Нууц үгийн хүчинтэй хугацаа дууссан байна.', 303);
        }

        return $next($request);
    }
}
