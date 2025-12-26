<?php
declare(strict_types=1);

require_once __DIR__ . '/app.php';

/**
 * Log an action to the audit_logs table
 */
function logAction(string $entityType, int $entityId, string $action, ?string $details = null): void {
    $user = currentUser();
    $userId = $user ? $user['id'] : null;

    $stmt = db()->prepare('INSERT INTO audit_logs (user_id, entity_type, entity_id, action, details) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$userId, $entityType, $entityId, $action, $details]);
}

/**
 * Create a new version snapshot of an entity
 */
function createVersion(string $entityType, int $entityId, array $data): void {
    $user = currentUser();
    $userId = $user ? $user['id'] : null;

    // Get current version count for this entity
    $stmt = db()->prepare('SELECT COUNT(*) FROM versions WHERE entity_type = ? AND entity_id = ?');
    $stmt->execute([$entityType, $entityId]);
    $versionNumber = (int)$stmt->fetchColumn() + 1;

    $stmt = db()->prepare('INSERT INTO versions (entity_type, entity_id, user_id, data_json, version_number) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$entityType, $entityId, $userId, json_encode($data), $versionNumber]);
}

/**
 * Get the history of an entity
 */
function getEntityHistory(string $entityType, int $entityId): array {
    $stmt = db()->prepare('
        SELECT v.*, u.username 
        FROM versions v 
        LEFT JOIN users u ON v.user_id = u.id 
        WHERE v.entity_type = ? AND v.entity_id = ? 
        ORDER BY v.version_number DESC
    ');
    $stmt->execute([$entityType, $entityId]);
    return $stmt->fetchAll();
}

/**
 * Revert an entity to a specific version
 */
function revertToVersion(string $entityType, int $entityId, int $versionId): bool {
    // Get the version data
    $stmt = db()->prepare('SELECT * FROM versions WHERE id = ? AND entity_type = ? AND entity_id = ?');
    $stmt->execute([$versionId, $entityType, $entityId]);
    $version = $stmt->fetch();

    if (!$version) return false;

    $data = json_decode($version['data_json'], true);
    unset($data['id']); // Don't overwrite the ID

    if ($entityType === 'country') {
        $stmt = db()->prepare('UPDATE countries SET name = ?, full_name = ?, capital = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
        $stmt->execute([$data['name'], $data['full_name'], $data['capital'], $entityId]);
    } elseif ($entityType === 'treaty') {
        $stmt = db()->prepare('UPDATE treaties SET stable_id = ?, type = ?, status = ?, signature_date = ?, in_force_date = ?, duration = ?, content_html = ?, content_json = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
        $stmt->execute([
            $data['stable_id'], $data['type'], $data['status'], $data['signature_date'], 
            $data['in_force_date'], $data['duration'], $data['content_html'], 
            $data['content_json'], $entityId
        ]);
    }

    logAction($entityType, $entityId, 'revert', 'Reverted to version #' . $version['version_number']);
    createVersion($entityType, $entityId, $data); // Create a new version snapshot for the revert action itself

    return true;
}

