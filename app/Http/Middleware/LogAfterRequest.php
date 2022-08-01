<?php
namespace App\Http\Middleware;

use App\AuditResolvers\IpAddressResolver;
use App\Models\LogRequest;
use Closure;
use Exception;

class LogAfterRequest
{

    public function handle($request, Closure $next)
    {
        $method = $request->getMethod();

        $user = auth()->user();
        $r = new LogRequest();
        if ($user) {
            $r->userid = $user->userid;
        }
        $r->ip = IpAddressResolver::resolve();
        $r->url = $request->fullUrl();
        $r->method = $method;
        $r->save();


        if ($method === "OPTIONS") {
            return response('');
        }
        $response = $next($request);
        try{

            $url = $request->fullUrl();

            if (str_ends_with($url, "/admin/accesslog/get")) return $response;
            if (str_ends_with($url, "/admin/accesslog/show")) return $response;
            if (str_ends_with($url, "/admin/changelog/get")) return $response;
            if (str_ends_with($url, "/admin/changelog/show")) return $response;

            $user = auth()->user();
            if ($user) {
                $r->userid = $user->userid;
            }
            $r->ip = IpAddressResolver::resolve();
            $r->url = $request->fullUrl();
            $r->method = $method;
            $data = $request->all();
            if (array_key_exists('password', $data)) $data['password'] = "******";
            $r->request = json_encode($data, JSON_UNESCAPED_UNICODE);
            $r->responseCode = @$response->status();
            $content = @$response->content();
            $decode = @json_decode($content);
            if ($decode) {
                $content = @json_encode($decode, JSON_UNESCAPED_UNICODE);
            }
            $r->response = $content;
            $r->responseTime = microtime(true) - LUMEN_START;
            $r->save();

        } catch (Exception $e) {

        }
        return $response;
    }

    public function terminate($request, $response)
    {
    }
}
