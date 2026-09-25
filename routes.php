<?php

declare(strict_types=1);

const AUTH = 'AuthMiddleware';
const CSRF = 'CSRFMiddleware';

$router
    ->get('/', 'Home@index')
    ->get('/profile', 'Home@profile', [AUTH])
    ->get('/attractions', 'Home@attractions')
    ->get('/educational', 'Home@educational')

    ->get('/login', 'Auth@showLogin')
    ->post('/login', 'Auth@login', [CSRF])
    ->get('/signup', 'Auth@showSignup')
    ->post('/signup', 'Auth@signup', [CSRF])
    ->post('/logout', 'Auth@logout', [AUTH, CSRF])

    ->get('/tickets', 'Ticket@index')
    ->get('/tickets/standard', 'Ticket@showStandard')
    ->get('/tickets/premium', 'Ticket@showPremium')
    ->post('/tickets/standard', 'Ticket@addStandard', [CSRF])
    ->post('/tickets/premium', 'Ticket@addPremium', [CSRF])

    ->get('/accommodations', 'Accommodation@index')
    ->post('/accommodations/add', 'Accommodation@addToCart', [CSRF])

    ->get('/cart', 'Cart@index')
    ->post('/cart/remove', 'Cart@removeItem', [CSRF])
    ->post('/cart/clear', 'Cart@clear', [CSRF])

    ->get('/checkout', 'Payment@checkout', [AUTH])
    ->post('/checkout/process', 'Payment@process', [AUTH, CSRF])
    ->post('/webhook/stripe', 'Payment@webhook');
