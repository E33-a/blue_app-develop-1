<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;

class StripeController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));
    }

    public function createCheckoutSession(Request $request)
    {
        try {
            $session = Session::create([
                'ui_mode' => 'custom',
                'customer_email' => $request->email, // Si tienes el email del cliente
                'line_items' => [
                    [
                        'price' => $request->priceId,
                        'quantity' => $request->quantity,
                    ],
                ],
                'mode' => 'payment',
                'return_url' => env('APP_URL') . '/complete?session_id={CHECKOUT_SESSION_ID}',
                'automatic_tax' => ['enabled' => true], // Habilita el cálculo automático de impuestos si lo necesitas
            ]);

            return response()->json(['clientSecret' => $session->client_secret]);
        } catch (ApiErrorException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function retrieveSession(Request $request)
    {
        try {
            $session = Session::retrieve($request->session_id, ['expand' => ['payment_intent']]);

            return response()->json([
                'status' => $session->status,
                'payment_status' => $session->payment_status,
                'payment_intent_id' => $session->payment_intent->id,
                'payment_intent_status' => $session->payment_intent->status
            ]);
        } catch (ApiErrorException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );

            // Manejar el evento
            switch ($event->type) {
                case 'checkout.session.completed':
                    $session = $event->data->object;
                    // Actualizar el estado del pedido en tu base de datos
                    break;
                case 'payment_intent.succeeded':
                    $paymentIntent = $event->data->object;
                    // Procesar el pago exitoso
                    break;
                // Añade más casos según sea necesario
            }

            return response()->json(['status' => 'success']);
        } catch (\UnexpectedValueException $e) {
            return response()->json(['error' => 'Webhook error: ' . $e->getMessage()], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }
    }
}
