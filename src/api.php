<?php
declare(strict_types=1);

require_once __DIR__ . '/app.php';

// $path is already defined and cleaned in public/index.php

if ($path === '/api/search-entities') {
    requireAuth();
    $q = trim($_GET['q'] ?? $_GET['search_amendments'] ?? '');
    $entities = [];

    if ($q !== '') {
        $searchTerm = "%$q%";
        
        // Search Laws
        $stmt = db()->prepare("SELECT 'local_law' as type, id, title as label, law_number as sublabel FROM local_laws WHERE title LIKE ? OR law_number LIKE ? LIMIT 10");
        $stmt->execute([$searchTerm, $searchTerm]);
        $entities = array_merge($entities, $stmt->fetchAll());

        // Search Treaties
        $stmt = db()->prepare("SELECT 'treaty' as type, id, stable_id as label, type as sublabel FROM treaties WHERE stable_id LIKE ? LIMIT 10");
        $stmt->execute([$searchTerm]);
        $entities = array_merge($entities, $stmt->fetchAll());
    }

    // Return a simple list for HTMX or similar
    if (isset($_GET['format']) && $_GET['format'] === 'json') {
        header('Content-Type: application/json');
        echo json_encode($entities);
    } else {
        // Default: Return HTML fragment for HTMX
        foreach ($entities as $entity) {
            $value = $entity['type'] . ':' . $entity['id'];
            echo "<button type='button' 
                          class='flex flex-col w-full text-left px-4 py-3 hover:bg-gray-50 border-b border-gray-100 last:border-0 transition-colors'
                          @click='addAmendment(\"{$entity['type']}\", {$entity['id']}, \"" . htmlspecialchars($entity['label']) . "\")'>
                    <span class='text-xs font-bold uppercase tracking-widest text-gray-900'>{$entity['label']}</span>
                    <span class='text-[10px] text-gray-400 font-mono'>{$entity['type']}: " . ($entity['sublabel'] ?: $entity['id']) . "</span>
                  </button>";
        }
        if (empty($entities) && $q !== '') {
            echo "<div class='px-4 py-8 text-center text-[10px] font-bold uppercase tracking-widest text-gray-400'>Keine Ergebnisse gefunden</div>";
        }
    }
    exit;
}

