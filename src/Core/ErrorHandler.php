<?php

declare(strict_types=1);

namespace Core;

use Closure;
use Core\Constants\SessionKey;
use Core\Logging\Logger;
use Exceptions\InvalidWebhookException;
use Exceptions\NotFoundException;
use Exceptions\UserFacingException;
use Exceptions\ValidationException;
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

        return $this->views->render(self::NOT_FOUND_VIEW, ['message' => $e->getMessage()], $e->status());
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

        if (!$this->views->exists(self::SERVER_ERROR_VIEW)) {
            return Response::html(self::FALLBACK_HTML, HttpStatus::InternalServerError);
        }

        return $this->views->render(
            self::SERVER_ERROR_VIEW,
            ['message' => Messages::SERVER_ERROR],
            HttpStatus::InternalServerError,
        );
    }

    private function withoutSensitive(array $body): array
    {
        return array_diff_key($body, array_flip(self::SENSITIVE_FIELDS));
    }
}
