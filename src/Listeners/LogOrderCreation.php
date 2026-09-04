<?php

namespace Listeners;

use Events\OrderCreated;
use Core\Logger;

class LogOrderCreation
{
    public function handle(OrderCreated $event)
    {
        Logger::info('Order created event handled', [
            'order_id' => $event->orderId,
            'customer_id' => $event->customerId,
            'total' => $event->cart['total'] ?? 0,
            'items_count' => count($event->cart['items'] ?? [])
        ]);
    }
}
