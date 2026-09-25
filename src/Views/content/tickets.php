<?php

declare(strict_types=1);

use Core\Constants\RedirectKey;

return [
    'index' => [
        'kicker' => 'Admission',
        'title' => 'Select your tickets',
        'lede' => 'Choose a simple day pass or unlock the full safari route with premium access.',
    ],
    'ages' => [
        'adult' => ['field' => 'adult', 'title' => 'Adult tickets', 'hint' => 'Ages 16 and over', 'price_label' => 'Adult'],
        'child' => ['field' => 'child', 'title' => 'Child tickets', 'hint' => 'Ages 3 to 15', 'price_label' => 'Child'],
    ],
    'booking' => [
        'date_label' => 'Visit date',
        'total_label' => 'Total',
        'submit_label' => 'Add to cart',
    ],
    'tiers' => [
        'standard' => [
            'card' => [
                'title' => 'Standard Ticket',
                'text' => 'Full-day entry to the zoo, animal habitats, visitor paths, restaurants, and family facilities.',
                'includes' => ['Full-day zoo entry', 'Animal habitats and visitor paths', 'Restaurants and family facilities'],
                'cta' => 'View Standard',
                'featured' => false,
            ],
            'href' => RedirectKey::TICKETS_STANDARD,
            'page' => [
                'kicker' => 'Standard admission',
                'title' => 'Plan a classic zoo day.',
                'lede' => 'A straightforward pass for habitats, facilities, food stops, and a full day at Riget Zoo Adventures.',
                'form_title' => 'Standard tickets',
                'date_id' => 'standard_visit_date',
                'form_intro' => 'Select your group size and visit date. You can review the total before checkout.',
                'extra_pill' => 'Infants free',
                'callout' => null,
                'media' => [
                    'src' => '/assets/images/WEBP/ticket-3.webp',
                    'alt' => 'Standard Ticket at Riget Zoo Adventures',
                    'icon' => 'confirmation_number',
                ],
            ],
        ],
        'premium' => [
            'card' => [
                'title' => 'Premium Ticket',
                'text' => 'Includes premium safari experiences, guided access, and the widest route through the park.',
                'includes' => ['Drive-through safari', 'Walking safari and boat safari', 'Experienced guides and education programs'],
                'cta' => 'View Premium',
                'featured' => true,
            ],
            'href' => RedirectKey::TICKETS_PREMIUM,
            'page' => [
                'kicker' => 'Premium admission',
                'title' => 'Unlock the full safari route.',
                'lede' => 'Premium tickets add guided experiences and expanded access for visitors who want the complete day.',
                'form_title' => 'Premium tickets',
                'date_id' => 'premium_visit_date',
                'form_intro' => 'Choose your group size and date. Premium access includes drive-through safari, walking safari, and boat safari experiences.',
                'extra_pill' => 'Guided access',
                'callout' => [
                    'title' => 'Premium includes',
                    'text' => 'Drive-through safari, walking safari, boat safari, experienced guides, and education programs.',
                ],
                'media' => [
                    'src' => '/assets/images/WEBP/ticket-4.webp',
                    'alt' => 'Premium Ticket at Riget Zoo Adventures',
                    'icon' => 'confirmation_number',
                ],
            ],
        ],
    ],
];
