<?php

declare(strict_types=1);

namespace Core\Constants;

final class SessionKey
{
    public const CART = 'cart';
    public const CUSTOMER_ID = 'customer_id';
    public const USERNAME = 'username';
    public const FIRST_NAME = 'first_name';
    public const EMAIL = 'email';
    public const PAYMENT_INTENT = 'payment_intent_id';
    public const FLASH = 'flash';
    public const SUCCESS = 'success';
    public const ERROR = 'error';
    public const FORM_DATA = 'form_data';
    public const VALIDATION_ERRORS = 'errors';
    public const LAST_REGENERATION = 'last_regeneration';
    public const CSRF_TOKEN = 'csrf_token';
}
