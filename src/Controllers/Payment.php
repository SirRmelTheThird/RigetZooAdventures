<?php

namespace Controllers;

use Core\Request;
use Core\Response;
use Core\Session;
use Services\BookingService;
use Services\PaymentService;
use Config\Config;
use Core\Logger;

class Payment extends Controller
{
    private $bookingService;
    private $paymentService;

    public function __construct()
    {
        $this->bookingService = new BookingService();

        try {
            $stripeSecret = Config::get('STRIPE_SECRET_KEY');
            if (!empty($stripeSecret)) {
                $this->paymentService = new PaymentService();
            } else {
                $this->paymentService = null;
            }
        } catch (\Exception $e) {
            Logger::error('PaymentService initialization failed', ['error' => $e->getMessage()]);
            $this->paymentService = null;
        }
    }

    public function checkout()
    {
        if (!Session::isLoggedIn()) {
            Session::flash('error', 'Please log in to checkout');
            Response::redirect('/login');
        }

        $cart = Session::get('cart', ['items' => [], 'total' => 0]);

        if (empty($cart['items'])) {
            Session::flash('error', 'Your cart is empty');
            Response::redirect('/cart');
        }

        // Create Payment Intent
        $clientSecret = null;
        $error = null;

        if ($this->paymentService) {
            try {
                $paymentIntent = $this->paymentService->createPaymentIntent(
                    $cart['total'],
                    'usd',
                    [
                        'customer_id' => Session::getUserId(),
                        'order_type' => 'zoo_booking'
                    ]
                );

                $clientSecret = $paymentIntent->client_secret;

                // Store payment intent ID in session for later verification
                Session::set('payment_intent_id', $paymentIntent->id);

            } catch (\Exception $e) {
                Logger::exception($e, ['customer_id' => Session::getUserId()]);
                $error = 'Unable to initialize payment. Please try again.';
            }
        } else {
            $error = 'Payment system is not configured. Please check your environment settings.';
        }

        $stripePublishableKey = Config::get('STRIPE_PUBLISHABLE_KEY');

        Response::view('checkout', compact('cart', 'clientSecret', 'stripePublishableKey', 'error'));
    }

    public function process()
    {
        if (!Session::isLoggedIn()) {
            if (Request::isAjax()) {
                Response::json(['error' => 'Not authenticated'], 401);
            }
            Session::flash('error', 'Please log in to checkout');
            Response::redirect('/login');
        }

        $cart = Session::get('cart', ['items' => [], 'total' => 0]);

        if (empty($cart['items'])) {
            if (Request::isAjax()) {
                Response::json(['error' => 'Cart is empty'], 400);
            }
            Session::flash('error', 'Your cart is empty');
            Response::redirect('/cart');
        }

        $paymentIntentId = $_POST['payment_intent_id'] ?? Session::get('payment_intent_id');

        if (!$paymentIntentId) {
            if (Request::isAjax()) {
                Response::json(['error' => 'Payment intent not found'], 400);
            }
            Session::flash('error', 'Payment intent not found');
            Response::redirect('/checkout');
        }

        if (!$this->paymentService) {
            if (Request::isAjax()) {
                Response::json(['error' => 'Payment service not configured'], 500);
            }
            Session::flash('error', 'Payment service not configured');
            Response::redirect('/checkout');
        }

        try {
            // Verify payment was successful
            $paymentSuccessful = $this->paymentService->confirmPayment($paymentIntentId);

            if (!$paymentSuccessful) {
                if (Request::isAjax()) {
                    Response::json(['error' => 'Payment was not successful'], 402);
                }
                Session::flash('error', 'Payment was not successful. Please try again.');
                Response::redirect('/checkout');
            }

            $customerId = Session::getUserId();

            // Create order with transaction support
            $orderId = $this->bookingService->createOrder($customerId, $cart);

            if (!$orderId) {
                throw new \Exception('Failed to create order');
            }

            // Update order with Stripe payment ID
            $order = \Models\Order::find($orderId);
            $order->markAsPaid($paymentIntentId);

            // Clear cart and payment intent from session
            Session::remove('cart');
            Session::remove('payment_intent_id');

            Logger::info('Payment processed successfully', [
                'order_id' => $orderId,
                'customer_id' => $customerId,
                'payment_intent_id' => $paymentIntentId,
                'amount' => $cart['total']
            ]);

            Session::flash('success', 'Order #' . $orderId . ' placed successfully! Thank you for your booking.');

            if (Request::isAjax()) {
                Response::json([
                    'success' => true,
                    'order_id' => $orderId,
                    'redirect' => '/profile'
                ]);
            }

            Response::redirect('/profile');

        } catch (\Exception $e) {
            Logger::exception($e, [
                'customer_id' => Session::getUserId(),
                'payment_intent_id' => $paymentIntentId,
                'cart_total' => $cart['total'] ?? 0
            ]);

            if (Request::isAjax()) {
                Response::json(['error' => 'Payment processing failed. Please contact support.'], 500);
            }

            Session::flash('error', 'Payment processing failed. Please contact support.');
            Response::redirect('/checkout');
        }
    }

    public function webhook()
    {
        $payload = @file_get_contents('php://input');
        $signature = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

        if (!$this->paymentService) {
            http_response_code(500);
            exit;
        }

        try {
            $event = $this->paymentService->handleWebhook($payload, $signature);

            if (!$event) {
                http_response_code(400);
                exit;
            }

            // Handle different event types
            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $paymentIntent = $event->data->object;
                    Logger::info('Webhook: Payment succeeded', [
                        'payment_intent_id' => $paymentIntent->id,
                        'amount' => $paymentIntent->amount / 100
                    ]);
                    break;

                case 'payment_intent.payment_failed':
                    $paymentIntent = $event->data->object;
                    Logger::warning('Webhook: Payment failed', [
                        'payment_intent_id' => $paymentIntent->id,
                        'error' => $paymentIntent->last_payment_error->message ?? 'Unknown error'
                    ]);
                    break;

                default:
                    Logger::info('Webhook: Unhandled event type', ['type' => $event->type]);
            }

            http_response_code(200);
            exit;

        } catch (\Exception $e) {
            Logger::exception($e);
            http_response_code(400);
            exit;
        }
    }
}