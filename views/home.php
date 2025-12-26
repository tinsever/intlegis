<div class="space-y-16">
    <!-- Hero Section -->
    <div class="text-center py-12 md:py-20 px-4 sm:px-6 lg:px-8 bg-white border-b-2 border-brand-accent">
        <h1 class="text-4xl md:text-6xl font-serif font-bold text-gray-900 tracking-tighter uppercase mb-6">
            <?= htmlspecialchars(config('app.name', 'IntLegis')) ?>
        </h1>
        <p class="text-[10px] font-bold uppercase tracking-[0.4em] text-gray-500 max-w-2xl mx-auto mb-12">
            <?= htmlspecialchars(config('app.description', 'Zentralarchiv für internationale Verträge und amtliche Bekanntmachungen')) ?>
        </p>

        <div class="max-w-3xl mx-auto relative group">
            <form action="<?= url('/search') ?>" method="GET" class="relative">
                <input type="search" name="q" 
                       class="block w-full px-6 py-6 bg-gray-50 border-2 border-gray-900 rounded-none leading-5 placeholder-gray-400 focus:outline-none focus:bg-white text-xl font-serif italic transition-all shadow-[8px_8px_0px_0px_rgba(0,0,0,0.1)] focus:shadow-none" 
                       placeholder="RECHTSDATENBANK DURCHSUCHEN...">
            <button type="submit"
                    class="absolute right-4 top-4 bottom-4 px-4 md:px-8 bg-brand-accent text-white font-bold uppercase tracking-[0.2em] text-[10px] hover:opacity-90 transition-colors">
                Suchen
            </button>
            </form>
        </div>

        <div class="mt-16 flex flex-wrap justify-center gap-8 md:gap-12">
            <?php if (config('modules.treaties.enabled', true)): ?>
            <a href="<?= url('/treaties') ?>" class="flex flex-col items-center gap-4 group">
                <div class="p-6 border-2 border-gray-200 group-hover:border-gray-900 transition-colors">
                    <svg class="w-10 h-10 text-gray-400 group-hover:text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                </div>
                <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-500 group-hover:text-gray-900"><?= htmlspecialchars(config('modules.treaties.label', 'Verträge')) ?></span>
            </a>
            <a href="<?= url('/countries') ?>" class="flex flex-col items-center gap-4 group">
                <div class="p-6 border-2 border-gray-200 group-hover:border-gray-900 transition-colors">
                    <svg class="w-10 h-10 text-gray-400 group-hover:text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                </div>
                <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-500 group-hover:text-gray-900"><?= htmlspecialchars(config('modules.countries.label', 'Staaten')) ?></span>
            </a>
            <?php endif; ?>

            <?php if (config('modules.laws.enabled', true)): ?>
            <a href="<?= url('/laws') ?>" class="flex flex-col items-center gap-4 group">
                <div class="p-6 border-2 border-gray-200 group-hover:border-gray-900 transition-colors">
                    <svg class="w-10 h-10 text-gray-400 group-hover:text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                </div>
                <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-500 group-hover:text-gray-900"><?= htmlspecialchars(config('modules.laws.label', 'Anzeiger')) ?></span>
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Edits Section -->
    <div class="max-w-5xl mx-auto px-4">
        <h2 class="text-[10px] font-bold uppercase tracking-[0.4em] text-gray-400 mb-10 text-center">
            Protokoll der letzten Änderungen
        </h2>

        <div class="divide-y divide-gray-100 border-t border-b border-gray-100">
            <?php foreach ($recentEdits as $log): ?>
                <div class="flex items-center gap-8 py-6 group">
                    <div class="text-[10px] font-mono font-bold text-gray-300 group-hover:text-gray-900 transition-colors w-32">
                        <?= date('d.m.Y H:i', strtotime($log['created_at'])) ?>
                    </div>
                    <div class="flex-grow">
                        <div class="flex justify-between items-center">
                            <p class="text-sm font-serif italic text-gray-900">
                                <?= htmlspecialchars($log['username'] ?? 'System') ?> hat 
                                <span class="font-bold not-italic font-sans uppercase text-[10px] tracking-widest mx-1"><?= htmlspecialchars($log['entity_label'] ?? 'Eintrag #' . $log['entity_id']) ?></span> 
                                <?= $log['action'] === 'create' ? 'neu angelegt' : ($log['action'] === 'update' ? 'überarbeitet' : 'entfernt') ?>.
                            </p>
                            <span class="badge">
                                <?= $log['entity_type'] === 'treaty' ? htmlspecialchars(config('modules.treaties.label', 'Völkerrecht')) : ($log['entity_type'] === 'local_law' ? htmlspecialchars(config('modules.laws.label', 'Anzeiger')) : htmlspecialchars(config('modules.countries.label', 'Staat'))) ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

