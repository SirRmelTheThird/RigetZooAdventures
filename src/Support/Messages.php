<?php

declare(strict_types=1);

namespace Support;

final class Messages
{
    // Auth
    public const LOGIN_REQUIRED = 'Please log in to continue.';
    public const INVALID_CREDENTIALS = 'Invalid username or password.';
    public const LOGGED_IN = 'You have successfully logged in.';
    public const ACCOUNT_CREATED = 'Your account has been created. Please log in.';
    public const LOGGED_OUT = 'You have been logged out.';
    public const USERNAME_TAKEN = 'That username is already taken.';
    public const EMAIL_TAKEN = 'An account with that email address already exists.';
    public const PASSWORD_MISMATCH = 'Your passwords do not match.';

    // Cart
    public const INVALID_ITEM = 'Invalid cart item.';
    public const CART_EMPTY = 'Your cart is empty.';
    public const ITEM_REMOVED = 'The item has been removed from your cart.';
    public const CART_CLEARED = 'Your cart has been cleared.';
    public const TICKETS_ADDED = 'Tickets have been added to your cart.';
    public const ACCOMMODATION_ADDED = 'Accommodation has been added to your cart.';
    public const SELECT_AT_LEAST_ONE_TICKET = 'Please select at least one ticket.';
    public const SINGLE_ACCOMMODATION_ONLY = 'You can only book one accommodation per order.';
    public const CHECKOUT_AFTER_DATE = 'The check-out date must be after the check-in date.';
    public const ACCOMMODATION_NOT_FOUND = 'The selected accommodation could not be found.';
    public const ACCOMMODATION_UNAVAILABLE = 'The selected accommodation is not available for your chosen dates.';
    public const ACCOMMODATION_DATE_UNAVAILABLE = 'This date falls within an unavailable period. Please choose another date.';
    public const ACCOMMODATION_RANGE_UNAVAILABLE = 'Your selected dates overlap an unavailable period.';
    public const GUESTS_EXCEEDED = 'This accommodation can accommodate a maximum of %d guests.';
    public const TICKET_NOT_FOUND = 'No %s %s tickets are currently available for purchase.';
    public const TICKETS_SOLD_OUT = 'Only %s %s tickets remain available.';

    // Checkout
    public const PAYMENT_INTENT_MISSING = 'The payment could not be found.';
    public const PAYMENT_INIT_FAILED = 'We could not initialise your payment. Please try again.';
    public const PAYMENT_NOT_SUCCESSFUL = 'Your payment was unsuccessful. Please try again.';
    public const PAYMENT_MISMATCH = 'Your payment does not match your order. Your payment has been refunded. Please place your order again.';
    public const PAYMENT_UNVERIFIABLE = 'We could not verify your payment. Please contact support.';
    public const BOOKING_REFUNDED = '%s Your payment has been refunded.';
    public const REFUND_FAILED = 'Your booking could not be completed, and the automatic refund was unsuccessful. Please contact support.';
    public const ORDER_PLACED = 'Order #%s has been placed successfully. Thank you for your booking.';

    // HTTP
    public const PAGE_NOT_FOUND = 'The requested page could not be found.';
    public const CSRF_INVALID = 'Your session has expired or the request is invalid. Please try again.';
    public const SERVER_ERROR = 'Something went wrong. Please try again.';

    // Discord
    public const DISCORD_NO_FAILURE_REASON = 'No failure reason was provided.';
}
