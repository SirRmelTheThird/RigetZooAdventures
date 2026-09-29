<?php

declare(strict_types=1);

namespace Services\Views;

use Core\Session\SessionStore;
use Cart\CartStore;

final class SharedViewData
{
    public function __construct(
        private readonly SessionStore $session,
        private readonly CartStore $cartStore,
    ) {
    }

    public function isLoggedIn(): bool
    {
        return $this->session->isLoggedIn();
    }

    public function username(): ?string
    {
        return $this->session->getUsername();
    }

    public function cartCount(): int
    {
        return count($this->cartStore->cart()->items());
    }
}
