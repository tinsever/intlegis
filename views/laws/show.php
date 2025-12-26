<div class="space-y-16">
    <div class="pb-8 border-b-4 border-brand-accent">
        <?php if (!empty($amendedBy)): ?>
            <div class="mb-8 border border-brand-accent bg-brand-accent/5 p-6 flex items-center gap-4 no-print">
                <svg class="w-6 h-6 text-brand-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <div class="text-[10px] font-bold uppercase tracking-widest">
                    <p class="text-brand-accent mb-1">Hinweis zur Aktualität</p>
                    <p class="text-gray-900">Dieser Rechtsakt wurde durch nachfolgende Bestimmungen geändert. Prüfen Sie die <a href="#amendments" class="underline">Änderungshistorie</a>.</p>
                </div>
            </div>
        <?php endif; ?>

        <div class="flex justify-between items-start mb-12">
            <div class="space-y-4">
            <div class="flex items-center gap-3">
                    <span class="text-[10px] font-bold uppercase tracking-[0.3em] text-gray-500"><?= htmlspecialchars(config('modules.laws.label', 'Anzeiger')) ?></span>
                    <span class="h-1.5 w-1.5 bg-gray-300 rounded-full"></span>
                    <span class="text-[10px] font-mono font-bold text-gray-500 uppercase tracking-widest"><?= htmlspecialchars($law['law_number']) ?></span>
                </div>
                <h1 class="text-5xl font-serif font-bold text-gray-900 leading-[1.1] uppercase tracking-tighter"><?= htmlspecialchars($law['title']) ?></h1>
                <div class="flex gap-6 text-[10px] font-bold uppercase tracking-widest text-gray-500 pt-2">
                    <div class="flex flex-col">
                        <span class="text-gray-400 mb-1">Datum</span>
                        <span class="text-gray-900"><?= $law['effective_date'] ? date('d.m.Y', strtotime($law['effective_date'])) : date('d.m.Y', strtotime($law['created_at'])) ?></span>
            </div>
                <?php if ($law['category']): ?>
                        <div class="flex flex-col">
                            <span class="text-gray-400 mb-1">Kategorie</span>
                            <span class="text-gray-900"><?= htmlspecialchars($law['category']) ?></span>
                        </div>
                <?php endif; ?>
            </div>
        </div>
            <div class="flex flex-col gap-3">
            <div x-data="{ open: false }" class="relative inline-block text-left">
                    <button @click="open = !open" type="button" class="w-full btn btn-secondary">
                    Export
                </button>
                    <div x-show="open" @click.away="open = false" class="origin-top-right absolute right-0 mt-2 w-48 bg-white border border-gray-900 z-10">
                    <div class="py-1">
                            <a href="<?= url('/export?type=law&id=' . $law['id'] . '&format=pdf') ?>" target="_blank" class="block px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-700 hover:bg-gray-50 border-b border-gray-100 last:border-0">Als PDF</a>
                            <a href="<?= url('/export?type=law&id=' . $law['id'] . '&format=html') ?>" class="block px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-700 hover:bg-gray-50 border-b border-gray-100 last:border-0">Als HTML</a>
                            <a href="<?= url('/export?type=law&id=' . $law['id'] . '&format=txt') ?>" class="block px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-700 hover:bg-gray-50 border-b border-gray-100 last:border-0">Als Text (TXT)</a>
                    </div>
                </div>
            </div>
            <?php if (currentUser()): ?>
                    <a href="<?= url('/laws/' . $law['id'] . '/edit') ?>" class="btn btn-secondary">Bearbeiten</a>
                <form action="<?= url('/laws/' . $law['id'] . '/delete') ?>" method="POST" onsubmit="return confirm('Dieses Gesetz wirklich löschen?')">
                        <button type="submit" class="w-full btn btn-danger">Löschen</button>
                </form>
            <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-12">
        <div class="flex-1 min-w-0">
            <div class="prose-law">
                <?= $law['content_html'] ?>
            </div>
        </div>

        <!-- Sidebar: ToC -->
        <aside id="toc-container" class="hidden lg:block w-64 no-print">
            <div class="sticky top-12">
                <h3 class="text-[10px] font-bold uppercase tracking-[0.3em] text-gray-400 mb-6 border-b border-gray-100 pb-2">Gliederung</h3>
                <div id="toc-content"></div>
            </div>
        </aside>
    </div>

    <?php if (!empty($amends) || !empty($amendedBy)): ?>
        <div id="amendments" class="mt-24 pt-12 border-t-2 border-brand-accent">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                <?php if (!empty($amends)): ?>
                    <div>
                        <h3 class="text-[10px] font-bold uppercase tracking-[0.3em] text-gray-400 mb-8">Änderungshistorie (Abgehende Änderungen)</h3>
                        <div class="relative pl-8 border-l-2 border-brand-accent space-y-12">
                            <?php foreach ($amends as $a): ?>
                                <div class="relative">
                                    <div class="absolute -left-[41px] top-1.5 h-4 w-4 rounded-full bg-brand-accent border-4 border-white" :class="{ 'border-[#fdf6e3]': $store.theme.mode === 'sepia', 'border-[#242424]': $store.theme.mode === 'dark' }"></div>
                                    <a href="<?= url('/' . ($a['amended_entity_type'] === 'local_law' ? 'laws' : 'treaties') . '/' . $a['amended_entity_id']) ?>" 
                                       class="block group">
                                        <span class="text-xs font-bold uppercase tracking-widest text-gray-700 group-hover:text-brand-accent transition-colors block mb-1"><?= htmlspecialchars($a['title']) ?></span>
                                        <span class="text-[8px] text-gray-400 uppercase tracking-widest"><?= $a['amended_entity_type'] ?></span>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($amendedBy)): ?>
                    <div>
                        <h3 class="text-[10px] font-bold uppercase tracking-[0.3em] text-gray-400 mb-8">Eingehende Änderungen</h3>
                        <div class="relative pl-8 border-l-2 border-brand-accent space-y-12">
                            <?php foreach ($amendedBy as $a): ?>
                                <div class="relative">
                                    <div class="absolute -left-[41px] top-1.5 h-4 w-4 rounded-full bg-brand-accent border-4 border-white" :class="{ 'border-[#fdf6e3]': $store.theme.mode === 'sepia', 'border-[#242424]': $store.theme.mode === 'dark' }"></div>
                                    <a href="<?= url('/' . ($a['amending_entity_type'] === 'local_law' ? 'laws' : 'treaties') . '/' . $a['amending_entity_id']) ?>" 
                                       class="block group">
                                        <span class="text-xs font-bold uppercase tracking-widest text-gray-700 group-hover:text-brand-accent transition-colors block mb-1"><?= htmlspecialchars($a['title']) ?></span>
                                        <span class="text-[8px] text-gray-400 uppercase tracking-widest"><?= $a['amending_entity_type'] ?></span>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($links)): ?>
        <div class="mt-24 pt-12 border-t-2 border-gray-100">
            <h3 class="text-[10px] font-bold uppercase tracking-[0.3em] text-gray-400 mb-8 text-center">Referenzen & Links</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <?php foreach ($links as $link): ?>
                    <a href="<?= htmlspecialchars($link['url']) ?>" target="_blank" class="flex items-center justify-between p-6 bg-gray-50 border border-gray-200 hover:border-brand-accent transition-colors group">
                        <span class="text-xs font-bold uppercase tracking-widest text-gray-700 group-hover:text-gray-900"><?= htmlspecialchars($link['label'] ?: 'Dokumentation') ?></span>
                        <svg class="w-4 h-4 text-gray-400 group-hover:text-brand-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="mt-24 bg-gray-50 border border-gray-100 p-8 no-print" 
         :class="{ 'bg-[#eee2c5] border-[#e1d5b5]': $store.theme.mode === 'sepia', 'bg-[#2d2d2d] border-[#333333]': $store.theme.mode === 'dark' }">
        <h3 class="text-[10px] font-bold uppercase tracking-[0.3em] text-gray-400 mb-4">Zitationsempfehlung</h3>
        <div class="flex items-center justify-between gap-4">
            <code class="text-xs font-mono text-gray-600 select-all" :class="{ 'text-[#5e4b34]': $store.theme.mode === 'sepia', 'text-gray-400': $store.theme.mode === 'dark' }">
                <?= htmlspecialchars(config('app.name')) ?>, <?= htmlspecialchars($law['title']) ?>, Nr. <?= htmlspecialchars($law['law_number']) ?> (<?= date('Y', strtotime($law['effective_date'] ?: $law['created_at'])) ?>)
            </code>
            <button onclick="navigator.clipboard.writeText(this.previousElementSibling.textContent.trim())" class="text-[8px] font-bold uppercase tracking-widest text-brand-accent hover:underline flex-shrink-0">Kopieren</button>
        </div>
    </div>

    <div class="mt-24 pt-12 border-t border-gray-100 text-[10px] text-gray-400 font-bold uppercase tracking-[0.4em] text-center italic">
        Offizielle Veröffentlichung - <?= htmlspecialchars(config('app.name', 'IntLegis')) ?>
    </div>
</div>

