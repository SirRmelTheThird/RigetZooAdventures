<?php

declare(strict_types=1);

namespace Support;

final class Messages
{
    // Auth
    public const LOGIN_REQUIRED = 'Please log in to continue';
    public const INVALID_CREDENTIALS = 'Invalid Credentials';
    public const LOGGED_IN = 'Logged In Successfully!';
    public const ACCOUNT_CREATED = 'Account Created! Please Log In.';
    public const LOGGED_OUT = 'You have been logged out';
    public const USERNAME_TAKEN = 'That username is already taken.';
    public const EMAIL_TAKEN = 'An account with that email already exists.';
    public const PASSWORD_MISMATCH = 'Passwords do not match.';

    // Cart
    public const INVALID_ITEM = 'Invalid item';
    public const CART_EMPTY = 'Your cart is empty';
    public const ITEM_REMOVED = 'Item removed from cart';
    public const CART_CLEARED = 'Cart cleared';
    public const TICKETS_ADDED = 'Tickets added to cart!';
    public const ACCOMMODATION_ADDED = 'Accommodation added to cart!';
    public const SELECT_AT_LEAST_ONE_TICKET = 'Please select at least one ticket';
    public const SINGLE_ACCOMMODATION_ONLY = 'You can only book one accommodation per order';
    public const CHECKOUT_AFTER_DATE = 'Check-out date must be after check-in date';
    public const ACCOMMODATION_NOT_FOUND = 'Accommodation not found';
    public const ACCOMMODATION_UNAVAILABLE = 'This accommodation is not available for the selected dates';
    public const GUESTS_EXCEEDED = 'This accommodation sleeps at most %d guests';
    public const TICKET_NOT_FOUND = 'No %s %s ticket is on sale';
    public const TICKETS_SOLD_OUT = 'Not enough %s %s tickets remain';

    // Checkout
    public const PAYMENT_INTENT_MISSING = 'Payment intent not found';
    public const PAYMENT_INIT_FAILED = 'Unable to initialize payment. Please try again.';
    public const PAYMENT_NOT_SUCCESSFUL = 'Payment was not successful. Please try again.';
    public const PAYMENT_MISMATCH = 'Your payment does not match your cart. It has been refunded; please check out again.';
    public const PAYMENT_UNVERIFIABLE = 'We could not verify this payment. Please contact support.';
    public const BOOKING_REFUNDED = '%s Your payment has been refunded.';
    public const REFUND_FAILED = 'Your booking failed and the automatic refund did not go through. Please contact support.';
    public const ORDER_PLACED = 'Order #%s placed successfully! Thank you for your booking.';

    // HTTP
    public const PAGE_NOT_FOUND = 'Page not found';
    public const CSRF_INVALID = 'CSRF token invalid';
    public const SERVER_ERROR = 'Something went wrong. Please try again.';

    // Discord
    public const DISCORD_NO_FAILURE_REASON = 'No failure reason provided';
}
