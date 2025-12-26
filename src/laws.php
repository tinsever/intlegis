<?php
declare(strict_types=1);

require_once __DIR__ . '/app.php';
require_once __DIR__ . '/audit.php';

$method = $_SERVER['REQUEST_METHOD'];
$path   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($path === '/laws') {
    $db = db();
    $params = [];
    $where = [];
    
    $selectedCategory = $_GET['category'] ?? '';
    $selectedYear = $_GET['year'] ?? '';
    $q = trim($_GET['q'] ?? '');
    
    if ($selectedCategory !== '') {
        $where[] = '(category = ? OR category LIKE ?)';
        $params[] = $selectedCategory;
        $params[] = $selectedCategory . ' > %';
    }
    
    if ($selectedYear !== '') {
        $where[] = "strftime('%Y', COALESCE(effective_date, created_at)) = ?";
        $params[] = $selectedYear;
    }

    if ($q !== '') {
        $searchTerm = "%$q%";
        $where[] = '(title LIKE ? OR law_number LIKE ? OR content_html LIKE ?)';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    $stmt = $db->prepare("SELECT * FROM local_laws $whereSql ORDER BY created_at DESC");
    $stmt->execute($params);
    $laws = $stmt->fetchAll();
    
    // Get all unique years for the filter
    $years = $db->query("SELECT DISTINCT strftime('%Y', COALESCE(effective_date, created_at)) as year FROM local_laws ORDER BY year DESC")->fetchAll(PDO::FETCH_COLUMN);
    
    render('laws/index', compact('laws', 'selectedCategory', 'selectedYear', 'years', 'q'));
}

if ($path === '/laws/create') {
    requireAuth();
    render('laws/create');
}

if ($path === '/laws/store' && $method === 'POST') {
    requireAuth();
    $db = db();
    
    $title = trim($_POST['title'] ?? '');
    $law_number = trim($_POST['law_number'] ?? '');
    $category = $_POST['category'] ?? '';
    $publication_date = $_POST['publication_date'] ?: null;
    $effective_date = $_POST['effective_date'] ?: null;
    $status = $_POST['status'] ?? 'active';
    $content_html = $_POST['content_html'] ?? '';
    $content_json = $_POST['content_json'] ?? '';

    $stmt = $db->prepare('INSERT INTO local_laws (title, law_number, category, publication_date, effective_date, status, content_html, content_json) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$title, $law_number, $category, $publication_date, $effective_date, $status, $content_html, $content_json]);
    $id = (int)$db->lastInsertId();

    // Handle Amendments
    if (isset($_POST['amendments']) && is_array($_POST['amendments'])) {
        $stmtAmends = $db->prepare('INSERT INTO entity_amendments (amending_entity_type, amending_entity_id, amended_entity_type, amended_entity_id) VALUES ("local_law", ?, ?, ?)');
        foreach ($_POST['amendments'] as $amendment) {
            if (strpos($amendment, ':') !== false) {
                list($type, $targetId) = explode(':', $amendment);
                $stmtAmends->execute([$id, $type, (int)$targetId]);
            }
        }
    }

    // Handle Links
    if (isset($_POST['links']) && is_array($_POST['links'])) {
        $stmtLinks = $db->prepare('INSERT INTO related_links (entity_type, entity_id, url, label) VALUES ("local_law", ?, ?, ?)');
        foreach ($_POST['links'] as $link) {
            if (!empty($link['url'])) {
                $stmtLinks->execute([$id, $link['url'], $link['label'] ?: null]);
            }
        }
    }

    logAction('local_law', $id, 'create');
    createVersion('local_law', $id, [
        'id' => $id, 'title' => $title, 'law_number' => $law_number, 
        'category' => $category, 'publication_date' => $publication_date, 
        'effective_date' => $effective_date, 'status' => $status, 
        'content_html' => $content_html, 'content_json' => $content_json
    ]);

    redirect('/laws');
}

if (preg_match('#^/laws/(\d+)$#', $path, $m)) {
    $id = (int)$m[1];
    $stmt = db()->prepare('SELECT * FROM local_laws WHERE id = ?');
    $stmt->execute([$id]);
    $law = $stmt->fetch();
    if (!$law) { http_response_code(404); exit('Nicht gefunden'); }

    // Get related links
    $stmtLinks = db()->prepare('SELECT * FROM related_links WHERE entity_type = "local_law" AND entity_id = ?');
    $stmtLinks->execute([$id]);
    $links = $stmtLinks->fetchAll();

    // Get Amendments
    $amends = getAmendedEntities('local_law', $id);
    $amendedBy = getAmendingEntities('local_law', $id);

    render('laws/show', compact('law', 'links', 'amends', 'amendedBy'));
}

if (preg_match('#^/laws/(\d+)/edit$#', $path, $m)) {
    requireAuth();
    $id = (int)$m[1];
    $stmt = db()->prepare('SELECT * FROM local_laws WHERE id = ?');
    $stmt->execute([$id]);
    $law = $stmt->fetch();
    if (!$law) { http_response_code(404); exit('Nicht gefunden'); }

    // Get related links
    $stmtLinks = db()->prepare('SELECT * FROM related_links WHERE entity_type = "local_law" AND entity_id = ?');
    $stmtLinks->execute([$id]);
    $links = $stmtLinks->fetchAll();

    // Get Amendments
    $amends = getAmendedEntities('local_law', $id);

    render('laws/edit', compact('law', 'links', 'amends'));
}

if (preg_match('#^/laws/(\d+)/update$#', $path, $m) && $method === 'POST') {
    requireAuth();
    $id = (int)$m[1];
    $db = db();

    $title = trim($_POST['title'] ?? '');
    $law_number = trim($_POST['law_number'] ?? '');
    $category = $_POST['category'] ?? '';
    $publication_date = $_POST['publication_date'] ?: null;
    $effective_date = $_POST['effective_date'] ?: null;
    $status = $_POST['status'] ?? 'active';
    $content_html = $_POST['content_html'] ?? '';
    $content_json = $_POST['content_json'] ?? '';

    $stmt = $db->prepare('UPDATE local_laws SET title = ?, law_number = ?, category = ?, publication_date = ?, effective_date = ?, status = ?, content_html = ?, content_json = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
    $stmt->execute([$title, $law_number, $category, $publication_date, $effective_date, $status, $content_html, $content_json, $id]);

    // Handle Amendments
    $db->prepare('DELETE FROM entity_amendments WHERE amending_entity_type = "local_law" AND amending_entity_id = ?')->execute([$id]);
    if (isset($_POST['amendments']) && is_array($_POST['amendments'])) {
        $stmtAmends = $db->prepare('INSERT INTO entity_amendments (amending_entity_type, amending_entity_id, amended_entity_type, amended_entity_id) VALUES ("local_law", ?, ?, ?)');
        foreach ($_POST['amendments'] as $amendment) {
            if (strpos($amendment, ':') !== false) {
                list($type, $targetId) = explode(':', $amendment);
                $stmtAmends->execute([$id, $type, (int)$targetId]);
            }
        }
    }

    // Refresh Links
    $db->prepare('DELETE FROM related_links WHERE entity_type = "local_law" AND entity_id = ?')->execute([$id]);
    if (isset($_POST['links']) && is_array($_POST['links'])) {
        $stmtLinks = $db->prepare('INSERT INTO related_links (entity_type, entity_id, url, label) VALUES ("local_law", ?, ?, ?)');
        foreach ($_POST['links'] as $link) {
            if (!empty($link['url'])) {
                $stmtLinks->execute([$id, $link['url'], $link['label'] ?: null]);
            }
        }
    }

    logAction('local_law', $id, 'update');
    createVersion('local_law', $id, [
        'id' => $id, 'title' => $title, 'law_number' => $law_number, 
        'category' => $category, 'publication_date' => $publication_date, 
        'effective_date' => $effective_date, 'status' => $status, 
        'content_html' => $content_html, 'content_json' => $content_json
    ]);

    redirect("/laws/$id");
}

if (preg_match('#^/laws/(\d+)/delete$#', $path, $m) && $method === 'POST') {
    requireAuth();
    $id = (int)$m[1];
    db()->prepare('DELETE FROM local_laws WHERE id = ?')->execute([$id]);
    logAction('local_law', $id, 'delete');
    redirect('/laws');
}

