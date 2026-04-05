<?php
session_start();

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
use App\Controllers\JazzController;
use App\Controllers\StoriesController;
use App\Controllers\CartController;
use App\Controllers\AdminStoriesController;

$uri    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$uri = rtrim($uri, '/');
if ($uri === '') {
    $uri = '/';
}

Session::start();

set_exception_handler(function (Throwable $e): void {
    if (ob_get_level()) {
        ob_end_clean();
    }
    http_response_code(500);
    echo '<pre style="color:white;background:#111;padding:20px;white-space:pre-wrap;">';
    echo 'Message: ' . $e->getMessage() . "\n\n";
    echo 'File: '    . $e->getFile()    . "\n";
    echo 'Line: '    . $e->getLine()    . "\n\n";
    echo $e->getTraceAsString();
    echo '</pre>';
    exit;
});

/* ── Dynamic regex routes ── */

if (preg_match('#^/food/restaurant/(\d+)$#', $uri, $m)) {
    (new FoodController())->restaurant((int)$m[1]);
    exit;
}

if (preg_match('#^/dance/event/(\d+)$#', $uri, $m)) {
    (new EventDetailController())->show((int)$m[1]);
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

/* ── Switch router ── */

switch ($uri) {

    /* ── Home ── */
    case '/':
    case '/home':
        if ($method === 'GET') { (new HomeController())->index(); }
        else { http_response_code(405); }
        break;

    /* ── Stories (public) ── */
    case '/stories':
        if ($method === 'GET') { (new StoriesController())->index(); }
        else { http_response_code(405); }
        break;

    case '/stories/venue':
        if ($method === 'GET') { (new StoriesController())->venue(); }
        else { http_response_code(405); }
        break;

    case '/stories/detail':
        if ($method === 'GET') { (new StoriesController())->detail(); }
        else { http_response_code(405); }
        break;

    /* ── Stories JSON API  (Lecture 6 requirement) ── */
    case '/api/stories':
        if ($method === 'GET') { (new StoriesController())->apiStories(); }
        else { http_response_code(405); }
        break;

    /* ── Cart ── */
    case '/cart':
        if ($method === 'GET') { (new CartController())->get(); }
        else { http_response_code(405); }
        break;

    case '/cart/add':
        if ($method === 'POST') { (new CartController())->add(); }
        else { http_response_code(405); }
        break;

    case '/cart/remove':
        if ($method === 'POST') { (new CartController())->remove(); }
        else { http_response_code(405); }
        break;

    case '/cart/update':
        if ($method === 'POST') { (new CartController())->update(); }
        else { http_response_code(405); }
        break;

    /* ── Dance ── */
    case '/dance':
        (new DanceController())->index();
        break;

    /* ── Food ── */
    case '/food':
        (new FoodController())->index();
        break;

    /* ── Auth ── */
    case '/login':
        $c = new AuthController();
        if ($method === 'POST') { $c->login(); } else { $c->showLogin(); }
        break;

    case '/register':
        $c = new AuthController();
        if ($method === 'POST') { $c->register(); } else { $c->showRegister(); }
        break;

    case '/reset-password':
        $c = new AuthController();
        if ($method === 'POST') { $c->resetPassword(); } else { $c->showResetPassword(); }
        break;

    case '/forgot-password':
        $c = new AuthController();
        if ($method === 'POST') { $c->forgotPassword(); } else { $c->showForgotPassword(); }
        break;

    case '/logout':
        (new AuthController())->logout();
        break;

    /* ── History ── */
    case '/history':
        (new HistoryController())->index();
        break;

    case '/history/locations':
        (new HistoryController())->locations();
        break;

    /* ── Jazz ── */
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

    /* ── CMS Stories ── */
    case '/cms/stories':
        if ($method === 'GET') { (new AdminStoriesController())->index(); }
        else { http_response_code(405); }
        break;

    case '/cms/stories/edit':
        if ($method === 'GET') { (new AdminStoriesController())->edit(); }
        else { http_response_code(405); }
        break;

    case '/cms/stories/update':
        if ($method === 'POST') { (new AdminStoriesController())->update(); }
        else { http_response_code(405); }
        break;

    /*
     * DELETE — must be POST (Lecture 1: destructive actions must never be GET).
     * The CMS Index sends a small POST form with a confirm dialog instead of a GET link.
     */
    case '/cms/stories/delete':
        if ($method === 'POST') { (new AdminStoriesController())->delete(); }
        else { http_response_code(405); }
        break;

    case '/cms/stories/detail-page':
        if ($method === 'GET') { (new AdminStoriesController())->editDetailPage(); }
        else { http_response_code(405); }
        break;

    case '/cms/stories/detail-page/save':
        if ($method === 'POST') { (new AdminStoriesController())->saveDetailPage(); }
        else { http_response_code(405); }
        break;

    default:
        http_response_code(404);
        echo 'Page not found';
        break;
}