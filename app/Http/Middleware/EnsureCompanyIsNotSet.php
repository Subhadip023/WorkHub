<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureCompanyIsNotSet
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::user()->company_id) {
            return redirect()->route('projects.index');
        }

        return $next($request);
    }
}
