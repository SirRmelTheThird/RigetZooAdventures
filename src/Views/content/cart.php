<?php

declare(strict_types=1);

use Core\Constants\RedirectKey;

return [
    'header' => [
        'kicker' => 'Your visit',
        'title' => 'Shopping Cart',
        'lede' => 'Review tickets and stays before moving to checkout.',
    ],
    'items_title' => 'Your items',
    'remove_label' => 'Remove',
    'continue' => ['label' => 'Continue shopping', 'href' => RedirectKey::TICKETS],
    'clear_confirm' => 'Remove everything from your cart?',
    'summary' => [
        'title' => 'Order summary',
        'items_label' => 'Items',
        'total_label' => 'Total',
        'points_label' => 'Points to earn',
        'checkout_label' => 'Proceed to checkout',
        'clear_label' => 'Clear cart',
    ],
    'empty' => [
        'icon' => 'shopping_cart',
        'title' => 'Your cart is empty',
        'text' => 'Start with admission tickets, then add an overnight stay if you want more time in the park.',
        'primary' => ['label' => 'Browse tickets', 'href' => RedirectKey::TICKETS],
        'secondary' => ['label' => 'View stays', 'href' => RedirectKey::ACCOMMODATIONS],
    ],
];
