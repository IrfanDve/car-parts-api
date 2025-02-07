<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\PaymentIntent;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        $endpointSecret = config('services.stripe.webhook_secret');
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
            
            Log::info('Stripe webhook received', [
                'event_type' => $event->type,
                'event_id' => $event->id
            ]);

        } catch (\UnexpectedValueException $e) {
            
            Log::error('Stripe webhook invalid payload', ['error' => $e->getMessage()]);
            return $this->errorResponse('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error('Stripe webhook invalid signature', ['error' => $e->getMessage()]);
            return $this->errorResponse('Invalid signature', 400);
        } catch (\Exception $e) {
            Log::error('Stripe webhook processing error', ['error' => $e->getMessage()]);
            return $this->errorResponse('Internal server error', 500);
        }

        return $this->handleWebhookEvent($event);
    }

    protected function handleWebhookEvent($event)
    {
        try {
            switch ($event->type) {
                case 'checkout.session.completed':
                    return $this->handleCheckoutSessionCompleted($event->data->object);
                
                // Add more event handlers as needed
                // case 'payment_intent.succeeded':
                //     return $this->handlePaymentIntentSucceeded($event->data->object);
                
                default:
                    Log::info('Unhandled Stripe webhook event', ['event_type' => $event->type]);
                    return $this->successResponse();
            }
        } catch (\Exception $e) {
            Log::error('Webhook event handling failed', [
                'event_type' => $event->type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse('Event handling failed', 500);
        }
    }

    protected function handleCheckoutSessionCompleted($session)
    {
        if (empty($session->payment_intent)) {
            Log::error('Missing payment intent in checkout session', ['session_id' => $session->id]);
            return $this->errorResponse('Missing payment intent', 400);
        }

        try {
            $payment = Payment::where('transaction_id', $session->payment_intent)->firstOrFail();
            
            if ($session->payment_status === 'paid') {
                $this->updatePaymentDetails($payment, $session);
                $this->updateOrderStatus($payment);
                
                Log::info('Payment successfully processed', [
                    'payment_id' => $payment->id,
                    'order_id' => $payment->order_id
                ]);
            }

            return $this->successResponse();

        } catch (ModelNotFoundException $e) {
            Log::error('Payment record not found', [
                'payment_intent' => $session->payment_intent,
                'session_id' => $session->id
            ]);
            return $this->errorResponse('Payment record not found', 404);
        }
    }

    protected function updatePaymentDetails(Payment $payment, $session)
    {
        // Retrieve additional payment details from Stripe
        $paymentIntent = PaymentIntent::retrieve($session->payment_intent);
        
        $payment->update([
            'status' => 'completed',
            'payment_method' => $paymentIntent->payment_method_types[0],
            'metadata' => [
                'stripe_customer_id' => $session->customer,
                'payment_method_details' => $paymentIntent->charges->data[0]->payment_method_details ?? null
            ]
        ]);
    }

    protected function updateOrderStatus(Payment $payment)
    {
        if ($payment->order) {
            $payment->order->update(['status' => 'completed']);
        }
    }

    protected function successResponse()
    {
        return response()->json([
            'status' => true,
            'message' => 'Webhook handled successfully'
        ]);
    }

    protected function errorResponse($message, $statusCode)
    {
        return response()->json([
            'status' => false,
            'message' => $message
        ], $statusCode);
    }
}