<?php

namespace Grechanyuk\Comepay\Middleware;

use Closure;
use Grechanyuk\Comepay\Facades\Comepay;
use Illuminate\Support\Facades\Request;

class ComepayNotificateStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(Comepay::checkAuthorization(Request::header('Authorization'))) {
            return $next($request);
        }

        return abort(403);
    }
}
