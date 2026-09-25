<?php

declare(strict_types=1);

namespace Core;

final class Response
{
    private const CONTENT_TYPE_HTML = 'text/html; charset=UTF-8';
    private const CONTENT_TYPE_JSON = 'application/json';

    private function __construct(
        private readonly HttpStatus $status,
        private readonly array $headers,
        private readonly string $body,
    ) {
    }

    public static function html(string $body, HttpStatus $status = HttpStatus::Ok): self
    {
        return new self($status, ['Content-Type' => self::CONTENT_TYPE_HTML], $body);
    }

    public static function json(array $data, HttpStatus $status = HttpStatus::Ok): self
    {
        return new self($status, ['Content-Type' => self::CONTENT_TYPE_JSON], json_encode($data, JSON_THROW_ON_ERROR));
    }

    public static function redirect(string $location): self
    {
        return new self(HttpStatus::Found, ['Location' => $location], '');
    }

    public static function empty(HttpStatus $status): self
    {
        return new self($status, [], '');
    }

    public function status(): HttpStatus
    {
        return $this->status;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function body(): string
    {
        return $this->body;
    }

    public function send(): void
    {
        http_response_code($this->status->value);

        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        echo $this->body;
    }
}
