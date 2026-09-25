<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use Core\ViewRenderer;
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

    public function testDoesNotLeakOutputOnFailure(): void
    {
        $this->expectException(LogicException::class);
        $this->views->render('missing');
    }
}
