<?php
declare(strict_types=1);

require_once __DIR__ . '/app.php';
require_once __DIR__ . '/audit.php';


// requireAuth() removed from top to allow public viewing

$method = $_SERVER['REQUEST_METHOD'];
$path   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($path === '/' || $path === '/treaties') {
    $stmt = db()->query('SELECT * FROM treaties ORDER BY created_at DESC');
    $treaties = $stmt->fetchAll();
    render('treaties/index', compact('treaties'));
}

if ($path === '/treaties/search') {
    $q = trim($_GET['q'] ?? '');
    $selectedTags = $_GET['tags'] ?? [];
    if (!is_array($selectedTags)) $selectedTags = [$selectedTags];
    
    $db = db();
    
    // Get all unique tags for the filter sidebar
    $allTags = $db->query('SELECT DISTINCT tag FROM treaty_tags ORDER BY tag ASC')->fetchAll(PDO::FETCH_COLUMN);
    
    $treaties = [];
    if ($q !== '' || !empty($selectedTags)) {
        $params = [];
        $where = [];
        
        if ($q !== '') {
            $searchTerm = "%$q%";
            $where[] = '(t.stable_id LIKE ? OR t.content_html LIKE ? OR tt.tag LIKE ?)';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($selectedTags)) {
            $placeholders = implode(',', array_fill(0, count($selectedTags), '?'));
            $where[] = "t.id IN (SELECT treaty_id FROM treaty_tags WHERE tag IN ($placeholders))";
            $params = array_merge($params, $selectedTags);
        }
        
        $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $stmt = $db->prepare("
            SELECT DISTINCT t.* FROM treaties t
            LEFT JOIN treaty_tags tt ON t.id = tt.treaty_id
            $whereSql
            ORDER BY t.created_at DESC
        ");
        $stmt->execute($params);
        $treaties = $stmt->fetchAll();
    }
    render('treaties/search', compact('treaties', 'q', 'allTags', 'selectedTags'));
}

if ($path === '/treaties/create') {
    requireAuth();
    requirePrimary();
    $countries = db()->query('SELECT * FROM countries ORDER BY name ASC')->fetchAll();
    $amends = [];
    render('treaties/create', compact('countries', 'amends'));
}

if ($path === '/treaties/store' && $method === 'POST') {
    requireAuth();
    requirePrimary();
    $db = db();
    $db->beginTransaction();

    try {
        $stable_id = trim($_POST['stable_id'] ?? '');
        $type = $_POST['type'] ?? '';
        $status = $_POST['status'] ?? '';
        $signature_date = $_POST['signature_date'] ?: null;
        $in_force_date = $_POST['in_force_date'] ?: null;
        $duration = $_POST['duration'] ?? '';
        $content_html = $_POST['content_html'] ?? '';
        $content_json = $_POST['content_json'] ?? '';

        $stmt = $db->prepare('INSERT INTO treaties (stable_id, type, status, signature_date, in_force_date, duration, content_html, content_json) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$stable_id, $type, $status, $signature_date, $in_force_date, $duration, $content_html, $content_json]);
        $id = (int)$db->lastInsertId();

        // Handle Amendments
        if (isset($_POST['amendments']) && is_array($_POST['amendments'])) {
            $stmtAmends = $db->prepare('INSERT INTO entity_amendments (amending_entity_type, amending_entity_id, amended_entity_type, amended_entity_id) VALUES ("treaty", ?, ?, ?)');
            foreach ($_POST['amendments'] as $amendment) {
                if (strpos($amendment, ':') !== false) {
                    list($type, $targetId) = explode(':', $amendment);
                    $stmtAmends->execute([$id, $type, (int)$targetId]);
                }
            }
        }

        // Handle Links
        if (isset($_POST['links']) && is_array($_POST['links'])) {
            $stmtLinks = $db->prepare('INSERT INTO related_links (entity_type, entity_id, url, label) VALUES ("treaty", ?, ?, ?)');
            foreach ($_POST['links'] as $link) {
                if (!empty($link['url'])) {
                    $stmtLinks->execute([$id, $link['url'], $link['label'] ?: null]);
                }
            }
        }

        // Handle Parties
        if (isset($_POST['parties']) && is_array($_POST['parties'])) {
            $stmtParties = $db->prepare('INSERT INTO treaty_parties (treaty_id, country_id) VALUES (?, ?)');
            foreach ($_POST['parties'] as $countryId) {
                $stmtParties->execute([$id, (int)$countryId]);
            }
        }

        // Handle Tags
        if (isset($_POST['tags']) && !empty($_POST['tags'])) {
            $tags = array_map('trim', explode(',', $_POST['tags']));
            $stmtTags = $db->prepare('INSERT INTO treaty_tags (treaty_id, tag) VALUES (?, ?)');
            foreach ($tags as $tag) {
                if ($tag !== '') $stmtTags->execute([$id, $tag]);
            }
        }

        $db->commit();
        
        $treaty = ['id' => $id, 'stable_id' => $stable_id, 'type' => $type, 'status' => $status, 'signature_date' => $signature_date, 'in_force_date' => $in_force_date, 'duration' => $duration, 'content_html' => $content_html, 'content_json' => $content_json];
        logAction('treaty', $id, 'create');
        createVersion('treaty', $id, $treaty);

        redirect('/treaties');
    } catch (Exception $e) {
        $db->rollBack();
        flash('Fehler beim Speichern des Vertrags: ' . $e->getMessage());
        redirect('/treaties/create');
    }
}

if (preg_match('#^/treaties/(\d+)$#', $path, $m)) {
    $id = (int)$m[1];
    $stmt = db()->prepare('SELECT * FROM treaties WHERE id = ?');
    $stmt->execute([$id]);
    $treaty = $stmt->fetch();
    if (!$treaty) { http_response_code(404); exit('Nicht gefunden'); }

    // Get Parties
    $stmt = db()->prepare('SELECT c.* FROM countries c JOIN treaty_parties tp ON c.id = tp.country_id WHERE tp.treaty_id = ?');
    $stmt->execute([$id]);
    $parties = $stmt->fetchAll();

    // Get Tags
    $stmt = db()->prepare('SELECT tag FROM treaty_tags WHERE treaty_id = ?');
    $stmt->execute([$id]);
    $tags = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Get related links
    $stmtLinks = db()->prepare('SELECT * FROM related_links WHERE entity_type = "treaty" AND entity_id = ?');
    $stmtLinks->execute([$id]);
    $links = $stmtLinks->fetchAll();

    // Get Amendments
    $amends = getAmendedEntities('treaty', $id);
    $amendedBy = getAmendingEntities('treaty', $id);

    render('treaties/show', compact('treaty', 'parties', 'tags', 'links', 'amends', 'amendedBy'));
}

if (preg_match('#^/treaties/(\d+)/edit$#', $path, $m)) {
    requireAuth();
    requirePrimary();
    $id = (int)$m[1];
    $stmt = db()->prepare('SELECT * FROM treaties WHERE id = ?');
    $stmt->execute([$id]);
    $treaty = $stmt->fetch();
    if (!$treaty) { http_response_code(404); exit('Nicht gefunden'); }

    $countries = db()->query('SELECT * FROM countries ORDER BY name ASC')->fetchAll();
    
    $stmt = db()->prepare('SELECT country_id FROM treaty_parties WHERE treaty_id = ?');
    $stmt->execute([$id]);
    $currentPartyIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $stmt = db()->prepare('SELECT tag FROM treaty_tags WHERE treaty_id = ?');
    $stmt->execute([$id]);
    $tags = implode(', ', $stmt->fetchAll(PDO::FETCH_COLUMN));

    // Get related links
    $stmtLinks = db()->prepare('SELECT * FROM related_links WHERE entity_type = "treaty" AND entity_id = ?');
    $stmtLinks->execute([$id]);
    $links = $stmtLinks->fetchAll();

    // Get Amendments
    $amends = getAmendedEntities('treaty', $id);

    render('treaties/edit', compact('treaty', 'countries', 'currentPartyIds', 'tags', 'links', 'amends'));
}

if (preg_match('#^/treaties/(\d+)/update$#', $path, $m) && $method === 'POST') {
    requireAuth();
    requirePrimary();
    $id = (int)$m[1];
    $db = db();
    $db->beginTransaction();

    try {
        $stable_id = trim($_POST['stable_id'] ?? '');
        $type = $_POST['type'] ?? '';
        $status = $_POST['status'] ?? '';
        $signature_date = $_POST['signature_date'] ?: null;
        $in_force_date = $_POST['in_force_date'] ?: null;
        $duration = $_POST['duration'] ?? '';
        $content_html = $_POST['content_html'] ?? '';
        $content_json = $_POST['content_json'] ?? '';

        $stmt = $db->prepare('UPDATE treaties SET stable_id = ?, type = ?, status = ?, signature_date = ?, in_force_date = ?, duration = ?, content_html = ?, content_json = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
        $stmt->execute([$stable_id, $type, $status, $signature_date, $in_force_date, $duration, $content_html, $content_json, $id]);

        // Handle Amendments
        $db->prepare('DELETE FROM entity_amendments WHERE amending_entity_type = "treaty" AND amending_entity_id = ?')->execute([$id]);
        if (isset($_POST['amendments']) && is_array($_POST['amendments'])) {
            $stmtAmends = $db->prepare('INSERT INTO entity_amendments (amending_entity_type, amending_entity_id, amended_entity_type, amended_entity_id) VALUES ("treaty", ?, ?, ?)');
            foreach ($_POST['amendments'] as $amendment) {
                if (strpos($amendment, ':') !== false) {
                    list($type, $targetId) = explode(':', $amendment);
                    $stmtAmends->execute([$id, $type, (int)$targetId]);
                }
            }
        }

        // Refresh Links
        $db->prepare('DELETE FROM related_links WHERE entity_type = "treaty" AND entity_id = ?')->execute([$id]);
        if (isset($_POST['links']) && is_array($_POST['links'])) {
            $stmtLinks = $db->prepare('INSERT INTO related_links (entity_type, entity_id, url, label) VALUES ("treaty", ?, ?, ?)');
            foreach ($_POST['links'] as $link) {
                if (!empty($link['url'])) {
                    $stmtLinks->execute([$id, $link['url'], $link['label'] ?: null]);
                }
            }
        }

        // Refresh Parties
        $db->prepare('DELETE FROM treaty_parties WHERE treaty_id = ?')->execute([$id]);
        if (isset($_POST['parties']) && is_array($_POST['parties'])) {
            $stmtParties = $db->prepare('INSERT INTO treaty_parties (treaty_id, country_id) VALUES (?, ?)');
            foreach ($_POST['parties'] as $countryId) {
                $stmtParties->execute([$id, (int)$countryId]);
            }
        }

        // Refresh Tags
        $db->prepare('DELETE FROM treaty_tags WHERE treaty_id = ?')->execute([$id]);
        if (isset($_POST['tags']) && !empty($_POST['tags'])) {
            $tagsArray = array_map('trim', explode(',', $_POST['tags']));
            $stmtTags = $db->prepare('INSERT INTO treaty_tags (treaty_id, tag) VALUES (?, ?)');
            foreach ($tagsArray as $tag) {
                if ($tag !== '') $stmtTags->execute([$id, $tag]);
            }
        }

        $db->commit();
        
        $treaty = ['id' => $id, 'stable_id' => $stable_id, 'type' => $type, 'status' => $status, 'signature_date' => $signature_date, 'in_force_date' => $in_force_date, 'duration' => $duration, 'content_html' => $content_html, 'content_json' => $content_json];
        logAction('treaty', $id, 'update');
        createVersion('treaty', $id, $treaty);

        redirect("/treaties/$id");
    } catch (Exception $e) {
        $db->rollBack();
        flash('Fehler beim Aktualisieren des Vertrags: ' . $e->getMessage());
        redirect("/treaties/$id/edit");
    }
}

if (preg_match('#^/treaties/(\d+)/history$#', $path, $m)) {
    $id = (int)$m[1];
    $stmt = db()->prepare('SELECT * FROM treaties WHERE id = ?');
    $stmt->execute([$id]);
    $treaty = $stmt->fetch();
    if (!$treaty) { http_response_code(404); exit('Nicht gefunden'); }

    $history = getEntityHistory('treaty', $id);
    render('treaties/history', compact('treaty', 'history'));
}

if (preg_match('#^/treaties/(\d+)/revert/(\d+)$#', $path, $m) && $method === 'POST') {
    requireAuth();
    requirePrimary();
    $id = (int)$m[1];
    $versionId = (int)$m[2];
    
    if (revertToVersion('treaty', $id, $versionId)) {
        flash('Vertrag erfolgreich wiederhergestellt.');
    } else {
        flash('Wiederherstellung des Vertrags fehlgeschlagen.');
    }
    redirect("/treaties/$id/history");
}
