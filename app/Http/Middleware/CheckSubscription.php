<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()?->subscribed('premium')) {
            return redirect()->route('subscription.pricing')
                ->with('error', 'You need a premium subscription to access this feature.');
        }

        return $next($request);
    }
}