<?php

declare(strict_types=1);

namespace Core;

use Core\Constants\RedirectKey;
use LogicException;

final class Request
{
    private const METHOD_POST = 'POST';

    public function __construct(
        private readonly string $method,
        private readonly string $path,
        private readonly array $body = [],
        private readonly array $server = [],
        private readonly string $rawBody = '',
    ) {
    }

    public static function fromGlobals(): self
    {
        if (!array_key_exists('REQUEST_METHOD', $_SERVER) || !array_key_exists('REQUEST_URI', $_SERVER)) {
            throw new LogicException('Request::fromGlobals() needs a web SAPI; build a Request manually in CLI.');
        }

        $uri = (string) $_SERVER['REQUEST_URI'];
        $queryStart = strpos($uri, '?');

        if ($queryStart !== false) {
            $uri = substr($uri, 0, $queryStart);
        }

        return new self(
            (string) $_SERVER['REQUEST_METHOD'],
            $uri,
            $_POST,
            $_SERVER,
            (string) file_get_contents('php://input'),
        );
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        $path = rtrim($this->path, '/');

        if ($path === '') {
            return '/';
        }

        return $path;
    }

    public function isPost(): bool
    {
        return $this->method === self::METHOD_POST;
    }

    public function body(): array
    {
        return $this->body;
    }

    public function rawBody(): string
    {
        return $this->rawBody;
    }


    public function header(string $name): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));

        if (!array_key_exists($key, $this->server)) {
            return null;
        }

        return (string) $this->server[$key];
    }

    public function wantsJson(): bool
    {
        $requestedWith = $this->header('X-Requested-With');

        if ($requestedWith !== null && strtolower($requestedWith) === 'xmlhttprequest') {
            return true;
        }

        $accept = $this->header('Accept');

        if ($accept === null) {
            return false;
        }

        return str_contains($accept, 'application/json');
    }

    public function backUrl(): string
    {
        $referer = $this->header('Referer');
        $host = $this->header('Host');

        if ($referer === null || $host === null) {
            return RedirectKey::HOME;
        }

        $refererParts = parse_url($referer);
        $requestHost = parse_url('//' . $host, PHP_URL_HOST);

        if ($refererParts === false || !array_key_exists('host', $refererParts)) {
            return RedirectKey::HOME;
        }

        if ($refererParts['host'] !== $requestHost) {
            return RedirectKey::HOME;
        }

        $target = $this->pathAndQuery($refererParts);

        if (!str_starts_with($target, '/') || str_starts_with($target, '//') || str_contains($target, '\\')) {
            return RedirectKey::HOME;
        }

        return $target;
    }

    private function pathAndQuery(array $parts): string
    {
        $target = RedirectKey::HOME;

        if (array_key_exists('path', $parts)) {
            $target = $parts['path'];
        }

        if (array_key_exists('query', $parts)) {
            $target .= '?' . $parts['query'];
        }

        return $target;
    }
}
