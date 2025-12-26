<?php
declare(strict_types=1);

require_once __DIR__ . '/app.php';

/**
 * Handle Replication Export (Primary Side)
 */
function handleSyncExport(): void {
    $lastId = (int)($_GET['last_id'] ?? 0);
    $db = db();

    // Fetch new audit logs
    $stmt = $db->prepare('SELECT * FROM audit_logs WHERE id > ? ORDER BY id ASC LIMIT 100');
    $stmt->execute([$lastId]);
    $logs = $stmt->fetchAll();

    $response = [
        'logs' => $logs,
        'versions' => [],
        'entities' => [
            'treaties' => [],
            'countries' => [],
            'treaty_parties' => [],
            'treaty_tags' => [],
        ]
    ];

    if (!empty($logs)) {
        $treatyIds = [];
        $countryIds = [];

        foreach ($logs as $log) {
            if ($log['entity_type'] === 'treaty') $treatyIds[] = (int)$log['entity_id'];
            if ($log['entity_type'] === 'country') $countryIds[] = (int)$log['entity_id'];
        }

        $treatyIds = array_unique($treatyIds);
        $countryIds = array_unique($countryIds);

        // Fetch related versions
        if (!empty($treatyIds) || !empty($countryIds)) {
            $versionStmt = $db->prepare('SELECT * FROM versions WHERE (entity_type = "treaty" AND entity_id IN (' . implode(',', array_fill(0, count($treatyIds) ?: 1, '?')) . ')) OR (entity_type = "country" AND entity_id IN (' . implode(',', array_fill(0, count($countryIds) ?: 1, '?')) . '))');
            $versionStmt->execute(array_merge($treatyIds ?: [0], $countryIds ?: [0]));
            $response['versions'] = $versionStmt->fetchAll();
        }

        // Fetch current entity states
        if (!empty($treatyIds)) {
            $response['entities']['treaties'] = $db->query("SELECT * FROM treaties WHERE id IN (" . implode(',', $treatyIds) . ")")->fetchAll();
            $response['entities']['treaty_parties'] = $db->query("SELECT * FROM treaty_parties WHERE treaty_id IN (" . implode(',', $treatyIds) . ")")->fetchAll();
            $response['entities']['treaty_tags'] = $db->query("SELECT * FROM treaty_tags WHERE treaty_id IN (" . implode(',', $treatyIds) . ")")->fetchAll();
        }
        if (!empty($countryIds)) {
            $response['entities']['countries'] = $db->query("SELECT * FROM countries WHERE id IN (" . implode(',', $countryIds) . ")")->fetchAll();
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

/**
 * Handle Replication Import (Secondary Side)
 */
function pullUpdates(): array {
    if (isPrimary()) return ['status' => 'error', 'message' => 'Updates können auf einer primären Instanz nicht abgerufen werden'];

    $db = db();
    $lastLogId = (int)$db->query('SELECT last_log_id FROM sync_state ORDER BY id DESC LIMIT 1')->fetchColumn() ?: 0;

    $primaryUrl = rtrim((string)config('instance.primary_url'), '/') . '/api/sync?last_id=' . $lastLogId;

    $ch = curl_init($primaryUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        return ['status' => 'error', 'message' => 'Primärinstanz antwortete mit HTTP ' . $httpCode];
    }

    $data = json_decode((string)$response, true);
    if (!$data || !isset($data['logs'])) {
        return ['status' => 'error', 'message' => 'Ungültige Antwort von der Primärinstanz'];
    }

    if (empty($data['logs'])) {
        return ['status' => 'success', 'count' => 0, 'message' => 'Bereits aktuell'];
    }

    $db->beginTransaction();
    try {
        $driver = config('db.driver');

        // 1. Sync Countries
        foreach ($data['entities']['countries'] as $c) {
            if ($driver === 'sqlite') {
                $stmt = $db->prepare('INSERT INTO countries (id, name, full_name, capital, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?) ON CONFLICT(id) DO UPDATE SET name=excluded.name, full_name=excluded.full_name, capital=excluded.capital, updated_at=excluded.updated_at');
            } else {
                $stmt = $db->prepare('INSERT INTO countries (id, name, full_name, capital, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE name=VALUES(name), full_name=VALUES(full_name), capital=VALUES(capital), updated_at=VALUES(updated_at)');
            }
            $stmt->execute([$c['id'], $c['name'], $c['full_name'], $c['capital'], $c['created_at'], $c['updated_at']]);
        }

        // 2. Sync Treaties
        foreach ($data['entities']['treaties'] as $t) {
            if ($driver === 'sqlite') {
                $sql = 'INSERT INTO treaties (id, stable_id, type, status, signature_date, in_force_date, duration, content_html, content_json, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON CONFLICT(id) DO UPDATE SET stable_id=excluded.stable_id, type=excluded.type, status=excluded.status, signature_date=excluded.signature_date, in_force_date=excluded.in_force_date, duration=excluded.duration, content_html=excluded.content_html, content_json=excluded.content_json, updated_at=excluded.updated_at';
            } else {
                $sql = 'INSERT INTO treaties (id, stable_id, type, status, signature_date, in_force_date, duration, content_html, content_json, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE stable_id=VALUES(stable_id), type=VALUES(type), status=VALUES(status), signature_date=VALUES(signature_date), in_force_date=VALUES(in_force_date), duration=VALUES(duration), content_html=VALUES(content_html), content_json=VALUES(content_json), updated_at=VALUES(updated_at)';
            }
            $db->prepare($sql)->execute([$t['id'], $t['stable_id'], $t['type'], $t['status'], $t['signature_date'], $t['in_force_date'], $t['duration'], $t['content_html'], $t['content_json'], $t['created_at'], $t['updated_at']]);
        }

        // 3. Sync Parties & Tags (Clear and re-insert for simplicity)
        if (!empty($data['entities']['treaties'])) {
            $tIds = array_column($data['entities']['treaties'], 'id');
            $db->query('DELETE FROM treaty_parties WHERE treaty_id IN (' . implode(',', $tIds) . ')');
            $db->query('DELETE FROM treaty_tags WHERE treaty_id IN (' . implode(',', $tIds) . ')');
            
            foreach ($data['entities']['treaty_parties'] as $p) {
                $db->prepare('INSERT INTO treaty_parties (treaty_id, country_id, is_organization, organization_name) VALUES (?, ?, ?, ?)')->execute([$p['treaty_id'], $p['country_id'], $p['is_organization'], $p['organization_name']]);
            }
            foreach ($data['entities']['treaty_tags'] as $tg) {
                $db->prepare('INSERT INTO treaty_tags (treaty_id, tag) VALUES (?, ?)')->execute([$tg['treaty_id'], $tg['tag']]);
            }
        }

        // 4. Sync Versions
        foreach ($data['versions'] as $v) {
            if ($driver === 'sqlite') {
                $sql = 'INSERT INTO versions (id, entity_type, entity_id, user_id, data_json, version_number, created_at) VALUES (?, ?, ?, ?, ?, ?, ?) ON CONFLICT(id) DO NOTHING';
            } else {
                $sql = 'INSERT INTO versions (id, entity_type, entity_id, user_id, data_json, version_number, created_at) VALUES (?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE id=id';
            }
            $db->prepare($sql)->execute([$v['id'], $v['entity_type'], $v['entity_id'], $v['user_id'], $v['data_json'], $v['version_number'], $v['created_at']]);
        }

        // 5. Sync Audit Logs
        foreach ($data['logs'] as $l) {
            if ($driver === 'sqlite') {
                $sql = 'INSERT INTO audit_logs (id, user_id, entity_type, entity_id, action, details, created_at) VALUES (?, ?, ?, ?, ?, ?, ?) ON CONFLICT(id) DO NOTHING';
            } else {
                $sql = 'INSERT INTO audit_logs (id, user_id, entity_type, entity_id, action, details, created_at) VALUES (?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE id=id';
            }
            $db->prepare($sql)->execute([$l['id'], $l['user_id'], $l['entity_type'], $l['entity_id'], $l['action'], $l['details'], $l['created_at']]);
        }

        // 6. Update Sync State
        $newLastId = (int)end($data['logs'])['id'];
        $db->prepare('INSERT INTO sync_state (last_log_id) VALUES (?)')->execute([$newLastId]);

        $db->commit();
        return ['status' => 'success', 'count' => count($data['logs']), 'message' => 'Synchronisiert: ' . count($data['logs']) . ' neue Datensätze'];
    } catch (Exception $e) {
        $db->rollBack();
        return ['status' => 'error', 'message' => 'Synchronisierung fehlgeschlagen: ' . $e->getMessage()];
    }
}
