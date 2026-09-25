<?php

declare(strict_types=1);

use Core\Constants\RedirectKey;

return [
    'name' => 'Riget Zoo Adventures',
    'short_name' => 'RZA',
    'description' => 'Book zoo tickets, safari experiences, and overnight stays at Riget Zoo Adventures.',
    'tagline' => 'Safari days, wildlife learning, and overnight stays planned in one place.',
    'logo' => '/assets/images/logo/rza-logo.png',
    'nav' => [
        ['label' => 'Home', 'href' => RedirectKey::HOME],
        [
            'label' => 'Bookings',
            'children' => [
                ['label' => 'Tickets', 'href' => RedirectKey::TICKETS],
                ['label' => 'Accommodations', 'href' => RedirectKey::ACCOMMODATIONS],
            ],
        ],
        ['label' => 'Attractions', 'href' => RedirectKey::ATTRACTIONS],
        ['label' => 'Educational', 'href' => RedirectKey::EDUCATIONAL],
    ],
    'social' => [
        ['label' => 'Facebook', 'href' => 'https://www.facebook.com', 'icon' => 'fa-facebook-f'],
        ['label' => 'Twitter', 'href' => 'https://twitter.com', 'icon' => 'fa-twitter'],
        ['label' => 'Instagram', 'href' => 'https://instagram.com', 'icon' => 'fa-instagram'],
        ['label' => 'Pinterest', 'href' => 'https://pinterest.com', 'icon' => 'fa-pinterest'],
    ],
];
