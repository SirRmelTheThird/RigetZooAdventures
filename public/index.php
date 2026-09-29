<?php

declare(strict_types=1);

use Bootstrap\Container;
use Config\Config;
use Core\Http\Request;
use Core\Session\Session;

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/config/Bootstrap.php';

Config::load();
ini_set('display_errors', Config::shouldDisplayErrors() ? '1' : '0');
error_reporting(Config::shouldDisplayErrors() ? E_ALL : E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

Session::start();

$root = dirname(__DIR__);
$container = new Container($root);
$router = $container->router();

$registerRoutes = require $root . '/routes.php';
$registerRoutes($router);

$request = Request::fromGlobals();

$container->errorHandler()
    ->handle(static fn () => $router->dispatch($request), $request)
    ->send();
