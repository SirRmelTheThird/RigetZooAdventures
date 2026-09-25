<?php

declare(strict_types=1);

use Core\Constants\RedirectKey;

return [
    'header' => [
        'kicker' => 'Account',
        'title' => 'Your Profile',
        'lede' => 'View account details, reward points, and booking history.',
    ],
    'orders' => [
        'title' => 'Order History',
        'columns' => ['Order', 'Date', 'Items', 'Total', 'Status'],
        'general_booking' => 'General booking',
    ],
    'empty' => [
        'icon' => 'receipt_long',
        'title' => 'No orders yet',
        'text' => 'Start with a ticket, then return here to review your bookings.',
        'primary' => ['label' => 'Start booking', 'href' => RedirectKey::TICKETS],
    ],
    'account' => [
        'title' => 'Account Information',
        'logout_label' => 'Log out',
    ],
];
