<div class="mb-16 border-b-2 border-brand-accent pb-8">
    <h1 class="text-4xl font-serif font-bold text-gray-900 tracking-tighter uppercase text-center md:text-left">Globale Suche</h1>
    <p class="text-xs font-bold uppercase tracking-[0.2em] text-gray-500 mt-4 text-center md:text-left">
        Abfrage: <span class="text-gray-900 italic font-serif normal-case text-lg ml-2">"<?= htmlspecialchars($q) ?>"</span>
    </p>
</div>

<div class="space-y-16">
    <!-- Laws Results -->
    <section>
        <h2 class="text-[10px] font-bold uppercase tracking-[0.3em] text-gray-400 mb-8 border-b border-gray-100 pb-2">
            <?= htmlspecialchars(config('modules.laws.label', 'Anzeiger')) ?> (<?= count($laws) ?>)
        </h2>
        <div class="divide-y divide-gray-100">
            <?php if (empty($laws)): ?>
                <p class="text-[10px] text-gray-400 italic uppercase py-4">Keine Einträge gefunden</p>
            <?php else: ?>
                <?php foreach ($laws as $law): ?>
                    <a href="<?= url('/laws/' . $law['id']) ?>" class="block group py-6 first:pt-0 last:pb-0">
                        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                            <div class="space-y-2">
                                <div class="flex items-center gap-3">
                                    <span class="badge"><?= htmlspecialchars($law['category']) ?></span>
                                    <span class="text-[10px] font-mono font-bold text-gray-400 uppercase"><?= htmlspecialchars($law['law_number']) ?></span>
                                </div>
                                <h3 class="text-xl font-serif font-bold text-gray-900 group-hover:underline decoration-gray-300"><?= htmlspecialchars($law['title']) ?></h3>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- Treaties Results -->
    <section>
        <h2 class="text-[10px] font-bold uppercase tracking-[0.3em] text-gray-400 mb-8 border-b border-gray-100 pb-2">
            <?= htmlspecialchars(config('modules.treaties.label', 'Verträge')) ?> (<?= count($treaties) ?>)
        </h2>
        <div class="divide-y divide-gray-100">
            <?php if (empty($treaties)): ?>
                <p class="text-[10px] text-gray-400 italic uppercase py-4">Keine Einträge gefunden</p>
            <?php else: ?>
                <?php foreach ($treaties as $t): ?>
                    <a href="<?= url('/treaties/' . $t['id']) ?>" class="block group py-6 first:pt-0 last:pb-0">
                        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                            <div class="space-y-2">
                                <div class="flex items-center gap-3">
                                    <span class="badge"><?= htmlspecialchars($t['type']) ?></span>
                                    <span class="text-[10px] font-mono font-bold text-gray-400 uppercase"><?= htmlspecialchars($t['stable_id']) ?></span>
                                </div>
                                <h3 class="text-xl font-serif font-bold text-gray-900 group-hover:underline decoration-gray-300"><?= htmlspecialchars($t['stable_id']) ?></h3>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php if (empty($laws) && empty($treaties)): ?>
    <div class="text-center py-24 bg-gray-50 border border-dashed border-gray-300">
        <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Keine passenden Einträge in der gesamten Datenbank gefunden</p>
        <a href="<?= url('/') ?>" class="mt-8 inline-block btn btn-secondary">Zur Startseite</a>
    </div>
<?php endif; ?>

