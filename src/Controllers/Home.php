<?php

declare(strict_types=1);

namespace Controllers;

use Core\Http\Request;
use Core\Http\Response;
use Core\Session\SessionStore;
use Core\View\ViewRenderer;

final class Home
{
    public function __construct(
        private readonly ViewRenderer $views,
        private readonly SessionStore $session,
    ) {
    }

    public function index(Request $request): Response
    {
        return $this->views->render('home');
    }

    public function attractions(Request $request): Response
    {
        return $this->views->render('attractions');
    }

    public function educational(Request $request): Response
    {
        return $this->views->render('educational');
    }
}
