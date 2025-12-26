<?php
/**
 * Helfer zur Erstellung eines Snippets mit Hervorhebung
 */
function getSnippet(string $html, string $query, int $radius = 150): string {
    $text = strip_tags($html);
    if (empty($query)) return mb_strimwidth($text, 0, 300, '...');
    
    $pos = mb_stripos($text, $query);
    if ($pos === false) return mb_strimwidth($text, 0, 300, '...');
    
    $start = max(0, $pos - $radius);
    $length = mb_strlen($query) + ($radius * 2);
    $snippet = mb_substr($text, $start, $length);
    
    $prefix = $start > 0 ? '...' : '';
    $suffix = ($start + $length) < mb_strlen($text) ? '...' : '';
    
    $snippet = htmlspecialchars($prefix . $snippet . $suffix);
    return preg_replace('/(' . preg_quote(htmlspecialchars($query), '/') . ')/i', '<mark class="bg-gray-200 font-bold p-0.5">$1</mark>', $snippet);
}
?>

<div class="flex flex-col lg:flex-row gap-12">
    <!-- Sidebar: Filter -->
    <aside class="w-full lg:w-72">
        <div class="bg-gray-50 border-2 border-gray-900 p-8 shadow-[8px_8px_0px_0px_rgba(0,0,0,0.05)]">
            <h2 class="text-[10px] font-bold uppercase tracking-[0.3em] text-gray-900 border-b border-gray-200 pb-4 mb-8">
                Filteroptionen
            </h2>
            
            <form action="<?= url('/treaties/search') ?>" method="GET" class="space-y-10">
                <input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>">
                
                <div class="space-y-4">
                    <h3 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Kategorien / Tags</h3>
                    <div class="space-y-3 max-h-[500px] overflow-y-auto pr-4 scrollbar-thin scrollbar-thumb-gray-200">
                        <?php foreach ($allTags as $tag): ?>
                            <label class="flex items-center group cursor-pointer">
                                <input type="checkbox" name="tags[]" value="<?= htmlspecialchars($tag) ?>" 
                                    <?= in_array($tag, $selectedTags) ? 'checked' : '' ?>
                                    onchange="this.form.submit()"
                                    class="h-4 w-4 text-gray-900 border-gray-300 rounded-none focus:ring-gray-900">
                                <span class="ml-3 text-[10px] font-bold uppercase tracking-widest text-gray-500 group-hover:text-gray-900 transition-colors">
                                    <?= htmlspecialchars($tag) ?>
                                </span>
                            </label>
                        <?php endforeach; ?>
                        <?php if (empty($allTags)): ?>
                            <p class="text-[10px] text-gray-400 italic uppercase tracking-widest">Keine Tags</p>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($selectedTags) || !empty($q)): ?>
                    <div class="pt-6 border-t border-gray-200">
                        <a href="<?= url('/treaties/search') ?>" class="text-[10px] font-bold text-red-600 hover:text-red-900 uppercase tracking-widest flex items-center justify-center border border-red-100 py-2 hover:bg-red-50 transition-colors">
                            Filter zurücksetzen
                        </a>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </aside>

    <!-- Main Content: Ergebnisse -->
    <div class="flex-1">
        <div class="mb-12 border-b-2 border-brand-accent pb-8">
            <h1 class="text-4xl font-serif font-bold text-gray-900 tracking-tighter uppercase">
                <?php if ($q): ?>
                    <?= count($treaties) ?> Ergebnisse für <span class="italic normal-case">"<?= htmlspecialchars($q) ?>"</span>
                <?php else: ?>
                    Alle Verträge
                <?php endif; ?>
            </h1>
        </div>

        <div class="divide-y divide-gray-100">
            <?php foreach ($treaties as $t): ?>
                <div class="py-10 first:pt-0 last:pb-0 group">
                    <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-6 mb-4">
                        <div class="space-y-2">
                            <div class="flex items-center gap-3">
                                <span class="badge"><?= htmlspecialchars($t['type']) ?></span>
                                <span class="text-[10px] font-mono font-bold text-gray-400 uppercase tracking-widest">REF-<?= str_pad($t['id'], 5, '0', STR_PAD_LEFT) ?></span>
                            </div>
                            <a href="<?= url('/treaties/' . $t['id']) ?>" class="block text-2xl font-serif font-bold text-gray-900 group-hover:underline decoration-gray-300 underline-offset-8 transition-all">
                                <?= htmlspecialchars($t['stable_id']) ?>
                            </a>
                        </div>
                        <div class="text-right flex flex-col items-end">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em] mb-1 text-right">Status: <?= htmlspecialchars($t['status']) ?></span>
                            <span class="text-sm font-serif italic text-gray-900">
                                Signiert: <?= htmlspecialchars($t['signature_date'] ?? 'N/A') ?>
                            </span>
                        </div>
                    </div>

                    <div class="prose prose-sm prose-slate max-w-none text-gray-600 mb-6 font-serif line-clamp-3 italic leading-relaxed">
                        <?= getSnippet($t['content_html'], $q) ?>
                    </div>

                    <?php
                    $stmtTags = db()->prepare('SELECT tag FROM treaty_tags WHERE treaty_id = ?');
                    $stmtTags->execute([$t['id']]);
                    $tTags = $stmtTags->fetchAll(PDO::FETCH_COLUMN);
                    ?>
                    <?php if (!empty($tTags)): ?>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($tTags as $tag): ?>
                                <span class="badge <?= in_array($tag, $selectedTags) ? 'border-gray-900 text-gray-900 bg-gray-100' : '' ?>">
                                    <?= htmlspecialchars($tag) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <?php if (empty($treaties)): ?>
                <div class="text-center py-32 bg-gray-50 border border-dashed border-gray-300">
                    <h3 class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.3em]">Keine passenden Dokumente gefunden</h3>
                    <div class="mt-8">
                        <a href="<?= url('/treaties/search') ?>" class="btn btn-secondary">Suche zurücksetzen</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
