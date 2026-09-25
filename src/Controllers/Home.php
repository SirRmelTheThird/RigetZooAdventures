<?php

declare(strict_types=1);

namespace Controllers;

use Core\Constants\RedirectKey;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\ViewRenderer;
use Models\Customer;
use Services\OrderQueryService;

final class Home
{
    public function __construct(
        private readonly ViewRenderer $views,
        private readonly OrderQueryService $orders,
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
        $user = Customer::with('rewardPoints')->find($customerId);

        if ($user === null) {
            Session::invalidate();

            return Response::redirect(RedirectKey::LOGIN);
        }

        return $this->views->render('profile', [
            'user' => $user,
            'orders' => $this->orders->ordersFor($customerId),
        ]);
    }
}
