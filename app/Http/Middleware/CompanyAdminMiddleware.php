<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompanyAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Admins only');
        }

        return $next($request);
    }
}
