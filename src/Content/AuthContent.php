<?php

declare(strict_types=1);

namespace Content;

use Contracts\AuthContentInterface;
use Core\View\Content;

class AuthContent implements AuthContentInterface
{
    private readonly Content $content;

    public function __construct(array $data)
    {
        $this->content = new Content($data);
    }

    public function getLogin(): array
    {
        return $this->content->get('login');
    }

    public function getSignup(): array
    {
        return $this->content->get('signup');
    }

    public function getHeader(): array
    {
        return [
            'back' => $this->content->get('back'),
            'icon' => $this->content->get('icon'),
        ];
    }
}
