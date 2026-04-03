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
use App\Controllers\JazzController;
use App\Exceptions\AppException;
use App\Exceptions\NotFoundException;
use App\Controllers\StoriesController;
use App\Controllers\AdminController;
use App\Controllers\AdminJazzController;
use App\Controllers\AdminTicketsController;
use App\Controllers\AdminOrderExportController;
use App\Controllers\AdminOrdersController;
use App\Controllers\AdminHomepageController;
use App\Controllers\AdminCmsUploadController;
use App\Controllers\AdminDanceController;
use App\Controllers\CartController;
use App\Controllers\TicketsController;
use App\Core\SecurityHeaders;

Session::start();
SecurityHeaders::send();

set_exception_handler(function (Throwable $e): void {
    $code    = 500;
    $message = 'An error occurred.';

    if ($e instanceof AppException) {
        $code    = $e->getHttpCode();
        $message = $e->getMessage();
    }

    if (ob_get_level()) {
        ob_end_clean();
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
        if ($method === 'GET') {
            (new CartController())->get();
        } else {
            http_response_code(405);
        }
        break;

    case '/cart/add':
        if ($method === 'POST') {
            (new CartController())->add();
        } else {
            http_response_code(405);
        }
        break;

    case '/cart/remove':
        if ($method === 'POST') {
            (new CartController())->remove();
        } else {
            http_response_code(405);
        }
        break;

    case '/cart/update':
        if ($method === 'POST') {
            (new CartController())->update();
        } else {
            http_response_code(405);
        }
        break;

    case '/dance':
        (new DanceController())->index();
        break;

    case '/food':
        (new FoodController())->index();
        break;

    case '/tickets':
        if ($method === 'GET') (new TicketsController())->index();
        else http_response_code(405);
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
        if ($_SERVER['REQUEST_METHOD'] === 'POST') $c->forgotPassword();
        else $c->showForgotPassword();
        break;

    case '/logout':
        (new AuthController())->logout();
        break;

    case '/admin/orders/export':
        if ($method === 'GET' || $method === 'POST') {
            (new AdminOrderExportController())->handle();
        } else {
            http_response_code(405);
        }
        break;

    case '/admin/orders':
        if ($method === 'GET') {
            (new AdminOrdersController())->index();
        } else {
            http_response_code(405);
        }
        break;

    case '/admin/cms/homepage':
        $cms = new AdminHomepageController();
        if ($method === 'GET') {
            $cms->showForm();
        } elseif ($method === 'POST') {
            $cms->save();
        } else {
            http_response_code(405);
        }
        break;

    case '/admin/cms/dance':
        $danceCms = new AdminDanceController();
        if ($method === 'GET') {
            $danceCms->showForm();
        } elseif ($method === 'POST') {
            $danceCms->save();
        } else {
            http_response_code(405);
        }
        break;

    case '/admin/cms/upload':
        if ($method === 'POST') {
            (new AdminCmsUploadController())->handle();
        } else {
            http_response_code(405);
        }
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

    case '/jazz':
    case '/jazz/':
        (new JazzController())->index();
        break;

    case '/jazz/gumbo-kings':
        (new JazzController())->gumboKings();
        break;

    case '/jazz/karsu':
        (new JazzController())->karsu();
        break;

    case '/jazz/gare-du-nord':
        (new JazzController())->gareDuNord();
        break;

    case '/admin':
        (new AdminController())->index();
        break;

    case '/admin/pages':
        (new AdminController())->pages();
        break;

    case '/admin/pages/edit':
        (new AdminController())->editPage();
        break;

    case '/admin/pages/update':
        if ($method === 'POST') (new AdminController())->updatePage();
        else { header('Location: /admin/pages'); exit; }
        break;

    case '/admin/jazz':
        (new AdminJazzController())->index();
        break;

    case '/admin/jazz/events':
        (new AdminJazzController())->events();
        break;

    case '/admin/jazz/events/new':
        (new AdminJazzController())->newEvent();
        break;

    case '/admin/jazz/events/edit':
        (new AdminJazzController())->editEvent();
        break;

    case '/admin/jazz/events/save':
        if ($method === 'POST') (new AdminJazzController())->saveEvent();
        else { header('Location: /admin/jazz/events'); exit; }
        break;

    case '/admin/jazz/events/delete':
        if ($method === 'POST') (new AdminJazzController())->deleteEvent();
        else { header('Location: /admin/jazz/events'); exit; }
        break;

    case '/admin/jazz/settings':
        (new AdminJazzController())->settings();
        break;

    case '/admin/jazz/discography':
        (new AdminJazzController())->discography();
        break;

    case '/admin/jazz/discography/edit':
        (new AdminJazzController())->editDiscTrack();
        break;

    case '/admin/jazz/discography/save':
        if ($method === 'POST') (new AdminJazzController())->saveDiscTrack();
        else { header('Location: /admin/jazz/discography'); exit; }
        break;

    case '/admin/jazz/discography/delete':
        if ($method === 'POST') (new AdminJazzController())->deleteDiscTrack();
        else { header('Location: /admin/jazz/discography'); exit; }
        break;

    case '/admin/tickets':
        (new AdminTicketsController())->index();
        break;

    case '/admin/tickets/settings':
        (new AdminTicketsController())->settings();
        break;

    case '/admin/tickets/edit':
        (new AdminTicketsController())->edit();
        break;

    case '/admin/tickets/new':
        (new AdminTicketsController())->newTicket();
        break;

    case '/admin/tickets/save':
        if ($method === 'POST') (new AdminTicketsController())->save();
        else { header('Location: /admin/tickets'); exit; }
        break;

    case '/admin/tickets/delete':
        if ($method === 'POST') (new AdminTicketsController())->delete();
        else { header('Location: /admin/tickets'); exit; }
        break;

    default:
        http_response_code(404);
        echo 'Page not found';
        break;
}