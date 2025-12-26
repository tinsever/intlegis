<?php
declare(strict_types=1);

require_once __DIR__ . '/app.php';
require_once __DIR__ . '/audit.php';


// requireAuth() removed from top to allow public viewing

$method = $_SERVER['REQUEST_METHOD'];
$path   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($path === '/countries') {
    $stmt = db()->query('SELECT * FROM countries ORDER BY name ASC');
    $countries = $stmt->fetchAll();
    render('countries/index', compact('countries'));
}

if (preg_match('#^/countries/(\d+)$#', $path, $m) && $method === 'GET') {
    $id = (int)$m[1];
    $stmt = db()->prepare('SELECT * FROM countries WHERE id = ?');
    $stmt->execute([$id]);
    $country = $stmt->fetch();
    if (!$country) { http_response_code(404); exit('Nicht gefunden'); }
    
    if (isHtmx()) {
        render('countries/_row', compact('country'));
    } else {
        redirect('/countries');
    }
}

if ($path === '/countries/store' && $method === 'POST') {
    requireAuth();
    requirePrimary();
    $name = trim($_POST['name'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $capital = trim($_POST['capital'] ?? '');

    if ($name === '') {
        http_response_code(422);
        echo 'Name ist erforderlich';
        exit;
    }

    $stmt = db()->prepare('INSERT INTO countries (name, full_name, capital) VALUES (?, ?, ?)');
    $stmt->execute([$name, $full_name, $capital]);
    $id = (int)db()->lastInsertId();

    $country = ['id' => $id, 'name' => $name, 'full_name' => $full_name, 'capital' => $capital];
    logAction('country', $id, 'create');
    createVersion('country', $id, $country);

    if (isHtmx()) {
        render('countries/_row', compact('country'));
    } else {
        redirect('/countries');
    }
}

if (preg_match('#^/countries/(\d+)/edit$#', $path, $m)) {
    requireAuth();
    requirePrimary();
    $id = (int)$m[1];
    $stmt = db()->prepare('SELECT * FROM countries WHERE id = ?');
    $stmt->execute([$id]);
    $country = $stmt->fetch();
    if (!$country) { http_response_code(404); exit('Nicht gefunden'); }
    render('countries/_form', compact('country'));
}

if (preg_match('#^/countries/(\d+)/update$#', $path, $m) && $method === 'POST') {
    requireAuth();
    requirePrimary();
    $id = (int)$m[1];
    $name = trim($_POST['name'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $capital = trim($_POST['capital'] ?? '');

    if ($name === '') {
        http_response_code(422);
        echo 'Name ist erforderlich';
        exit;
    }

    $stmt = db()->prepare('UPDATE countries SET name = ?, full_name = ?, capital = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
    $stmt->execute([$name, $full_name, $capital, $id]);

    $country = ['id' => $id, 'name' => $name, 'full_name' => $full_name, 'capital' => $capital];
    logAction('country', $id, 'update');
    createVersion('country', $id, $country);

    if (isHtmx()) {
        render('countries/_row', compact('country'));
    } else {
        redirect('/countries');
    }
}

if (preg_match('#^/countries/(\d+)/history$#', $path, $m)) {
    $id = (int)$m[1];
    $stmt = db()->prepare('SELECT * FROM countries WHERE id = ?');
    $stmt->execute([$id]);
    $country = $stmt->fetch();
    if (!$country) { http_response_code(404); exit('Nicht gefunden'); }

    $history = getEntityHistory('country', $id);
    render('countries/history', compact('country', 'history'));
}

if (preg_match('#^/countries/(\d+)/revert/(\d+)$#', $path, $m) && $method === 'POST') {
    requireAuth();
    requirePrimary();
    $id = (int)$m[1];
    $versionId = (int)$m[2];
    
    if (revertToVersion('country', $id, $versionId)) {
        flash('Staat erfolgreich wiederhergestellt.');
    } else {
        flash('Wiederherstellung des Staates fehlgeschlagen.');
    }
    redirect("/countries/$id/history");
}
