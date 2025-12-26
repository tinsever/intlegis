<nav class="flex mb-12 border-b border-gray-100 pb-4" aria-label="Breadcrumb">
    <ol role="list" class="flex items-center space-x-4">
        <li>
            <a href="<?= url('/treaties') ?>" class="text-[10px] font-bold uppercase tracking-widest text-gray-400 hover:text-gray-900 transition-colors">Verträge</a>
        </li>
        <li>
            <div class="flex items-center">
                <span class="text-gray-300 mx-2">/</span>
                <span class="text-[10px] font-bold uppercase tracking-widest text-gray-900"><?= htmlspecialchars($treaty['stable_id']) ?></span>
            </div>
        </li>
    </ol>
</nav>

<?php if (!empty($amendedBy)): ?>
    <div class="mb-12 border border-brand-accent bg-brand-accent/5 p-6 flex items-center gap-4 no-print">
        <svg class="w-6 h-6 text-brand-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        <div class="text-[10px] font-bold uppercase tracking-widest">
            <p class="text-brand-accent mb-1">Hinweis zur Aktualität</p>
            <p class="text-gray-900">Dieser Rechtsakt wurde durch nachfolgende Bestimmungen geändert. Prüfen Sie die <a href="#amendments" class="underline">Änderungshistorie</a>.</p>
        </div>
    </div>
<?php endif; ?>

<div class="flex flex-col md:flex-row md:items-end md:justify-between mb-16 gap-8 pb-8 border-b-4 border-brand-accent">
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-3 mb-4">
            <span class="badge"><?= htmlspecialchars($treaty['type']) ?></span>
            <span class="text-[10px] font-mono font-bold text-gray-400 uppercase tracking-widest">REF-<?= str_pad($treaty['id'], 5, '0', STR_PAD_LEFT) ?></span>
        </div>
        <h2 class="text-5xl font-serif font-bold text-gray-900 leading-tight uppercase tracking-tighter">
            <?= htmlspecialchars($treaty['stable_id']) ?>
        </h2>
        <div class="mt-6 flex flex-wrap gap-8 text-[10px] font-bold uppercase tracking-[0.2em] text-gray-500">
            <div class="flex flex-col">
                <span class="text-gray-400 mb-1">Unterzeichnet am</span>
                <span class="text-gray-900 font-serif italic normal-case text-sm"><?= htmlspecialchars($treaty['signature_date'] ?? 'Nicht dokumentiert') ?></span>
            </div>
            <div class="flex flex-col">
                <span class="text-gray-400 mb-1">Status</span>
                <?php
                $statusNames = [
                    'in_force' => 'In Kraft',
                    'draft' => 'Entwurf',
                    'signed' => 'Unterzeichnet',
                    'ratified' => 'Ratifiziert',
                    'suspended' => 'Suspendiert',
                    'terminated' => 'Beendet',
                ];
                $statusName = $statusNames[$treaty['status']] ?? str_replace('_', ' ', $treaty['status']);
                ?>
                <span class="<?= $treaty['status'] === 'in_force' ? 'text-green-700' : 'text-gray-900' ?>"><?= htmlspecialchars($statusName) ?></span>
            </div>
        </div>
    </div>
    <div class="flex flex-wrap gap-4">
        <div x-data="{ open: false }" class="relative inline-block text-left">
            <button @click="open = !open" type="button" class="btn btn-secondary">
                Export
            </button>
            <div x-show="open" @click.away="open = false" class="origin-top-right absolute right-0 mt-2 w-48 bg-white border border-gray-900 z-10 shadow-xl">
                <div class="py-1">
                    <a href="<?= url('/export?type=treaty&id=' . $treaty['id'] . '&format=pdf') ?>" target="_blank" class="block px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-700 hover:bg-gray-50 border-b border-gray-100 last:border-0">Als PDF</a>
                    <a href="<?= url('/export?type=treaty&id=' . $treaty['id'] . '&format=html') ?>" class="block px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-700 hover:bg-gray-50 border-b border-gray-100 last:border-0">Als HTML</a>
                    <a href="<?= url('/export?type=treaty&id=' . $treaty['id'] . '&format=txt') ?>" class="block px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-700 hover:bg-gray-50 border-b border-gray-100 last:border-0">Als Text (TXT)</a>
                </div>
            </div>
        </div>
        <a href="<?= url('/treaties/' . $treaty['id'] . '/history') ?>" class="btn btn-secondary">
            Verlauf
        </a>
        <?php if (isPrimary() && currentUser()): ?>
        <a href="<?= url('/treaties/' . $treaty['id'] . '/edit') ?>" class="btn btn-primary">
            Bearbeiten
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-12">
    <div class="lg:col-span-3">
        <div class="flex flex-col lg:flex-row gap-12">
            <div class="flex-1 min-w-0">
                <div class="prose-treaty">
                    <?= $treaty['content_html'] ?: '<p class="text-gray-400 italic font-serif">Kein Dokumenteninhalt in der Datenbank hinterlegt.</p>' ?>
                </div>
            </div>

            <!-- Sidebar: ToC -->
            <aside id="toc-container" class="hidden lg:block w-48 no-print">
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
                            <h3 class="text-[10px] font-bold uppercase tracking-[0.3em] text-gray-400 mb-8">Ändert folgende Rechtsakte</h3>
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
                            <h3 class="text-[10px] font-bold uppercase tracking-[0.3em] text-gray-400 mb-8">Geändert durch</h3>
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
    </div>

    <div class="space-y-12">
        <section>
            <h3 class="text-[10px] font-bold uppercase tracking-[0.3em] text-gray-400 mb-6 border-b border-gray-100 pb-2">Details</h3>
            <dl class="space-y-6">
                <div>
                    <dt class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">In Kraft seit</dt>
                    <dd class="mt-1 text-sm font-serif italic text-gray-900"><?= htmlspecialchars($treaty['in_force_date'] ?? 'N/A') ?></dd>
                </div>
                <div>
                    <dt class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Laufzeit</dt>
                    <dd class="mt-1 text-sm font-serif italic text-gray-900"><?= htmlspecialchars($treaty['duration'] ?? 'N/A') ?></dd>
                </div>
            </dl>
        </section>

        <section>
            <h3 class="text-[10px] font-bold uppercase tracking-[0.3em] text-gray-400 mb-6 border-b border-gray-100 pb-2"><?= htmlspecialchars(config('modules.countries.label', 'Vertragsstaaten')) ?></h3>
            <ul class="divide-y divide-gray-50">
                <?php foreach ($parties as $p): ?>
                    <li>
                        <a href="<?= url('/countries/' . $p['id'] . '/history') ?>" class="group flex items-center justify-between py-3">
                            <span class="text-xs font-bold uppercase tracking-widest text-gray-700 group-hover:text-gray-900"><?= htmlspecialchars($p['name']) ?></span>
                            <svg class="h-3 w-3 text-gray-300 group-hover:text-gray-900 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </a>
                    </li>
                <?php endforeach; ?>
                <?php if (empty($parties)): ?>
                    <li class="py-3 text-[10px] text-gray-400 italic uppercase tracking-widest">Keine Einträge registriert</li>
                <?php endif; ?>
            </ul>
        </section>

        <?php if (!empty($tags)): ?>
        <section>
            <h3 class="text-[10px] font-bold uppercase tracking-[0.3em] text-gray-400 mb-6 border-b border-gray-100 pb-2">Klassifizierung</h3>
            <div class="flex flex-wrap gap-2">
                <?php foreach ($tags as $tag): ?>
                    <span class="badge"><?= htmlspecialchars($tag) ?></span>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <?php if (!empty($links)): ?>
        <section>
            <h3 class="text-[10px] font-bold uppercase tracking-[0.3em] text-gray-400 mb-6 border-b border-gray-100 pb-2">Externe Quellen</h3>
            <ul class="space-y-4">
                <?php foreach ($links as $link): ?>
                    <li>
                        <a href="<?= htmlspecialchars($link['url']) ?>" target="_blank" class="group block">
                            <span class="text-[10px] font-bold text-gray-700 group-hover:text-brand-accent uppercase tracking-widest break-all"><?= htmlspecialchars($link['label'] ?: 'Dokumentation') ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
        <?php endif; ?>
    </div>
</div>

<div class="mt-24 bg-gray-50 border border-gray-100 p-8 no-print" 
     :class="{ 'bg-[#eee2c5] border-[#e1d5b5]': $store.theme.mode === 'sepia', 'bg-[#2d2d2d] border-[#333333]': $store.theme.mode === 'dark' }">
    <h3 class="text-[10px] font-bold uppercase tracking-[0.3em] text-gray-400 mb-4">Zitationsempfehlung</h3>
    <div class="flex items-center justify-between gap-4">
        <code class="text-xs font-mono text-gray-600 select-all" :class="{ 'text-[#5e4b34]': $store.theme.mode === 'sepia', 'text-gray-400': $store.theme.mode === 'dark' }">
            <?= htmlspecialchars(config('app.name')) ?>, <?= htmlspecialchars($treaty['stable_id']) ?> (REF-<?= str_pad($treaty['id'], 5, '0', STR_PAD_LEFT) ?>), Unterzeichnet <?= htmlspecialchars($treaty['signature_date'] ?: 'N/A') ?>
        </code>
        <button onclick="navigator.clipboard.writeText(this.previousElementSibling.textContent.trim())" class="text-[8px] font-bold uppercase tracking-widest text-brand-accent hover:underline flex-shrink-0">Kopieren</button>
    </div>
</div>

<div class="mt-24 pt-12 border-t border-gray-100 text-[10px] text-gray-400 font-bold uppercase tracking-[0.4em] text-center italic">
    Amtliches Dokumentationssystem - <?= htmlspecialchars(config('app.name', 'IntLegis')) ?>
</div>
