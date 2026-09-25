<?php

declare(strict_types=1);

namespace Services;

use Cart\Cart;
use Core\Constants\RedirectKey;
use Core\Logging\Logger;
use Exceptions\CartException;
use Exceptions\PaymentException;
use Exceptions\UserFacingException;
use Payments\PaymentGateway;
use Payments\PaymentIntentRef;
use Support\Messages;

final class CheckoutService
{
    private const CURRENCY = 'gbp';
    private const ORDER_TYPE = 'zoo_booking';

    public function __construct(
        private readonly PaymentGateway $gateway,
        private readonly OrderPlacer $orders,
        private readonly Logger $logger,
    ) {
    }

    /** @throws CartException|PaymentException */
    public function begin(int $customerId, Cart $cart): PaymentIntentRef
    {
        $this->assertNotEmpty($cart);

        return $this->gateway->createIntent($cart->totalMinorUnits(), self::CURRENCY, [
            'customer_id' => $customerId,
            'order_type' => self::ORDER_TYPE,
        ]);
    }

    public function complete(int $customerId, Cart $cart, string $intentId): int
    {
        $this->assertNotEmpty($cart);

        $intent = $this->gateway->retrieveIntent($intentId);

        if (!$intent->succeeded) {
            throw new PaymentException(Messages::PAYMENT_NOT_SUCCESSFUL, RedirectKey::CHECKOUT);
        }

        if ($intent->customerId !== $customerId) {
            $this->logger->error('Payment belongs to another customer', ['payment_intent_id' => $intentId, 'customer_id' => $customerId]);

            throw new PaymentException(Messages::PAYMENT_UNVERIFIABLE, RedirectKey::CHECKOUT);
        }

        if ($intent->amountMinorUnits !== $cart->totalMinorUnits()) {
            $this->logger->error('Paid amount differs from cart', [
                'payment_intent_id' => $intentId,
                'paid_minor_units' => $intent->amountMinorUnits,
                'cart_minor_units' => $cart->totalMinorUnits(),
            ]);

            $this->refundAndFail($intentId, Messages::PAYMENT_MISMATCH, RedirectKey::CHECKOUT, false);
        }

        try {
            return $this->orders->placePaidOrder($customerId, $cart, $intentId);
        } catch (UserFacingException $e) {
            $this->refundAndFail($intentId, $e->getMessage(), RedirectKey::CART, true);
        }
    }

    private function assertNotEmpty(Cart $cart): void
    {
        if ($cart->isEmpty()) {
            throw new CartException(Messages::CART_EMPTY, RedirectKey::CART);
        }
    }

    private function refundAndFail(string $intentId, string $reason, string $redirectTo, bool $appendRefundNotice): never
    {
        try {
            $this->gateway->refund($intentId);
        } catch (PaymentException $e) {
            $this->logger->error('Refund failed, manual action required', ['payment_intent_id' => $intentId]);

            throw new PaymentException(Messages::REFUND_FAILED, $redirectTo);
        }

        if ($appendRefundNotice) {
            $reason = sprintf(Messages::BOOKING_REFUNDED, $reason);
        }

        throw new PaymentException($reason, $redirectTo);
    }
}
