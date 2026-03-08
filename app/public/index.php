<?php

ob_start();
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Session;
use App\Controllers\AuthController;
use App\Controllers\DanceController;
use App\Controllers\EventDetailController;
use App\Controllers\ArtistController;
use App\Controllers\FoodController;
use App\Controllers\HomeController;
use App\Controllers\HistoryController;
use App\Exceptions\AppException;
use App\Exceptions\NotFoundException;

Session::start();

set_exception_handler(function (Throwable $e): void {
    $code    = 500;
    $message = 'An error occurred.';

    if ($e instanceof AppException) {
        $code    = $e->getHttpCode();
        $message = $e->getMessage();
    }

    http_response_code($code);
    require __DIR__ . '/../src/Views/error.php';
});

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (preg_match('#^/food/restaurant/(\d+)$#', $uri, $m)) {
    (new FoodController())->restaurant((int) $m[1]);
    exit;
}
if (preg_match('#^/dance/event/(\d+)$#', $uri, $m)) {
    (new EventDetailController())->show((int) $m[1]);
    exit;
}
if (preg_match('#^/dance/artist/([a-z0-9-]+)$#', $uri, $m)) {
    (new ArtistController())->show($m[1]);
    exit;
}

if (preg_match('#^/history/location/([a-z0-9-]+)$#', $uri, $m)) {
    (new HistoryController())->show($m[1]);
    exit;
}

switch ($uri) {
    case '/':
    case '/home':
        (new HomeController())->index();
        break;

    case '/dance':
        (new DanceController())->index();
        break;

    case '/food':
        (new FoodController())->index();
        break;

    case '/login':
        $c = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') $c->login();
        else $c->showLogin();
        break;

    case '/register':
        $c = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') $c->register();
        else $c->showRegister();
        break;

    case '/logout':
        (new AuthController())->logout();
        break;

    case '/history':
        (new HistoryController())->index();
        break;

    case '/history/locations':
        (new HistoryController())->locations();
        break;

    default:
        throw new NotFoundException('Page not found');
}
