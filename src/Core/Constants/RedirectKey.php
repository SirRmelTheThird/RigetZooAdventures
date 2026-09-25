<?php

declare(strict_types=1);

namespace Core\Constants;

final class RedirectKey
{
    public const HOME = '/';
    public const LOGIN = '/login';
    public const SIGNUP = '/signup';
    public const PROFILE = '/profile';
    public const TICKETS = '/tickets';
    public const TICKETS_STANDARD = '/tickets/standard';
    public const TICKETS_PREMIUM = '/tickets/premium';
    public const ACCOMMODATIONS = '/accommodations';
    public const ACCOMMODATIONS_ADD = '/accommodations/add';
    public const ATTRACTIONS = '/attractions';
    public const EDUCATIONAL = '/educational';
    public const CART = '/cart';
    public const CART_REMOVE = '/cart/remove';
    public const CART_CLEAR = '/cart/clear';
    public const CHECKOUT = '/checkout';
    public const PAYMENT_PROCESS = '/checkout/process';
    public const STRIPE_WEBHOOK = '/webhook/stripe';
    public const LOGOUT = '/logout';
}
