<?php
declare(strict_types=1);

/**
 * Front controller for the "I'll Get This One" API.
 * All routes under base_path land here (see .htaccess).
 */

$root = dirname(__DIR__);
spl_autoload_register(static function (string $class) use ($root): void {
    foreach (['/lib/', '/controllers/'] as $dir) {
        $path = $root . $dir . $class . '.php';
        if (is_file($path)) {
            require $path;
            return;
        }
    }
});

Config::load();
Http::cors();

// Turn any uncaught error into a clean JSON 500 (never leak stack traces).
set_exception_handler(static function (Throwable $e): void {
    error_log('[iggt] ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    Http::error(500, 'server_error', 'Something went wrong on the server.');
});

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Compute the path relative to the configured base_path.
$uri  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$base = rtrim((string)Config::get('base_path', ''), '/');
if ($base !== '' && str_starts_with($uri, $base)) {
    $uri = substr($uri, strlen($base));
}
$path  = '/' . trim($uri, '/');
$parts = $path === '/' ? [] : explode('/', trim($path, '/'));

// ------------------------------------------------------------------ routing
// Health check
if ($path === '/' || $path === '/health') {
    Http::ok(['service' => 'ill-get-this-one', 'status' => 'up']);
}

// /auth/*
if (($parts[0] ?? '') === 'auth') {
    $action = $parts[1] ?? '';
    if ($method === 'POST' && $action === 'register') AuthController::register();
    if ($method === 'POST' && $action === 'login')    AuthController::login();
    if ($method === 'POST' && $action === 'logout')   AuthController::logout();
    Http::error(404, 'not_found', 'Unknown auth route.');
}

// /me
if ($path === '/me' && $method === 'GET') {
    AuthController::me();
}

// /groups ...
if (($parts[0] ?? '') === 'groups') {
    // /groups
    if (!isset($parts[1])) {
        if ($method === 'GET')  GroupController::mine();
        if ($method === 'POST') GroupController::create();
        Http::error(405, 'method_not_allowed', 'Use GET or POST on /groups.');
    }

    // /groups/join
    if ($parts[1] === 'join' && $method === 'POST') {
        GroupController::join();
    }

    // /groups/{id}...
    $gid = (int)$parts[1];
    if ($gid <= 0) {
        Http::error(404, 'not_found', 'Unknown group route.');
    }

    // /groups/{id}
    if (!isset($parts[2])) {
        if ($method === 'GET') GroupController::show($gid);
        Http::error(405, 'method_not_allowed', 'Use GET on /groups/{id}.');
    }

    // /groups/{id}/events...
    if ($parts[2] === 'events') {
        if (!isset($parts[3])) {
            if ($method === 'GET')  EventController::index($gid);
            if ($method === 'POST') EventController::create($gid);
            Http::error(405, 'method_not_allowed', 'Use GET or POST on /groups/{id}/events.');
        }
        $eid = (int)$parts[3];
        if ($eid > 0 && $method === 'DELETE') {
            EventController::delete($gid, $eid);
        }
    }
}

Http::error(404, 'not_found', 'No route matches ' . $method . ' ' . $path . '.');
