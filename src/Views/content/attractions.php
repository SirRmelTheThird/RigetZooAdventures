<?php

declare(strict_types=1);

use Core\Constants\RedirectKey;

return [
    'header' => [
        'kicker' => 'Inside the park',
        'title' => 'Attractions and Facilities',
        'lede' => 'Explore animal habitats, visitor routes, restaurants, and the everyday stops that make the park easy to enjoy.',
    ],
    'features' => [
        [
            'title' => 'Wildlife Routes',
            'text' => 'Move between animal exhibits, immersive habitats, keeper encounters, children’s play areas, shows, and guided transport routes through the sanctuary.',
            'pills' => ['Animal habitats', 'Zoo train', 'Family facilities'],
            'media' => [
                'src' => '/assets/images/WEBP/wildlife-routes.webp',
                'alt' => 'Zoo train route at Riget Zoo Adventures',
                'icon' => 'train',
            ],
        ],
        [
            'title' => 'Gift Shop',
            'text' => 'Pick up safari clothing, plush animals, handmade keepsakes, and wildlife-inspired gifts after your day in the park.',
            'pills' => [],
            'media' => [
                'src' => '/assets/images/WEBP/gift-shop.webp',
                'alt' => 'Gift shop at Riget Zoo Adventures',
                'icon' => 'storefront',
            ],
        ],
        [
            'title' => 'Restaurants',
            'text' => 'Take a proper break with casual meals, scenic views, and food options placed close to the main visitor routes.',
            'pills' => [],
            'media' => [
                'src' => '/assets/images/WEBP/restaurant.webp',
                'alt' => 'Restaurant seating at Riget Zoo Adventures',
                'icon' => 'restaurant',
            ],
        ],
    ],
    'closing' => [
        'title' => 'Ready to plan the day?',
        'text' => 'Pick Standard or Premium admission, then add an overnight stay if you want more time in the park.',
        'cta' => ['label' => 'Book Tickets', 'href' => RedirectKey::TICKETS],
    ],
];
