<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;

class WebhookController extends CashierController
{
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