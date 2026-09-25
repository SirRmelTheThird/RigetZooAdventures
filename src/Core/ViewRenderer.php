<?php

declare(strict_types=1);

namespace Core;

use LogicException;
use Throwable;

final class ViewRenderer
{
    public function __construct(
        private readonly string $viewsPath,
    ) {
    }

    public function exists(string $view): bool
    {
        return is_file($this->path($view));
    }

    public function render(string $view, array $data = [], HttpStatus $status = HttpStatus::Ok): Response
    {
        $path = $this->path($view);

        if (!is_file($path)) {
            throw new LogicException("View not found: {$view}");
        }

        return Response::html(self::capture($path, $data), $status);
    }

    private function path(string $view): string
    {
        return "{$this->viewsPath}/{$view}.php";
    }

    private static function capture(string $viewPath, array $viewData): string
    {
        ob_start();

        try {
            extract($viewData, EXTR_SKIP);
            require $viewPath;
        } catch (Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        return (string) ob_get_clean();
    }
}
