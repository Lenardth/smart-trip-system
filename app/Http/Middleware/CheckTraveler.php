<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTraveler
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->isAgency()) {
            return redirect()->route('dashboard')
                ->with('error', 'This feature is only available for travelers.');
        }

        return $next($request);
    }
}
