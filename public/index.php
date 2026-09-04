<?php

require_once __DIR__ . '/../vendor/autoload.php';

spl_autoload_register(function ($class) {
    $file = __DIR__ . '/../src/' . str_replace('\\', '/', $class) . '.php';

    if (file_exists($file)) {
        require_once $file;
        return true;
    }

    $configFile = __DIR__ . '/../config/' . str_replace('Config\\', '', $class) . '.php';

    if (file_exists($configFile)) {
        require_once $configFile;
        return true;
    }

    return false;
});

\Config\Config::load();
require_once __DIR__ . '/../config/bootstrap.php';

if (\Config\Config::isDebug()) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

\Core\Session::start();

\Core\EventDispatcher::registerDefaults();

$router = new \Core\Router();

$router->get('/', 'Home@index');

$router->get('/login', 'Auth@showLogin');
$router->post('/login', 'Auth@login', ['CSRFMiddleware']);
$router->get('/signup', 'Auth@showSignup');
$router->post('/signup', 'Auth@signup', ['CSRFMiddleware']);
$router->get('/logout', 'Auth@logout', ['AuthMiddleware']);

$router->get('/profile', 'Home@profile', ['AuthMiddleware']);

$router->get('/tickets', 'Ticket@index');
$router->get('/tickets/standard', 'Ticket@showStandard');
$router->get('/tickets/premium', 'Ticket@showPremium');
$router->post('/tickets/standard', 'Ticket@addStandard', ['CSRFMiddleware']);
$router->post('/tickets/premium', 'Ticket@addPremium', ['CSRFMiddleware']);

$router->get('/accommodations', 'AccommodationCtrl@index');
$router->post('/accommodations/add', 'AccommodationCtrl@addToCart', ['CSRFMiddleware']);

$router->get('/cart', 'Cart@index');
$router->post('/cart/remove', 'Cart@removeItem', ['CSRFMiddleware', 'AuthMiddleware']);
$router->post('/cart/clear', 'Cart@clear', ['CSRFMiddleware', 'AuthMiddleware']);

$router->get('/checkout', 'Payment@checkout', ['AuthMiddleware']);
$router->post('/payment/process', 'Payment@process', ['CSRFMiddleware', 'AuthMiddleware']);
$router->post('/stripe/webhook', 'Payment@webhook');

$router->get('/attractions', function() {
    \Core\Response::view('attractions');
});

$router->get('/educational', function() {
    \Core\Response::view('educational');
});

// Global exception handler
try {
    $router->dispatch();
} catch (\Exceptions\NotFoundException $e) {
    \Core\Logger::warning('404 Not Found', [
        'uri' => \Core\Request::uri(),
        'message' => $e->getMessage()
    ]);
    \Core\Response::setStatusCode(404);
    \Core\Response::view('errors/404', ['message' => $e->getMessage()]);
} catch (\Exceptions\ValidationException $e) {
    \Core\Logger::info('Validation failed', ['errors' => $e->getErrors()]);
    \Core\Response::json(['errors' => $e->getErrors()], 422);
} catch (\Exceptions\PaymentException $e) {
    \Core\Logger::error('Payment exception', ['message' => $e->getMessage()]);
    \Core\Session::flash('error', $e->getMessage());
    \Core\Response::redirect('/checkout');
} catch (\Exception $e) {
    \Core\Logger::exception($e, [
        'uri' => \Core\Request::uri(),
        'method' => \Core\Request::method()
    ]);

    if (\Config\Config::isDebug()) {
        throw $e; 
    }

    \Core\Response::setStatusCode(500);
    \Core\Response::view('errors/500');
}

