<?php

declare(strict_types=1);

namespace Controllers;

use Core\Constants\RedirectKey;
use Core\Http\Request;
use Core\Http\Response;
use Core\Session\SessionStore;
use Core\View\ViewRenderer;
use Services\Auth\AuthService;
use Services\Orders\OrderQueryService;

final class Profile
{
    public function __construct(
        private readonly ViewRenderer $views,
        private readonly OrderQueryService $orders,
        private readonly AuthService $auth,
        private readonly SessionStore $session,
    ) {
    }

    public function profile(Request $request): Response
    {
        $customerId = $this->session->getUserId();
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
