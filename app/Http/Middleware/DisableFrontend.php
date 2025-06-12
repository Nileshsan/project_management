<?php

namespace App\Http\Middleware;

use Closure;

class DisableFrontend
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next) 
    {
        $global = global_setting();

        $allowedRoutes = ['front.signup.index', 'front.home'];

        if (
            $global?->frontend_disable &&
            !in_array(request()->route()->getName(), $allowedRoutes) &&
            !request()->ajax()
        ) {
            return redirect(route('login'));
        }

        return $next($request);
    }
}
