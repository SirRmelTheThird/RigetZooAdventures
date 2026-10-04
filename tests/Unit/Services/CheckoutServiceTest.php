<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use Cart\Cart;
use Core\Logging\Logger;
use Exceptions\Cart\CartException;
use Exceptions\Payment\PaymentException;
use Models\Auth\Customer;
use Payments\PaymentIntentRef;
use Payments\PaymentIntentState;
use PHPUnit\Framework\TestCase;
use Repositories\Contracts\Auth\CustomerRepository;
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
        $customers = $this->createStub(CustomerRepository::class);
        $customer = new Customer();
        $customer->forceFill([
            'id' => '9',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'username' => 'jane_doe',
            'email' => 'jane@example.com',
            'stripe_customer_id' => 'cus_test',
        ]);
        $customers->method('findById')->willReturn($customer);

        return new CheckoutService($gateway, $placer, new Logger(new MemoryLogWriter()), $customers);
    }

    private function paid(int $customerId, int $amount): PaymentIntentState
    {
        return new PaymentIntentState('pi_1', true, $amount, 'gbp', $customerId === 9 ? 'cus_test' : 'cus_other');
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
        $gateway->state = new PaymentIntentState('pi_1', false, 100, 'gbp', 'cus_test');

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
            'cus_test',
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

            public function createIntent(int $amountMinorUnits, string $currency, string $customerId, array $metadata): PaymentIntentRef
            {
                $this->amount = $amountMinorUnits;

                return parent::createIntent($amountMinorUnits, $currency, $customerId, $metadata);
            }
        };

        $this->checkout($gateway, new FakePlacer())->begin('9', $this->ticketCart(1, 0));

        self::assertSame(2000, $gateway->amount);
    }

    public function testBeginKeepsCustomerDetailsOutOfPaymentMetadata(): void
    {
        $gateway = new FakeGateway();

        $this->checkout($gateway, new FakePlacer())->begin('9', $this->ticketCart(1, 0));

        self::assertSame(['order_type' => 'tickets'], $gateway->metadata);
        self::assertSame('cus_test', $gateway->intentCustomerId);
    }

    public function testBeginCreatesAndStoresStripeCustomerWhenMissing(): void
    {
        $gateway = new FakeGateway();
        $customer = new Customer();
        $customer->forceFill([
            'id' => '9',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'username' => 'jane_doe',
            'email' => 'jane@example.com',
        ]);
        $customers = $this->createMock(CustomerRepository::class);
        $customers->expects(self::once())->method('findById')->with('9')->willReturn($customer);
        $customers->expects(self::once())->method('saveStripeCustomerId')->with($customer, 'cus_test');

        $checkout = new CheckoutService($gateway, new FakePlacer(), new Logger(new MemoryLogWriter()), $customers);
        $checkout->begin('9', $this->ticketCart(1, 0));

        self::assertSame([
            'customer_id' => '9',
            'name' => 'Jane Doe',
            'username' => 'jane_doe',
            'email' => 'jane@example.com',
        ], $gateway->customer);
    }
}
