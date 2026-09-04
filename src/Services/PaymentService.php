<?php

namespace Services;

use Config\Config;
use Core\Logger;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Webhook;

class PaymentService
{
    private $stripeKey;

    public function __construct()
    {
        $this->stripeKey = Config::get('STRIPE_SECRET_KEY');

        if (!$this->stripeKey) {
            throw new \Exception('Stripe secret key not configured');
        }

        Stripe::setApiKey($this->stripeKey);
    }

    /**
     * Create a Stripe Payment Intent
     *
     * @param float $amount Amount in dollars
     * @param string $currency Currency code (default: usd)
     * @param array $metadata Additional metadata
     * @return PaymentIntent
     */
    public function createPaymentIntent($amount, $currency = 'usd', $metadata = [])
    {
        try {
            // Convert dollars to cents for Stripe
            $amountInCents = intval($amount * 100);

            $paymentIntent = PaymentIntent::create([
                'amount' => $amountInCents,
                'currency' => $currency,
                'metadata' => $metadata,
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            Logger::info('Payment Intent created', [
                'payment_intent_id' => $paymentIntent->id,
                'amount' => $amount,
                'currency' => $currency
            ]);

            return $paymentIntent;

        } catch (\Stripe\Exception\ApiErrorException $e) {
            Logger::error('Stripe Payment Intent creation failed', [
                'error' => $e->getMessage(),
                'amount' => $amount
            ]);
            throw new \Exception('Failed to create payment intent: ' . $e->getMessage());
        }
    }

    /**
     * Retrieve a Payment Intent
     *
     * @param string $paymentIntentId
     * @return PaymentIntent
     */
    public function retrievePaymentIntent($paymentIntentId)
    {
        try {
            return PaymentIntent::retrieve($paymentIntentId);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            Logger::error('Failed to retrieve Payment Intent', [
                'payment_intent_id' => $paymentIntentId,
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Failed to retrieve payment intent');
        }
    }

    /**
     * Confirm a payment was successful
     *
     * @param string $paymentIntentId
     * @return bool
     */
    public function confirmPayment($paymentIntentId)
    {
        try {
            $paymentIntent = $this->retrievePaymentIntent($paymentIntentId);

            $isSuccessful = $paymentIntent->status === 'succeeded';

            Logger::info('Payment confirmation checked', [
                'payment_intent_id' => $paymentIntentId,
                'status' => $paymentIntent->status,
                'successful' => $isSuccessful
            ]);

            return $isSuccessful;

        } catch (\Exception $e) {
            Logger::exception($e, [
                'payment_intent_id' => $paymentIntentId
            ]);
            return false;
        }
    }

    /**
     * Handle Stripe webhook events
     *
     * @param string $payload Raw webhook payload
     * @param string $signature Stripe signature header
     * @return \Stripe\Event|null
     */
    public function handleWebhook($payload, $signature)
    {
        $webhookSecret = Config::get('STRIPE_WEBHOOK_SECRET');

        if (!$webhookSecret) {
            Logger::warning('Stripe webhook secret not configured');
            return null;
        }

        try {
            $event = Webhook::constructEvent(
                $payload,
                $signature,
                $webhookSecret
            );

            Logger::info('Webhook received', [
                'event_type' => $event->type,
                'event_id' => $event->id
            ]);

            return $event;

        } catch (\UnexpectedValueException $e) {
            Logger::error('Invalid webhook payload', ['error' => $e->getMessage()]);
            throw new \Exception('Invalid payload');
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Logger::error('Invalid webhook signature', ['error' => $e->getMessage()]);
            throw new \Exception('Invalid signature');
        }
    }

    /**
     * Cancel a Payment Intent
     *
     * @param string $paymentIntentId
     * @return bool
     */
    public function cancelPayment($paymentIntentId)
    {
        try {
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
            $paymentIntent->cancel();

            Logger::info('Payment Intent cancelled', [
                'payment_intent_id' => $paymentIntentId
            ]);

            return true;

        } catch (\Stripe\Exception\ApiErrorException $e) {
            Logger::error('Failed to cancel Payment Intent', [
                'payment_intent_id' => $paymentIntentId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}