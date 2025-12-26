<div class="flex justify-between items-end mb-12 border-b-2 border-brand-accent pb-6">
    <div>
        <h1 class="text-4xl font-serif font-bold text-gray-900 tracking-tighter uppercase"><?= htmlspecialchars(config('modules.laws.label', 'Anzeiger')) ?></h1>
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-gray-500 mt-2">Amtliche Bekanntmachungen und Gesetzestexte</p>
    </div>
    <?php if (currentUser()): ?>
        <a href="<?= url('/laws/create') ?>" class="btn btn-primary">
            Neuer Eintrag
        </a>
    <?php endif; ?>
</div>

<div class="flex flex-col lg:flex-row gap-12">
    <!-- Sidebar: Filter -->
    <aside class="w-full lg:w-72">
        <div class="bg-gray-50 border-2 border-gray-900 p-8 shadow-[8px_8px_0px_0px_rgba(0,0,0,0.05)]">
            <h2 class="text-[10px] font-bold uppercase tracking-[0.3em] text-gray-900 border-b border-gray-200 pb-4 mb-8">
                Filteroptionen
            </h2>
            
            <form action="<?= url('/laws') ?>" method="GET" class="space-y-10">
                <input type="hidden" name="q" value="<?= htmlspecialchars($q ?? '') ?>">
                
                <div class="space-y-4">
                    <h3 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Kategorie</h3>
                    <select name="category" onchange="this.form.submit()" class="form-input bg-white text-[10px] font-bold uppercase tracking-widest">
                        <option value="">Alle Kategorien</option>
                        <?php foreach (config('laws.structure', []) as $key => $section): ?>
                            <optgroup label="<?= htmlspecialchars($section['label']) ?>">
                                <option value="<?= htmlspecialchars($key) ?>" <?= $selectedCategory === $key ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($section['label']) ?> (Hauptkategorie)
                                </option>
                                <?php foreach ($section['subcategories'] ?? [] as $sub): ?>
                                    <?php $val = $key . ' > ' . $sub; ?>
                                    <option value="<?= htmlspecialchars($val) ?>" <?= $selectedCategory === $val ? 'selected' : '' ?>>
                                        &nbsp;&nbsp;↳ <?= htmlspecialchars($sub) ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="space-y-4">
                    <h3 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Jahr</h3>
                    <select name="year" onchange="this.form.submit()" class="form-input bg-white text-[10px] font-bold uppercase tracking-widest">
                        <option value="">Alle Jahre</option>
                        <?php foreach ($years as $year): ?>
                            <option value="<?= htmlspecialchars($year) ?>" <?= $selectedYear === $year ? 'selected' : '' ?>><?= htmlspecialchars($year) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if ($selectedCategory || $selectedYear): ?>
                    <div class="pt-6 border-t border-gray-200">
                        <a href="<?= url('/laws') ?>" class="text-[10px] font-bold text-red-600 hover:text-red-900 uppercase tracking-widest flex items-center justify-center border border-red-100 py-2 hover:bg-red-50 transition-colors">
                            Filter zurücksetzen
                        </a>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </aside>

    <!-- Main Content: List -->
    <div class="flex-1">
        <?php if ($q): ?>
            <div class="mb-8 border-b border-gray-100 pb-4">
                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-400">Ergebnisse für: <span class="text-gray-900 normal-case italic font-serif text-lg">"<?= htmlspecialchars($q) ?>"</span></p>
            </div>
        <?php endif; ?>

        <div class="divide-y divide-gray-200">
            <?php if (empty($laws)): ?>
                <div class="text-center py-24 bg-gray-50 border border-dashed border-gray-300">
                    <p class="text-xs font-bold uppercase tracking-widest text-gray-400">Keine passenden Einträge gefunden</p>
                    <?php if ($q || $selectedCategory || $selectedYear): ?>
                        <a href="<?= url('/laws') ?>" class="mt-4 inline-block text-[10px] font-bold uppercase tracking-widest text-gray-900 border-b border-gray-900">Suche & Filter zurücksetzen</a>
                    <?php endif; ?>
        </div>
    <?php else: ?>
        <?php foreach ($laws as $law): ?>
                    <a href="<?= url('/laws/' . $law['id']) ?>" class="block group py-8 first:pt-0 last:pb-0">
                        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                            <div class="space-y-3 flex-grow">
                                <div class="flex items-center gap-4">
                                    <span class="badge"><?= htmlspecialchars($law['category']) ?></span>
                                    <span class="text-[10px] font-mono font-bold text-gray-400 uppercase tracking-widest"><?= htmlspecialchars($law['law_number']) ?></span>
                                </div>
                                <h2 class="text-2xl font-serif font-bold text-gray-900 group-hover:underline decoration-gray-300 underline-offset-8 transition-all"><?= htmlspecialchars($law['title']) ?></h2>
                            </div>
                            <div class="text-right flex flex-col items-end">
                                <span class="text-[10px] font-bold text-gray-500 uppercase tracking-[0.2em] mb-1">Datum</span>
                                <span class="text-sm font-serif italic text-gray-900">
                                    <?= $law['effective_date'] ? date('d.m.Y', strtotime($law['effective_date'])) : date('d.m.Y', strtotime($law['created_at'])) ?>
                                </span>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    <?php endif; ?>
        </div>
    </div>
</div>

