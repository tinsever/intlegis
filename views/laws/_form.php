<div class="space-y-12">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="space-y-2">
            <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-500">Titel des Eintrags</label>
            <input type="text" name="title" required class="form-input" value="<?= htmlspecialchars($law['title'] ?? '') ?>" placeholder="z.B. Gesetz über die öffentliche Sicherheit">
        </div>
        <div class="space-y-2">
            <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-500">Gesetzesnummer / Aktenzeichen</label>
            <input type="text" name="law_number" class="form-input" value="<?= htmlspecialchars($law['law_number'] ?? '') ?>" placeholder="z.B. GBl. 2025 Nr. 12">
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="space-y-2">
            <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-500">Kategorie (Bereich)</label>
            <select name="category" class="form-input">
                <?php foreach (config('laws.structure', []) as $key => $section): ?>
                    <optgroup label="<?= htmlspecialchars($section['label']) ?>">
                        <option value="<?= htmlspecialchars($key) ?>" <?= ($law['category'] ?? '') === $key ? 'selected' : '' ?>>
                            <?= htmlspecialchars($section['label']) ?> (Hauptkategorie)
                        </option>
                        <?php foreach ($section['subcategories'] ?? [] as $sub): ?>
                            <?php $val = $key . ' > ' . $sub; ?>
                            <option value="<?= htmlspecialchars($val) ?>" <?= ($law['category'] ?? '') === $val ? 'selected' : '' ?>>
                                &nbsp;&nbsp;↳ <?= htmlspecialchars($sub) ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="space-y-2">
            <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-500">Veröffentlichungsdatum</label>
            <input type="date" name="publication_date" class="form-input" value="<?= $law['publication_date'] ?? '' ?>">
        </div>
        <div class="space-y-2">
            <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-500">Inkrafttreten</label>
            <input type="date" name="effective_date" class="form-input" value="<?= $law['effective_date'] ?? '' ?>">
        </div>
    </div>

    <div class="space-y-4">
        <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-500">Gesetzestext / Inhalt</label>
        <div class="border border-gray-200">
        <?php 
            $content_html = $law['content_html'] ?? '';
            include '_editor.php'; 
        ?>
    </div>
    </div>

    <div class="pt-12 border-t border-gray-100" x-data="{ 
        amendments: <?= json_encode(array_map(function($a) { return ['type' => $a['amended_entity_type'], 'id' => $a['amended_entity_id'], 'title' => $a['title']]; }, $amends ?? [])) ?>,
        addAmendment(type, id, title) {
            if (!this.amendments.find(a => a.type === type && a.id === id)) {
                this.amendments.push({type, id, title});
            }
            document.getElementById('search-results').innerHTML = '';
            this.$refs.searchInput.value = '';
        },
        removeAmendment(index) {
            this.amendments.splice(index, 1);
        }
    }">
        <h3 class="text-[10px] font-bold uppercase tracking-widest text-gray-900 mb-6 border-b border-gray-200 pb-2">Ändert folgende Rechtsakte</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="space-y-4">
                <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-400">Rechtsakt suchen</label>
                <div class="relative" @click.away="document.getElementById('search-results').innerHTML = ''">
                    <input type="text" 
                           class="form-input" 
                           placeholder="Titel oder Nummer eingeben..."
                           hx-get="<?= url('/api/search-entities') ?>" 
                           hx-trigger="keyup changed delay:300ms" 
                           hx-target="#search-results"
                           name="q"
                           x-ref="searchInput"
                           autocomplete="off">
                    <div id="search-results" class="absolute z-20 w-full mt-1 bg-white border border-gray-200 shadow-xl max-h-60 overflow-y-auto"></div>
                </div>
            </div>

            <div class="space-y-4">
                <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-400">Ausgewählte Änderungen</label>
                <div class="space-y-2">
                    <template x-for="(a, index) in amendments" :key="a.type + '-' + a.id">
                        <div class="flex items-center justify-between p-3 bg-gray-50 border border-gray-100 group">
                            <div class="flex flex-col">
                                <span class="text-[10px] font-bold uppercase tracking-widest text-gray-900" x-text="a.title"></span>
                                <span class="text-[8px] text-gray-400 font-mono uppercase" x-text="a.type + ':' + a.id"></span>
                            </div>
                            <button type="button" @click="removeAmendment(index)" class="text-gray-300 hover:text-red-600 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                            <input type="hidden" name="amendments[]" :value="a.type + ':' + a.id">
                        </div>
                    </template>
                    <div x-show="amendments.length === 0" class="p-3 border border-dashed border-gray-200 text-center">
                        <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Keine Verknüpfungen</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="pt-12 border-t border-gray-100">
        <h3 class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-6 text-center">Referenzen & Dokumente</h3>
        <?php 
            $links = $links ?? [];
            include __DIR__ . '/../_links_manager.php'; 
        ?>
    </div>

    <div class="flex items-center gap-6 pt-12 border-t-2 border-brand-accent">
        <button type="submit" class="btn btn-primary min-w-[150px] justify-center text-center">Speichern</button>
        <a href="<?= url('/laws') ?>" class="btn btn-secondary min-w-[150px] justify-center text-center text-gray-500">Abbrechen</a>
    </div>
</div>

