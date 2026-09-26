<?php

declare(strict_types=1);

namespace Core\View;

use App\Factories\ContentFactory;

final class View
{
    private const PARTIALS_DIR = 'partials';
    private const CONTENT_DIR = 'content';

    private static ?string $root = null;
    private static array $contentCache = [];

    public static function bind(string $viewsRoot): void
    {
        self::$root = rtrim($viewsRoot, '/\\');
    }

    public static function partial(string $name, array $props = []): void
    {
        $file = self::resolve(self::PARTIALS_DIR, $name);
        if (!is_file($file)) {
            throw ViewException::partialNotFound($name);
        }

        (static function (string $partialFile, array $partialProps): void {
            extract($partialProps, EXTR_SKIP);
            require $partialFile;
        })($file, $props);
    }

    public static function content(string $interface): object
    {
        if (array_key_exists($interface, self::$contentCache)) {
            return self::$contentCache[$interface];
        }

        $name = ContentFactory::fileFor($interface);
        $file = self::resolve(self::CONTENT_DIR, $name);

        if (!is_file($file)) {
            throw ViewException::contentNotFound($name);
        }

        $content = require $file;

        if (!is_array($content)) {
            throw ViewException::invalidContent($name);
        }

        $objectContent = ContentFactory::create($interface, $content);

        self::$contentCache[$interface] = $objectContent;

        return $objectContent;
}

    private static function resolve(string $directory, string $name): string
    {
        if (self::$root === null) {
            throw ViewException::notBound();
        }

        return sprintf('%s/%s/%s.php', self::$root, $directory, $name);
    }
}
