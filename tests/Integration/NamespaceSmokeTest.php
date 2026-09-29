<?php

declare(strict_types=1);

namespace Tests\Integration;

use Core\Session\Session;
use Core\View\View;
use Enums\OrderStatus;
use Enums\TicketCategory;
use Models\Tickets\Ticket;
use PHPUnit\Framework\TestCase;
use Services\Tickets\TicketPricing;
use Services\Orders\OrderPresenter;

/**
 * Namespace / class-existence smoke test.
 * Prevents broken view references (e.g. Core\Session, Core\View\TicketPricing)
 * from reaching production again.
 */
final class NamespaceSmokeTest extends TestCase
{
    public function testSessionStoresAndReadsAuthenticatedState(): void
    {
        $_SESSION = [];
        Session::set('customer_id', 'customer-1');

        self::assertSame('customer-1', Session::getUserId());
        self::assertFalse(Session::isLoggedIn());
    }

    public function testTicketPricingBuildsPricesForTicketViews(): void
    {
        $adult = new Ticket();
        $adult->setRawAttributes(['category' => TicketCategory::Adult->value, 'price' => '19.99']);
        $child = new Ticket();
        $child->setRawAttributes(['category' => TicketCategory::Child->value, 'price' => '9.99']);
        $pricing = TicketPricing::fromTickets([$adult, $child]);

        self::assertSame(19.99, $pricing->adult);
        self::assertSame(9.99, $pricing->child);
    }

    public function testOrderPresenterMapsStatusesForProfileView(): void
    {
        self::assertSame('rz-status--paid', OrderPresenter::statusClass(OrderStatus::Paid));
    }

    public function testViewBindWorks(): void
    {
        View::bind(dirname(__DIR__, 2) . '/src/Views');
        self::assertTrue(View::content(\Contracts\AuthContentInterface::class) instanceof \Contracts\AuthContentInterface);
    }
}
