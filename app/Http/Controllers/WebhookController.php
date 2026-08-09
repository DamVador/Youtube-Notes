<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;

class WebhookController extends CashierController
{
    /**
     * Handle a completed checkout session (one-time "lifetime" payment).
     */
    protected function handleCheckoutSessionCompleted(array $payload): \Symfony\Component\HttpFoundation\Response
    {
        $session = $payload['data']['object'];

        $isLifetime = ($session['mode'] ?? null) === 'payment'
            && ($session['metadata']['purchase_type'] ?? null) === 'lifetime'
            && ($session['payment_status'] ?? null) === 'paid';

        if ($isLifetime && ! empty($session['customer'])) {
            $user = Cashier::findBillable($session['customer']);

            if ($user && ! $user->lifetime_access) {
                $user->forceFill(['lifetime_access' => true])->save();
                \Log::info('Lifetime access granted', ['user_id' => $user->id]);
            }
        }

        return $this->successMethod();
    }

    /**
     * Handle customer subscription created.
     */
    protected function handleCustomerSubscriptionCreated(array $payload): \Symfony\Component\HttpFoundation\Response
    {
        // Log or handle new subscription
        \Log::info('New subscription created', ['payload' => $payload]);
        
        return parent::handleCustomerSubscriptionCreated($payload);
    }

    /**
     * Handle customer subscription deleted.
     */
    protected function handleCustomerSubscriptionDeleted(array $payload): \Symfony\Component\HttpFoundation\Response
    {
        // Log or handle subscription cancellation
        \Log::info('Subscription cancelled', ['payload' => $payload]);
        
        return parent::handleCustomerSubscriptionDeleted($payload);
    }
}