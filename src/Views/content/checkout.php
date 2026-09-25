<?php

declare(strict_types=1);

return [
    'header' => [
        'kicker' => 'Checkout',
        'title' => 'Complete your booking.',
        'lede' => 'Review your visit details, then complete payment securely through Stripe.',
    ],
    'summary' => [
        'title' => 'Order summary',
        'items_label' => 'Items',
        'total_label' => 'Total',
    ],
    'payment' => [
        'title' => 'Payment information',
        'text' => 'Your payment details are handled by Stripe and are never sent to this server.',
        'submit_label' => 'Complete payment',
        'failure_message' => 'Payment could not be completed.',
    ],
];
