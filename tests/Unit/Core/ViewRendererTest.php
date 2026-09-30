<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use Core\View\ViewRenderer;
use LogicException;
use PHPUnit\Framework\TestCase;

final class ViewRendererTest extends TestCase
{
    private string $viewsDir;
    private ViewRenderer $views;

    protected function setUp(): void
    {
        $this->viewsDir = sys_get_temp_dir() . '/rza_views_' . bin2hex(random_bytes(4));
        mkdir($this->viewsDir . '/errors', 0777, true);
        file_put_contents($this->viewsDir . '/errors/404.php', '<p>404: <?= htmlspecialchars($message) ?></p>');
        file_put_contents($this->viewsDir . '/hello.php', 'Hello <?= $name ?>');
        $this->views = new ViewRenderer($this->viewsDir);
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->viewsDir . '/errors/*') ?: []);
        array_map('unlink', glob($this->viewsDir . '/*.php') ?: []);
        @rmdir($this->viewsDir . '/errors');
        @rmdir($this->viewsDir);
    }

    public function testExtractsDataIntoTheTemplate(): void
    {
        self::assertSame('Hello Ana', $this->views->render('hello', ['name' => 'Ana'])->body());
    }

    public function testInjectsSharedViewDataWhenProvided(): void
    {
        file_put_contents($this->viewsDir . '/shared_test.php', 'Cart: <?= isset($shared) ? $shared->cartCount() : "none" ?>');
        $session = new \Tests\Support\InMemorySessionStore(['cart_count' => 3]);
        $shared = new \Services\Views\SharedViewData($session);
        $renderer = new ViewRenderer($this->viewsDir, $shared);

        self::assertSame('Cart: 3', $renderer->render('shared_test')->body());
    }

    public function testDoesNotLeakOutputOnFailure(): void
    {
        $this->expectException(LogicException::class);
        $this->views->render('missing');
    }

    public function testLayoutEscapesDocumentTitle(): void
    {
        $views = new ViewRenderer(dirname(__DIR__, 3) . '/src/Views');

        $body = $views->render('layouts/layout-header-structure', [
            'documentTitle' => '<script>alert(1)</script>',
        ])->body();

        self::assertStringContainsString(
            '<title>&lt;script&gt;alert(1)&lt;/script&gt;</title>',
            $body,
        );
    }
}
