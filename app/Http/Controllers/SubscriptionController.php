<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class SubscriptionController extends Controller
{
    public function pricing()
    {
        $user = auth()->user();
        
        return Inertia::render('Subscription/Pricing', [
            'isSubscribed' => $user ? $user->subscribed('premium') : false,
            'currentPlan' => $user && $user->subscribed('premium') 
                ? ($user->subscription('premium')->stripe_price === config('services.stripe.yearly_price_id') ? 'yearly' : 'monthly')
                : null,
            'prices' => [
                'monthly' => [
                    'id' => config('services.stripe.monthly_price_id'),
                    'amount' => 5.99,
                    'interval' => 'month',
                ],
                'yearly' => [
                    'id' => config('services.stripe.yearly_price_id'),
                    'amount' => 49.99,
                    'interval' => 'year',
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

        $checkout = $user->newSubscription('premium', $request->price_id)
            ->allowPromotionCodes()
            ->checkout([
                'success_url' => route('subscription.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('subscription.pricing'),
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
        $url = $request->user()->billingPortalUrl(route('subscription.pricing'));
        
        return Inertia::location($url);
    }
}