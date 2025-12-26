<?php
declare(strict_types=1);

require_once __DIR__ . '/app.php';

$q = trim($_GET['q'] ?? '');
$laws = [];
$treaties = [];

if ($q !== '') {
    $searchTerm = "%$q%";
    
    // Search Laws
    $stmt = db()->prepare("SELECT * FROM local_laws WHERE title LIKE ? OR law_number LIKE ? OR content_html LIKE ? OR category LIKE ? LIMIT 20");
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    $laws = $stmt->fetchAll();

    // Search Treaties
    $stmt = db()->prepare("SELECT * FROM treaties WHERE stable_id LIKE ? OR content_html LIKE ? LIMIT 20");
    $stmt->execute([$searchTerm, $searchTerm]);
    $treaties = $stmt->fetchAll();
}

render('search', compact('laws', 'treaties', 'q'));

