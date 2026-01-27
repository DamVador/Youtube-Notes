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
        // Ne pas activer en production
        if (app()->environment('production')) {
            return $next($request);
        }

        // Laisser passer les webhooks Stripe
        if ($request->is('stripe/*') || $request->is('webhook/*')) {
            return $next($request);
        }

        // Laisser passer les requêtes API (pour YouTube iframe, etc.)
        if ($request->expectsJson() || $request->is('api/*')) {
            return $next($request);
        }

        // Laisser passer les assets
        if ($request->is('build/*') || $request->is('storage/*') || $request->is('favicon.ico')) {
            return $next($request);
        }

        // Laisser passer certains User-Agents (bots légitimes pour les tests)
        $userAgent = $request->userAgent() ?? '';
        $allowedBots = ['GoogleBot', 'YouTube', 'Stripe'];
        foreach ($allowedBots as $bot) {
            if (stripos($userAgent, $bot) !== false) {
                return $next($request);
            }
        }

        // Vérifier si déjà authentifié via session
        if ($request->session()->get('preprod_authenticated')) {
            return $next($request);
        }

        // Vérifier le mot de passe soumis
        if ($request->isMethod('post') && $request->input('preprod_password') === config('app.preprod_password')) {
            $request->session()->put('preprod_authenticated', true);
            return redirect()->intended('/');
        }

        // Afficher le formulaire de mot de passe
        return response(view('auth.preprod-password'), 401);
    }
}