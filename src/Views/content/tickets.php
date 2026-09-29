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
                'title' => 'Standard Tickets',
                'text' => 'Perfect for families and wildlife lovers, enjoy a memorable day out exploring the wonders of nature, discovering fascinating animals, and creating lasting memories together.',
                'includes' => [
                    'first' => 'Full-Day Zoo Entry',
                    'second' => 'Animal Habitats and Visitor paths',
                    'third' => 'Restaurants and Family Facilities',
                ],
                'cta' => 'View Standard',
                'featured' => false,
            ],
            'href' => RedirectKey::TICKETS_STANDARD,
            'page' => [
                'kicker' => 'Standard admission',
                'title' => 'Plan a classic zoo day.',
                'lede' => 'Enjoy a memorable day discovering fascinating wildlife, exploring nature, and making lasting memories',
                'form_title' => 'Standard Ticket',
                'date_id' => 'standard_visit_date',
                'form_intro' => 'Choose your visit date and group size to plan your perfect day. Review your booking total before proceeding to checkout.',
                'extra_pill' => [
                    'Full-day zoo access',
                    'Wildlife habitats',
                    'Family-friendly visit',
                ],
                'callout' => [
                    'title' => 'What’s included',
                  'text' => [
                      'Animal feeding demonstrations',
                      'Wildlife conservation and educational displays',
                      'Children\'s discovery activities',
                      'Seasonal wildlife presentations',
                      'Interactive animal information stations',
                      'Self-guided zoo exploration',
                  ],
                ],
                'media' => [
                    'src' => '/assets/images/webp/ticket-3.webp',
                    'alt' => 'Standard Ticket at Riget Zoo Adventures',
                    'icon' => 'confirmation_number',
                ],
            ],
        ],
        'premium' => [
            'card' => [
                'title' => 'Premium Tickets',
                'text' => 'Take your visit to the next level with an unforgettable wildlife adventure. Designed for curious explorers, enjoy a more immersive experience with exciting opportunities to discover the natural world.',
                'includes' => [
                    'first' => 'Drive-Through Safari',
                    'second' => 'Guided Walking Safari and Boat Safari',
                    'third' => 'Experience Guides and Education Programs',
                ],
                'cta' => 'View Premium',
                'featured' => true,
            ],
            'href' => RedirectKey::TICKETS_PREMIUM,
            'page' => [
                'kicker' => 'Premium admission',
                'title' => 'Unlock the full safari route.',
                'lede' => 'Make your visit truly memorable with an immersive wildlife adventure designed for curious explorers and nature enthusiasts.',
                'form_title' => 'Premium Ticket',
                'date_id' => 'premium_visit_date',
                'form_intro' => 'Get ready for an unforgettable wildlife adventure. Choose your visit date and the number of guests to get started.',
                'extra_pill' => [
                    'Premium admission',
                    '3 safari experiences',
                    'Expert-guided tours',
                ],
                'callout' => [
                    'title' => 'Premium includes',
                    'text' => [
                        'Priority access to selected attractions',
                        'Exclusive wildlife viewing areas',
                        'Behind-the-scenes animal care insights',
                        'Personalised safari experience',
                        'Premium visitor facilities',
                        'Dedicated guest assistance',
                    ],
                ],
                'media' => [
                    'src' => '/assets/images/webp/ticket-4.webp',
                    'alt' => 'Premium Ticket at Riget Zoo Adventures',
                    'icon' => 'confirmation_number',
                ],
            ],
        ],
    ],
];
