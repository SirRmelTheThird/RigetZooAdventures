<?php

declare(strict_types=1);

namespace Cart;

use Core\Constants\SessionKey;
use Core\Logging\Logger;
use Core\Session;

final class SessionCartStore implements CartStore
{
    public function __construct(private readonly Logger $logger)
    {
    }

    public function load(): Cart
    {
        if (!Session::has(SessionKey::CART)) {
            return Cart::empty();
        }

        try {
            return Cart::fromArray((array) Session::get(SessionKey::CART));
        } catch (InvalidCartPayloadException $e) {
            $this->logger->warning('Discarded unreadable cart', ['reason' => $e->getMessage()]);
            Session::remove(SessionKey::CART);

            return Cart::empty();
        }
    }

    public function save(Cart $cart): void
    {
        if ($cart->isEmpty()) {
            Session::remove(SessionKey::CART);

            return;
        }

        Session::set(SessionKey::CART, $cart->toArray());
    }
}
