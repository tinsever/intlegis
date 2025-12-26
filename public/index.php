<?php
declare(strict_types=1);

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Base path detection for subfolders
$scriptName = $_SERVER['SCRIPT_NAME']; // e.g. /intlegis/index.php
$basePath = str_replace('/public/index.php', '', $scriptName);
$basePath = str_replace('/index.php', '', $basePath);

if ($basePath !== '' && strpos($path, $basePath) === 0) {
    $path = substr($path, strlen($basePath));
}
if ($path === '' || $path === '/') $path = '/';

// Installer Check
if (!file_exists(__DIR__ . '/../config.php')) {
    if (strpos($path, '/install') !== 0) {
        header('Location: ' . ($basePath ?: '') . '/install');
        exit;
    }
    session_start();
    require __DIR__ . '/../src/install.php';
    exit;
}

require_once __DIR__ . '/../src/app.php';

// Homepage
if ($path === '/') {
    require __DIR__ . '/../src/home.php';
    exit;
}

// Export Route
if ($path === '/export') {
    require __DIR__ . '/../src/export.php';
    handleExport();
    exit;
}

// Global Search
if ($path === '/search') {
    require __DIR__ . '/../src/search.php';
    exit;
}

// Replication API
if ($path === '/api/sync') {
    require __DIR__ . '/../src/sync.php';
    handleSyncExport();
    exit;
}

// Replication Trigger (Local)
if ($path === '/api/pull-sync') {
    require __DIR__ . '/../src/sync.php';
    $result = pullUpdates();
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}

// Entity Search API
if ($path === '/api/search-entities') {
    require __DIR__ . '/../src/api.php';
    exit;
}

// Auth Routes
if (in_array($path, ['/login', '/register', '/logout'])) {
    require __DIR__ . '/../src/auth.php';
    exit;
}

// Country Routes
if ($path === '/countries' || preg_match('#^/countries/(\d+)(|/edit|/update|/history|/revert/(\d+))$#', $path) || $path === '/countries/store') {
    if (!config('modules.treaties.enabled', true)) {
        http_response_code(404);
        render('404', ['title' => 'Not Found']);
        exit;
    }
    require __DIR__ . '/../src/countries.php';
    exit;
}

// Local Law Routes
if ($path === '/laws' || $path === '/laws/create' || $path === '/laws/store' || preg_match('#^/laws/(\d+)(|/edit|/update|/delete)$#', $path)) {
    if (!config('modules.laws.enabled', true)) {
        http_response_code(404);
        render('404', ['title' => 'Not Found']);
        exit;
    }
    require __DIR__ . '/../src/laws.php';
    exit;
}

// Treaty Routes
if ($path === '/' || $path === '/treaties' || $path === '/treaties/create' || $path === '/treaties/store' || $path === '/treaties/search' || preg_match('#^/treaties/(\d+)(|/edit|/update|/history|/revert/(\d+))$#', $path)) {
    if ($path !== '/' && !config('modules.treaties.enabled', true)) {
        http_response_code(404);
        render('404', ['title' => 'Not Found']);
        exit;
    }
    require __DIR__ . '/../src/treaties.php';
    exit;
}

// 404
http_response_code(404);
render('404', ['title' => 'Not Found']);
