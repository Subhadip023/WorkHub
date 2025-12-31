<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureCompanyIsSet
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::user()->company_id) {
            return redirect()->route('company.create');
        }

        return $next($request);
    }
}
