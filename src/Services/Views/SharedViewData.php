<?php

declare(strict_types=1);

namespace Services\Views;

use Core\Constants\SessionKey;
use Core\Session\SessionStore;
use Core\View\OldInput;

final class SharedViewData
{
    public function __construct(
        private readonly SessionStore $session
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
        return $this->session->getCartCount();
    }

    public function getFlash(string $key, mixed $default = null): mixed
    {
        return $this->session->getFlash($key, $default);
    }

    public function getSuccessFlash(): ?string
    {
        $val = $this->session->getFlash(SessionKey::SUCCESS);

        return is_string($val) ? $val : null;
    }

    public function getErrorFlash(): ?string
    {
        $val = $this->session->getFlash(SessionKey::ERROR);

        return is_string($val) ? $val : null;
    }

    /** @return array<string>|null */
    public function getValidationErrors(): ?array
    {
        $val = $this->session->getFlash(SessionKey::VALIDATION_ERRORS);

        return is_array($val) ? $val : null;
    }

    public function oldInput(): OldInput
    {
        return OldInput::fromFlash($this->session->getFlash(SessionKey::FORM_DATA));
    }
}
