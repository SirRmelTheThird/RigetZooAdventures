<?php

declare(strict_types=1);

namespace Controllers;

use Core\Constants\RedirectKey;
use Core\Http\Request;
use Core\Http\Response;
use Core\Session\Session;
use Core\View\ViewRenderer;
use Services\Auth\AuthService;
use Services\Orders\OrderQueryService;

final class Home
{
    public function __construct(
        private readonly ViewRenderer $views,
        private readonly OrderQueryService $orders,
        private readonly AuthService $auth,
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

    public function profile(Request $request): Response
    {
        $customerId = Session::userId();
        $user = $this->auth->findAuthenticatedCustomer($customerId);

        if ($user === null) {
            $this->auth->invalidateSession();

            return Response::redirect(RedirectKey::LOGIN);
        }

        return $this->views->render('profile', [
            'user' => $user,
            'orders' => $this->orders->ordersFor($customerId),
        ]);
    }
}
