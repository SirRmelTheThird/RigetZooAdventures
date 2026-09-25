<?php

declare(strict_types=1);

use Core\Constants\RedirectKey;

return [
    'hero' => [
        'kicker' => 'Plan your visit',
        'title' => 'Wild days, Restful nights.',
        'lede' => 'Book zoo tickets, safari experiences, and park stays with one clear path from planning to checkout.',
        'primary' => ['label' => 'Book tickets', 'href' => RedirectKey::TICKETS],
        'secondary' => ['label' => 'View stays', 'href' => RedirectKey::ACCOMMODATIONS],
        'media' => [
            'src' => '/assets/images/webp/home.webp',
            'alt' => 'Deer at Riget Zoo Adventures',
            'icon' => 'forest',
        ],
    ],
    'facts' => [
        ['icon' => 'schedule', 'title' => 'Open Daily', 'text' => '9am to 7pm'],
        ['icon' => 'child_care', 'title' => 'Infants', 'text' => 'Free Entry'],
        ['icon' => 'explore', 'title' => 'Premium Safari', 'text' => 'Drive, Walk or Boat'],
    ],
    'visit' => [
        'title' => 'A full visit in one place.',
        'lede' => 'Choose admission, add a stay inside the park, and keep your day focused on the animals instead of the admin.',
        'tiles' => [
            [
                'tone' => 'forest',
                'title' => 'Tickets',
                'text' => 'Pick Standard Tickets for a full day of discovery or Premium Tickets for added safari access and guided experiences.',
                'href' => RedirectKey::TICKETS,
                'media' => [
                    'src' => '/assets/images/webp/home-ticket.webp',
                    'alt' => 'Riget Zoo Adventures admission tickets',
                    'icon' => 'confirmation_number',
                ],
            ],
            [
                'tone' => 'sage',
                'title' => 'Accommodations',
                'text' => 'Wake near the wildlife with lodges, hotels, and glamping stays built around the park experience.',
                'href' => RedirectKey::ACCOMMODATIONS,
                'media' => [
                    'src' => '/assets/images/webp/home-accommodation.webp',
                    'alt' => 'Safari accommodation at Riget Zoo Adventures',
                    'icon' => 'cabin',
                ],
            ],
            [
                'tone' => 'cream',
                'title' => 'Attractions',
                'text' => 'Move through habitats, animal encounters, guided experiences, restaurants, and family facilities.',
                'href' => RedirectKey::ATTRACTIONS,
                'media' => [
                    'src' => '/assets/images/webp/home-attraction.webp',
                    'alt' => 'Zoo train and attraction route',
                    'icon' => 'train',
                ],
            ],
        ],
    ],
];
