<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class SubscriptionController extends Controller
{
    public function pricing()
    {
        $user = auth()->user();
        
        $currentPlan = null;
        if ($user) {
            if ($user->lifetime_access) {
                $currentPlan = 'lifetime';
            } elseif ($user->subscribed('premium')) {
                $currentPlan = 'monthly';
            }
        }

        return Inertia::render('Subscription/Pricing', [
            'isSubscribed' => $user ? $user->isPremium() : false,
            'currentPlan' => $currentPlan,
            'prices' => [
                'monthly' => [
                    'id' => config('services.stripe.monthly_price_id'),
                    'amount' => 5.99,
                    'interval' => 'month',
                ],
                'lifetime' => [
                    'id' => config('services.stripe.lifetime_price_id'),
                    'amount' => 29,
                    'interval' => 'lifetime',
                ],
            ],
        ]);
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'price_id' => 'required|string',
        ]);

        $user = $request->user();

        $successUrl = route('subscription.success') . '?session_id={CHECKOUT_SESSION_ID}';
        $cancelUrl = route('subscription.pricing');

        // Lifetime = one-time payment (no subscription in Stripe/Cashier)
        if ($request->price_id === config('services.stripe.lifetime_price_id')) {
            $checkout = $user->checkout([$request->price_id => 1], [
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'allow_promotion_codes' => true,
                'metadata' => [
                    'purchase_type' => 'lifetime',
                ],
            ]);

            return response()->json([
                'checkout_url' => $checkout->url,
            ]);
        }

        // Monthly = recurring subscription
        $checkout = $user->newSubscription('premium', $request->price_id)
            ->allowPromotionCodes()
            ->checkout([
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
            ]);

        return response()->json([
            'checkout_url' => $checkout->url,
        ]);
    }

    public function success(Request $request)
    {
        return Inertia::render('Subscription/Success');
    }

    public function billingPortal(Request $request)
    {
        $user = $request->user();

        // Lifetime users have no Stripe subscription to manage
        if ($user->lifetime_access && ! $user->subscribed('premium')) {
            return Inertia::render('Subscription/Manage');
        }

        $url = $user->billingPortalUrl(route('subscription.pricing'));

        return Inertia::location($url);
    }
}