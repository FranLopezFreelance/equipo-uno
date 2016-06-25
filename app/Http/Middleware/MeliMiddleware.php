<?php

namespace App\Http\Middleware;

use Closure;

class MeliMiddleware
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
        if (!session('access_token')) {
            return redirect('login');
        }

        return $next($request);
    }
}
