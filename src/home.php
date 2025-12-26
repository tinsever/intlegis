<?php
declare(strict_types=1);

require_once __DIR__ . '/app.php';

$db = db();

// Fetch recent audit logs with entity details
$stmt = $db->query("
    SELECT a.*, u.username,
           CASE 
             WHEN a.entity_type = 'treaty' THEN (SELECT stable_id FROM treaties WHERE id = a.entity_id)
             WHEN a.entity_type = 'local_law' THEN (SELECT title FROM local_laws WHERE id = a.entity_id)
             WHEN a.entity_type = 'country' THEN (SELECT name FROM countries WHERE id = a.entity_id)
           END as entity_label
    FROM audit_logs a
    LEFT JOIN users u ON a.user_id = u.id
    ORDER BY a.created_at DESC
    LIMIT 10
");
$recentEdits = $stmt->fetchAll();

render('home', compact('recentEdits'));

