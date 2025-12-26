<?php
declare(strict_types=1);

require_once __DIR__ . '/app.php';

function handleExport(): void {
    $type = $_GET['type'] ?? 'treaty';
    $id = (int)($_GET['id'] ?? 0);
    $format = $_GET['format'] ?? 'html';

    $db = db();
    if ($type === 'treaty') {
        $stmt = $db->prepare("SELECT * FROM treaties WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        $title = $data['stable_id'] ?? 'Export';
        $content = $data['content_html'] ?? '';
    } else {
        $stmt = $db->prepare("SELECT * FROM local_laws WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        $title = $data['title'] ?? 'Export';
        $content = $data['content_html'] ?? '';
    }

    if (!$data) {
        http_response_code(404);
        die('Nicht gefunden');
    }

    switch ($format) {
        case 'txt':
            header('Content-Type: text/plain; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $title . '.txt"');
            echo strip_tags(str_replace(['<br>', '<p>', '</div>'], ["\n", "\n\n", "\n"], $content));
            break;

        case 'html':
            header('Content-Type: text/html; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $title . '.html"');
            echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>$title</title>";
            echo "<style>body{font-family:serif; line-height:1.6; max-width:800px; margin:40px auto; padding:20px; color:#1a1a1a;} h1{border-bottom:2px solid #000; padding-bottom:10px; text-transform:uppercase;} .meta{color:#666; font-style:italic; margin-bottom:30px;}</style></head><body>";
            echo "<h1>$title</h1>";
            echo "<div class='meta'>Exportiert am " . date('d.m.Y H:i') . " von " . config('app.name') . "</div>";
            echo $content;
            echo "</body></html>";
            break;

        case 'pdf':
            // For a true PDF without heavy libraries, we use the print-to-PDF trick.
            // We serve an HTML page that triggers window.print() and then closes itself.
            echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>$title</title>";
            echo "<style>
                @media print { .no-print { display: none; } }
                body{font-family:serif; line-height:1.6; max-width:800px; margin:40px auto; padding:20px; color:#1a1a1a;} 
                h1{border-bottom:2px solid #000; padding-bottom:10px; text-transform:uppercase;} 
                .meta{color:#666; font-style:italic; margin-bottom:30px;}
                .no-print { background: #f3f4f6; padding: 20px; border-radius: 8px; text-align: center; margin-bottom: 40px; }
            </style></head><body>";
            echo "<div class='no-print'>
                    <p><b>Druckansicht / PDF-Export</b></p>
                    <p>Nutzen Sie die Druckfunktion Ihres Browsers (Strg+P / Cmd+P) und wählen Sie 'Als PDF speichern'.</p>
                    <button onclick='window.print()' style='padding:10px 20px; background:#1e3a8a; color:white; border:none; border-radius:4px; cursor:pointer;'>Druckdialog öffnen</button>
                    <button onclick='window.close()' style='padding:10px 20px; background:#e5e7eb; color:#374151; border:none; border-radius:4px; cursor:pointer; margin-left:10px;'>Schließen</button>
                  </div>";
            echo "<h1>$title</h1>";
            echo "<div class='meta'>Offizielles Dokument - " . config('app.name') . " - Stand: " . date('d.m.Y') . "</div>";
            echo $content;
            echo "<script>window.onload = function() { setTimeout(function() { window.print(); }, 500); };</script>";
            echo "</body></html>";
            break;
    }
    exit;
}

