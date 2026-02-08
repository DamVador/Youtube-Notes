<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRegistrationEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('app.registration_enabled')) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Registration is temporarily disabled. Please try again later.'
                ], 403);
            }
            
            return redirect()->route('login')
                ->with('error', 'Registration is temporarily disabled. Please try again later.');
        }

        return $next($request);
    }
}