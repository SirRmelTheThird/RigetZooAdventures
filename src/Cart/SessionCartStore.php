<?php

declare(strict_types=1);

namespace Cart;

use Core\Constants\SessionKey;
use Core\Logging\Logger;
use Core\SessionStore;

final class SessionCartStore implements CartStore
{
    public function __construct(
        private readonly SessionStore $session,
        private readonly Logger $logger,
    ) {
    }

    public function load(): Cart
    {
        if (!$this->session->has(SessionKey::CART)) {
            return Cart::empty();
        }

        try {
            /** @var mixed $raw */
            $raw = $this->session->get(SessionKey::CART);

            return Cart::fromArray((array) $raw);
        } catch (InvalidCartPayloadException $e) {
            $this->logger->warning('Discarded unreadable cart', ['reason' => $e->getMessage()]);
            $this->session->remove(SessionKey::CART);

            return Cart::empty();
        }
    }

    public function save(Cart $cart): void
    {
        if ($cart->isEmpty()) {
            $this->session->remove(SessionKey::CART);

            return;
        }

        $this->session->set(SessionKey::CART, $cart->toArray());
    }
}
