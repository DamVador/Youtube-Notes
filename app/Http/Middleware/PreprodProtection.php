<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreprodProtection
{
    /**
     * Protège la preprod avec un mot de passe simple
     * sans bloquer les requêtes API externes (YouTube, Stripe webhooks, etc.)
     */
    public function handle(Request $request, Closure $next): Response
{
    // Ne pas activer en production et local
    if (app()->environment('production', 'local')) {
        return $next($request);
    }

    // Laisser passer les webhooks et API
    if ($request->is('stripe/*') || $request->is('webhook/*') || $request->is('api/*') || $request->is('preprod-auth')) {
        return $next($request);
    }

    // Laisser passer les assets
    if ($request->is('build/*') || $request->is('storage/*') || $request->is('favicon.ico')) {
        return $next($request);
    }

    // Vérifier si déjà authentifié via session
    if ($request->session()->get('preprod_authenticated')) {
        return $next($request);
    }

    // Afficher le formulaire de mot de passe
    return response(view('auth.preprod-password'), 401);
}
}