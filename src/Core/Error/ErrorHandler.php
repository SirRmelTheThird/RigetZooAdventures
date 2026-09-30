<?php

declare(strict_types=1);

namespace Core\Error;

use Closure;
use Core\Constants\SessionKey;
use Core\Http\HttpStatus;
use Core\Http\Request;
use Core\Http\Response;
use Core\Logging\Logger;
use Core\Security\CSRF;
use Core\Session\Session;
use Core\View\ViewRenderer;
use Exceptions\Payment\InvalidWebhookException;
use Exceptions\Http\NotFoundException;
use Exceptions\System\UserFacingException;
use Exceptions\Validation\ValidationException;
use Support\Messages;
use Throwable;

final class ErrorHandler
{
    private const NOT_FOUND_VIEW = 'errors/404';
    private const SERVER_ERROR_VIEW = 'errors/500';
    private const FALLBACK_HTML = '<!doctype html><title>Error</title><h1>Something went wrong</h1><p>Please try again.</p>';

    private const SENSITIVE_FIELDS = ['password', 'confirm_password', CSRF::FIELD_NAME];

    public function __construct(
        private readonly ViewRenderer $views,
        private readonly Logger $logger,
    ) {
    }

    public function handle(Closure $action, Request $request): Response
    {
        try {
            return $action();
        } catch (ValidationException $e) {
            return $this->invalidInput($e, $request);
        } catch (InvalidWebhookException) {
            return Response::empty(HttpStatus::BadRequest);
        } catch (NotFoundException $e) {
            return $this->notFound($e, $request);
        } catch (UserFacingException $e) {
            return $this->rejected($e, $request);
        } catch (Throwable $e) {
            return $this->crashed($e, $request);
        }
    }

    private function invalidInput(ValidationException $e, Request $request): Response
    {
        if ($request->wantsJson()) {
            return Response::json(['errors' => $e->errors()], $e->status());
        }

        Session::flash(SessionKey::VALIDATION_ERRORS, $e->errors());
        Session::flash(SessionKey::FORM_DATA, $this->withoutSensitive($request->body()));

        return Response::redirect($request->backUrl());
    }

    private function notFound(NotFoundException $e, Request $request): Response
    {
        if ($request->wantsJson()) {
            return Response::json(['error' => $e->getMessage()], $e->status());
        }

        return $this->renderView(self::NOT_FOUND_VIEW, ['message' => $e->getMessage()], $e->status());
    }

    private function rejected(UserFacingException $e, Request $request): Response
    {
        if ($request->wantsJson()) {
            return Response::json(['error' => $e->getMessage()], $e->status());
        }

        Session::flashError($e->getMessage());

        $target = $e->redirectTo();

        if ($target === null) {
            $target = $request->backUrl();
        }

        return Response::redirect($target);
    }

    private function crashed(Throwable $e, Request $request): Response
    {
        $this->logger->exception($e, ['method' => $request->method(), 'path' => $request->path()]);

        if ($request->wantsJson()) {
            return Response::json(['error' => Messages::SERVER_ERROR], HttpStatus::InternalServerError);
        }

        return $this->renderView(
            self::SERVER_ERROR_VIEW,
            ['message' => Messages::SERVER_ERROR],
            HttpStatus::InternalServerError,
        );
    }

    /** @param array<string, mixed> $data */
    private function renderView(string $view, array $data, HttpStatus $status): Response
    {
        if (!$this->views->exists($view)) {
            return Response::html(self::FALLBACK_HTML, $status);
        }

        try {
            return $this->views->render($view, $data, $status);
        } catch (Throwable $e) {
            $this->logger->exception($e, ['view' => $view]);

            return Response::html(self::FALLBACK_HTML, $status);
        }
    }

    /**
     * @param array<string|int, mixed> $body
     * @return array<string|int, mixed>
     */
    private function withoutSensitive(array $body): array
    {
        $clean = [];

        foreach ($body as $key => $value) {
            if (in_array($key, self::SENSITIVE_FIELDS, true)) {
                continue;
            }

            if (is_array($value)) {
                $clean[$key] = $this->withoutSensitive($value);
                continue;
            }

            $clean[$key] = $value;
        }

        return $clean;
    }
}
