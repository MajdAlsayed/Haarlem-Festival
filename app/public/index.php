<?php
session_start();

ob_start();
require_once __DIR__ . '/../vendor/autoload.php';
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$uri = rtrim($uri, '/');
if ($uri === '') $uri = '/';
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
use App\Controllers\StoriesController;

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
$uri = rtrim((string) $uri, '/');
if ($uri === '') $uri = '/';

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
        if ($method === 'GET') (new HomeController())->index();
        else http_response_code(405);
        break;

    case '/stories':
        if ($method === 'GET') (new StoriesController())->index();
        else http_response_code(405);
        break;

    case '/stories/venue':
        if ($method === 'GET') (new StoriesController())->venue();
        else http_response_code(405);
        break;

    case '/stories/detail':
        if ($method === 'GET') (new StoriesController())->detail();
        else http_response_code(405);
        break;

    case '/cart':
        if ($method === 'GET') (new CartController())->get();
        else http_response_code(405);
        break;

    case '/cart/add':
        if ($method === 'POST') (new CartController())->add();
        else http_response_code(405);
        break;

    case '/cart/remove':
        if ($method === 'POST') (new CartController())->remove();
        else http_response_code(405);
        break;

    case '/cart/update':
        if ($method === 'POST') (new CartController())->update();
        else http_response_code(405);
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

    case '/reset-password':
        $c = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') $c->resetPassword();
        else $c->showResetPassword();
        break;

        case '/forgot-password':
        $c = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {$c->forgotPassword();
        } else $c->showForgotPassword();
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

    case '/history/tours':
        (new HistoryController())->tours();
        break;

    case '/history/tours/schedule':
        (new HistoryController())->toursSchedule();
        break;

    default:
        http_response_code(404);
        echo 'Page not found';
        break;
}