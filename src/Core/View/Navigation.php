<?php

declare(strict_types=1);

namespace Core\View;

final class Navigation
{
    private const ROOT_PATH = '/';

    public static function pathOf(string $requestUri): string
    {
        return (string) parse_url($requestUri, PHP_URL_PATH);
    }

    public static function resolve(array $links, string $currentPath): array
    {
        $resolved = [];
        foreach ($links as $link) {
            $resolved[] = self::resolveLink($link, $currentPath);
        }

        return $resolved;
    }

    private static function resolveLink(array $link, string $currentPath): array
    {
        if (!array_key_exists('children', $link)) {
            $link['active'] = self::matches($link['href'], $currentPath);

            return $link;
        }

        $link['children'] = self::resolve($link['children'], $currentPath);
        $link['active'] = in_array(true, array_column($link['children'], 'active'), true);

        return $link;
    }

    private static function matches(string $href, string $currentPath): bool
    {
        if ($href === self::ROOT_PATH) {
            return $currentPath === self::ROOT_PATH;
        }

        return $currentPath === $href || str_starts_with($currentPath, rtrim($href, '/') . '/');
    }
}
