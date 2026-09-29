<?php

declare(strict_types=1);

namespace Controllers;

use Core\Constants\RedirectKey;
use Core\Http\Request;
use Core\Http\Response;
use Core\Session\SessionStore;
use Core\View\ViewRenderer;
use Requests\Auth\LoginRequest;
use Requests\Auth\SignupRequest;
use Services\Auth\AuthService;
use Support\Messages;

final class Auth
{
    public function __construct(
        private readonly ViewRenderer $views,
        private readonly AuthService $auth,
        private readonly LoginRequest $loginRequest,
        private readonly SignupRequest $signupRequest,
        private readonly SessionStore $session,
    ) {
    }

    public function showLogin(Request $request): Response
    {
        return $this->views->render('auth/login');
    }

    public function login(Request $request): Response
    {
        $customer = $this->auth->authenticate($this->loginRequest->parse($request->body()));

        $this->session->signIn((string) $customer->id, (string) $customer->username, (string) $customer->first_name, (string) $customer->email);
        $this->session->flashSuccess(Messages::LOGGED_IN);
        return Response::redirect(RedirectKey::HOME);
    }

    public function showSignup(Request $request): Response
    {
        return $this->views->render('auth/signup');
    }

    public function signup(Request $request): Response
    {
        $this->auth->register($this->signupRequest->parse($request->body()));
        $this->session->flashSuccess(Messages::ACCOUNT_CREATED);
        return Response::redirect(RedirectKey::LOGIN);
    }

    public function logout(Request $request): Response
    {
        $this->session->invalidate();
        $this->session->flashSuccess(Messages::LOGGED_OUT);
        return Response::redirect(RedirectKey::HOME);
    }
}
