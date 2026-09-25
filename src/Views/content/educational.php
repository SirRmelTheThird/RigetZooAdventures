<?php

declare(strict_types=1);

use Core\Constants\RedirectKey;

return [
    'header' => [
        'kicker' => 'Learning visits',
        'title' => 'Educational visits',
        'lede' => 'Plan guided learning around conservation, biodiversity, animal behavior, and group discovery.',
    ],
    'feature' => [
        'title' => 'Built for discovery',
        'text' => 'Our zoo offers guided tours, workshops, and hands-on sessions that help students and youth groups understand wildlife and conservation.',
        'pills' => ['Guided tours', 'Youth groups', 'Conservation'],
        'cta' => ['label' => 'Book Tickets', 'href' => RedirectKey::TICKETS],
    ],
    'topics' => [
        [
            'icon' => 'diversity_3',
            'title' => 'Special Programs',
            'text' => 'Scouts, Guides, and Youth Groups can join structured sessions shaped around conservation, biodiversity, and animal behavior.',
        ],
        [
            'icon' => 'tune',
            'title' => 'Customized Experiences',
            'text' => 'We can tailor a visit around your group’s badge work, lesson topic, or preferred animal habitats.',
        ],
        [
            'icon' => 'event_available',
            'title' => 'Booking Information',
            'text' => 'For group bookings and educational programs, contact the zoo in advance so the team can prepare the right route and activities.',
        ],
    ],
];
