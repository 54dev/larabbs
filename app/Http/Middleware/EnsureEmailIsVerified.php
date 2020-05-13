<?php

namespace App\Http\Middleware;

use Closure;

class EnsureEmailIsVerified
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
        // 1.if user login
        // 2.and no verify email
        // 3.and visit not email verify or logout url
        if($request->user() &&
            ! $request->user()->hasVerifiedEmail() &&
            ! $request->is('email/*', 'logout')){

            return $request->expectsJson()
                    ? abort(403, 'Your Email address isn\'t verified')
                    : redirect()->route('verification.notice');
        }
        return $next($request);
    }
}
