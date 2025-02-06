<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Payment;
use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\PaymentIntent;

class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Handle the event
        switch ($event->type) {
            case 'checkout.session.completed':
                $session = $event->data->object;
                $payment = Payment::where('transaction_id', $session->payment_intent)->first();

                if ($payment && $session->payment_status === 'paid') {
                    // Update the payment status to "completed"
                    $payment->update([
                        'status' => 'completed',
                        'transaction_id' => $session->payment_intent,
                        'payment_method' => $session->payment_method_types[0], // e.g., card
                    ]);

                    // Update the order status to "completed"
                    $payment->order->update(['status' => 'completed']);
                }
                break;
        }

        return response()->json(['success' => true]);
    }
}