<?php

declare(strict_types=1);

namespace Services\Checkout;

use Cart\Cart;
use Core\Logging\Logger;
use Exceptions\Cart\CartException;
use Exceptions\Payment\PaymentException;
use Exceptions\System\UserFacingException;
use Payments\PaymentGateway;
use Payments\PaymentIntentRef;
use Repositories\Contracts\Auth\CustomerRepository;
use Core\Constants\RedirectKey;
use Services\Orders\OrderPlacer;
use Support\Messages;

final class CheckoutService
{
    private const CURRENCY = 'gbp';
    public function __construct(
        private readonly PaymentGateway $gateway,
        private readonly OrderPlacer $orders,
        private readonly Logger $logger,
        private readonly CustomerRepository $customers,
    ) {
    }

    /** @throws CartException|PaymentException */
    public function begin(string $customerId, Cart $cart): PaymentIntentRef
    {
        $this->assertNotEmpty($cart);
        $customer = $this->customers->findById($customerId);

        if ($customer === null) {
            throw new PaymentException(Messages::PAYMENT_UNVERIFIABLE);
        }

        $stripeCustomerId = (string) $customer->getAttribute('stripe_customer_id');

        if ($stripeCustomerId === '') {
            $stripeCustomerId = $this->gateway->createCustomer([
                'customer_id' => $customerId,
                'name' => trim(
                    (string) $customer->getAttribute('first_name')
                    . ' '
                    . (string) $customer->getAttribute('last_name')
                ),
                'username' => (string) $customer->getAttribute('username'),
                'email' => (string) $customer->getAttribute('email'),
            ]);
            $this->customers->saveStripeCustomerId($customer, $stripeCustomerId);
        }

        return $this->gateway->createIntent($cart->totalMinorUnits(), self::CURRENCY, $stripeCustomerId, [
            'order_type' => $this->orderType($cart),
        ]);
    }

    public function complete(string $customerId, Cart $cart, string $intentId): string
    {
        $this->assertNotEmpty($cart);

        $intent = $this->gateway->retrieveIntent($intentId);
        $customer = $this->customers->findById($customerId);

        if (!$intent->succeeded || $customer === null) {
            throw new PaymentException(Messages::PAYMENT_NOT_SUCCESSFUL);
        }

        if ($intent->customerId !== (string) $customer->getAttribute('stripe_customer_id')) {
            $this->logger->error('Payment belongs to another customer', ['payment_intent_id' => $intentId, 'customer_id' => $customerId]);

            throw new PaymentException(Messages::PAYMENT_UNVERIFIABLE);
        }

        if (strtolower($intent->currency) !== self::CURRENCY) {
            $this->logger->error('Payment currency differs from checkout currency', [
                'payment_intent_id' => $intentId,
                'payment_currency' => $intent->currency,
                'checkout_currency' => self::CURRENCY,
            ]);

            $this->refundAndFail($intentId, Messages::PAYMENT_MISMATCH, false);
        }

        if ($intent->amountMinorUnits !== $cart->totalMinorUnits()) {
            $this->logger->error('Paid amount differs from cart', [
                'payment_intent_id' => $intentId,
                'paid_minor_units' => $intent->amountMinorUnits,
                'cart_minor_units' => $cart->totalMinorUnits(),
            ]);

            $this->refundAndFail($intentId, Messages::PAYMENT_MISMATCH, false);
        }

        try {
            return $this->orders->placePaidOrder($customerId, $cart, $intentId);
        } catch (UserFacingException $e) {
            $this->refundAndFail($intentId, $e->getMessage(), true);
        }
    }

    private function assertNotEmpty(Cart $cart): void
    {
        if ($cart->isEmpty()) {
            throw new CartException(Messages::CART_EMPTY);
        }
    }

    private function orderType(Cart $cart): string
    {
        $types = [];

        foreach ($cart->items() as $item) {
            $type = match ($item->type()->cartType()) {
                'ticket' => 'tickets',
                default => $item->type()->cartType(),
            };
            $types[$type] = true;
        }

        return implode(',', array_keys($types));
    }

    private function refundAndFail(string $intentId, string $reason, bool $appendRefundNotice): never
    {
        try {
            $this->gateway->refund($intentId, 'refund:' . $intentId);
        } catch (PaymentException $e) {
            $this->logger->error('Refund failed, manual action required', ['payment_intent_id' => $intentId]);

            throw new PaymentException(Messages::REFUND_FAILED);
        }

        if ($appendRefundNotice) {
            $reason = sprintf(Messages::BOOKING_REFUNDED, $reason);
        }

        throw new PaymentException($reason, RedirectKey::CART);
    }
}
