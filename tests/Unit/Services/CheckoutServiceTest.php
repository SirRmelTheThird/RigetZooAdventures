<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use Cart\Cart;
use Core\Logging\Logger;
use Exceptions\Cart\CartException;
use Exceptions\Payment\PaymentException;
use Payments\PaymentIntentRef;
use Payments\PaymentIntentState;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Services\Checkout\CheckoutService;
use Tests\Support\CartFixtures;
use Tests\Support\FakeGateway;
use Tests\Support\FakePlacer;
use Tests\Support\MemoryLogWriter;

final class CheckoutServiceTest extends TestCase
{
    use CartFixtures;

    private function checkout(FakeGateway $gateway, FakePlacer $placer): CheckoutService
    {
        return new CheckoutService($gateway, $placer, new Logger(new MemoryLogWriter()));
    }

    private function paid(int $customerId, int $amount): PaymentIntentState
    {
        return new PaymentIntentState('pi_1', true, $amount, 'gbp', (string) $customerId);
    }

    public function testMatchingPaidIntentBooksTheOrderOnce(): void
    {
        $gateway = new FakeGateway();
        $placer = new FakePlacer();
        $cart = $this->ticketCart();
        $gateway->state = $this->paid(9, $cart->totalMinorUnits());

        self::assertSame('a2d92341-24c7-4589-94de-b525bd175cf32', $this->checkout($gateway, $placer)->complete('9', $cart, 'pi_1'));
        self::assertSame(1, $placer->calls);
        self::assertSame([], $gateway->refunded);
    }

    public function testUnpaidIntentBooksNothingAndRefundsNothing(): void
    {
        $gateway = new FakeGateway();
        $placer = new FakePlacer();
        $gateway->state = new PaymentIntentState('pi_1', false, 100, 'gbp', '9');

        try {
            $this->checkout($gateway, $placer)->complete('9', $this->ticketCart(), 'pi_1');
            self::fail('expected PaymentException');
        } catch (PaymentException) {
            self::assertSame(0, $placer->calls);
            self::assertSame([], $gateway->refunded);
        }
    }

    public function testSomeoneElsesPaymentIsNeverUsedOrRefunded(): void
    {
        $gateway = new FakeGateway();
        $placer = new FakePlacer();
        $cart = $this->ticketCart();
        $gateway->state = $this->paid(1, $cart->totalMinorUnits());

        try {
            $this->checkout($gateway, $placer)->complete('9', $cart, 'pi_1');
            self::fail('expected PaymentException');
        } catch (PaymentException) {
            self::assertSame(0, $placer->calls);
            self::assertSame([], $gateway->refunded);
        }
    }

    public function testWrongCurrencyPaymentIsRefundedAndNeverBooked(): void
    {
        $gateway = new FakeGateway();
        $placer = new FakePlacer();
        $cart = $this->ticketCart();
        $gateway->state = new PaymentIntentState(
            'pi_1',
            true,
            $cart->totalMinorUnits(),
            'eur',
            '9',
        );

        try {
            $this->checkout($gateway, $placer)->complete('9', $cart, 'pi_1');
            self::fail('expected PaymentException');
        } catch (PaymentException) {
            self::assertSame(0, $placer->calls);
            self::assertSame(['pi_1'], $gateway->refunded);
            self::assertSame(['refund:pi_1'], $gateway->refundKeys);
        }
    }

    public function testCartChangedAfterPayingIsRefundedNotBooked(): void
    {
        $gateway = new FakeGateway();
        $placer = new FakePlacer();
        $gateway->state = $this->paid(9, 100);

        try {
            $this->checkout($gateway, $placer)->complete('9', $this->ticketCart(), 'pi_1');
            self::fail('expected PaymentException');
        } catch (PaymentException) {
            self::assertSame(0, $placer->calls);
            self::assertSame(['pi_1'], $gateway->refunded);
            self::assertSame(['refund:pi_1'], $gateway->refundKeys);
        }
    }

    public function testSoldOutAfterPayingRefundsAndSaysSo(): void
    {
        $gateway = new FakeGateway();
        $placer = new FakePlacer();
        $placer->failWith = new CartException('Not enough Standard Adult tickets remain');
        $cart = $this->ticketCart();
        $gateway->state = $this->paid(9, $cart->totalMinorUnits());

        try {
            $this->checkout($gateway, $placer)->complete('9', $cart, 'pi_1');
            self::fail('expected PaymentException');
        } catch (PaymentException $e) {
            self::assertSame(['pi_1'], $gateway->refunded);
            self::assertStringContainsString('refunded', $e->getMessage());
            self::assertSame('/cart', $e->redirectTo());
        }
    }

    public function testInfrastructureErrorsAreNotRefunded(): void
    {
        $gateway = new FakeGateway();
        $placer = new FakePlacer();
        $placer->failWith = new RuntimeException('db down');
        $cart = $this->ticketCart();
        $gateway->state = $this->paid(9, $cart->totalMinorUnits());

        try {
            $this->checkout($gateway, $placer)->complete('9', $cart, 'pi_1');
            self::fail('expected RuntimeException');
        } catch (RuntimeException $e) {
            self::assertSame([], $gateway->refunded, 'infrastructure failures must stay retryable, not refunded');
        }
    }

    public function testFailedRefundTellsTheCustomerToContactSupport(): void
    {
        $gateway = new FakeGateway();
        $gateway->refundFails = true;
        $gateway->state = $this->paid(9, 1);

        try {
            $this->checkout($gateway, new FakePlacer())->complete('9', $this->ticketCart(), 'pi_1');
            self::fail('expected PaymentException');
        } catch (PaymentException $e) {
            self::assertStringContainsString('contact support', $e->getMessage());
        }
    }

    public function testEmptyCartIsRejectedBeforeTouchingTheGateway(): void
    {
        $this->expectException(CartException::class);
        $this->checkout(new FakeGateway(), new FakePlacer())->begin('9', Cart::empty());
    }

    public function testBeginSendsTheCartTotalInCents(): void
    {
        $gateway = new class () extends FakeGateway {
            public int $amount = 0;

            public function createIntent(int $amountMinorUnits, string $currency, array $metadata): PaymentIntentRef
            {
                $this->amount = $amountMinorUnits;

                return parent::createIntent($amountMinorUnits, $currency, $metadata);
            }
        };

        $this->checkout($gateway, new FakePlacer())->begin('9', $this->ticketCart(1, 0));

        self::assertSame(1999, $gateway->amount);
    }
}
