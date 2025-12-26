<div class="md:flex md:items-center md:justify-between mb-8 pb-4 border-b border-gray-100">
    <div class="flex-1 min-w-0">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
            Neuen Vertragsdatensatz erstellen
        </h2>
        <p class="mt-1 text-sm text-gray-500">Füllen Sie die technischen Metadaten und den Dokumenteninhalt unten aus.</p>
    </div>
</div>

<form action="/treaties/store" method="POST" class="space-y-8">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Technical Metadata -->
        <div class="space-y-6">
            <div class="bg-gray-50 p-6 rounded-xl border border-gray-200 shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-4">Kernidentifikation</h3>
                
                <div>
                    <label for="stable_id" class="block text-sm font-medium text-gray-700">Stabiler Bezeichner (Feste ID)</label>
                    <input type="text" name="stable_id" id="stable_id" required class="form-input" placeholder="z.B. UN-2023-001">
                    <p class="mt-1 text-xs text-gray-400 font-medium tracking-tight">Diese ID sollte sich nach der Erstellung nie wieder ändern.</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700">Vertragstyp</label>
                        <select name="type" id="type" class="form-input">
                            <option value="bilateral">Bilateral</option>
                            <option value="multilateral">Multilateral</option>
                            <option value="accession">Beitritt</option>
                            <option value="protocol">Protokoll</option>
                            <option value="termination">Kündigung</option>
                            <option value="mou">MOU (Absichtserklärung)</option>
                        </select>
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Aktueller Status</label>
                        <select name="status" id="status" class="form-input">
                            <option value="draft">Entwurf</option>
                            <option value="signed">Unterzeichnet</option>
                            <option value="ratified">Ratifiziert</option>
                            <option value="in_force">In Kraft</option>
                            <option value="suspended">Suspendiert</option>
                            <option value="terminated">Beendet</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 p-6 rounded-xl border border-gray-200 shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-4">Daten & Zeitplan</h3>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="signature_date" class="block text-sm font-medium text-gray-700">Unterzeichnungsdatum</label>
                        <input type="date" name="signature_date" id="signature_date" class="form-input">
                    </div>
                    <div>
                        <label for="in_force_date" class="block text-sm font-medium text-gray-700">In Kraft seit</label>
                        <input type="date" name="in_force_date" id="in_force_date" class="form-input">
                    </div>
                </div>

                <div>
                    <label for="duration" class="block text-sm font-medium text-gray-700">Laufzeit / Kündigungsfrist</label>
                    <input type="text" name="duration" id="duration" class="form-input" placeholder="z.B. 10 Jahre, 6 Monate Kündigungsfrist">
                </div>

                <div>
                    <label for="tags" class="block text-sm font-medium text-gray-700">Thematische Tags (kommagetrennt)</label>
                    <input type="text" name="tags" id="tags" class="form-input" placeholder="Handel, Verteidigung, Kultur">
                </div>
            </div>
        </div>

        <!-- Parties Selection -->
        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm flex flex-col h-full">
            <h3 class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-4">Vertragsstaaten</h3>
            <div class="flex-grow overflow-y-auto pr-2 space-y-2 max-h-[460px]">
                <?php foreach ($countries as $c): ?>
                    <label class="relative flex items-start p-3 rounded-lg border border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors group">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="parties[]" value="<?= $c['id'] ?>" class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        </div>
                        <div class="ml-3 text-sm">
                            <span class="font-bold text-gray-900 group-hover:text-indigo-600 transition-colors"><?= htmlspecialchars($c['name']) ?></span>
                            <span class="block text-gray-400 text-xs"><?= htmlspecialchars($c['capital'] ?? 'Keine Hauptstadt') ?></span>
                        </div>
                    </label>
                <?php endforeach; ?>
                <?php if (empty($countries)): ?>
                    <div class="text-center py-12 text-gray-400">
                        <p>Keine Einträge verfügbar.</p>
                        <a href="<?= url('/countries') ?>" class="text-indigo-600 hover:underline text-sm font-bold">Zuerst <?= htmlspecialchars(config('modules.countries.label', 'Staaten')) ?> hinzufügen</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm" x-data="{ 
        amendments: <?= json_encode(array_map(function($a) { return ['type' => $a['amended_entity_type'], 'id' => $a['amended_entity_id'], 'title' => $a['title']]; }, $amends ?? [])) ?>,
        addAmendment(type, id, title) {
            if (!this.amendments.find(a => a.type === type && a.id === id)) {
                this.amendments.push({type, id, title});
            }
            document.getElementById('search-results-treaty').innerHTML = '';
            this.$refs.searchInput.value = '';
        },
        removeAmendment(index) {
            this.amendments.splice(index, 1);
        }
    }">
        <h3 class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-4">Offizieller Dokumenteninhalt</h3>
        <div class="mt-2">
            <?php include __DIR__ . '/_editor.php'; ?>
        </div>

        <div class="mt-12 pt-8 border-t border-gray-100">
            <h3 class="text-[10px] font-bold uppercase tracking-widest text-gray-900 mb-6 border-b border-gray-200 pb-2">Ändert folgende Rechtsakte</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-4">
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-400">Rechtsakt suchen</label>
                    <div class="relative" @click.away="document.getElementById('search-results-treaty').innerHTML = ''">
                        <input type="text" 
                               class="form-input" 
                               placeholder="Titel oder Nummer eingeben..."
                               hx-get="<?= url('/api/search-entities') ?>" 
                               hx-trigger="keyup changed delay:300ms" 
                               hx-target="#search-results-treaty"
                               name="q"
                               x-ref="searchInput"
                               autocomplete="off">
                        <div id="search-results-treaty" class="absolute z-20 w-full mt-1 bg-white border border-gray-200 shadow-xl max-h-60 overflow-y-auto"></div>
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

        <div class="mt-12 pt-8 border-t border-gray-100">
            <?php include __DIR__ . '/../_links_manager.php'; ?>
        </div>
    </div>

    <!-- Form Actions -->
    <div class="flex items-center justify-end space-x-4 pt-4 border-t border-gray-100">
        <a href="/treaties" class="btn btn-secondary">Abbrechen</a>
        <button type="submit" class="btn btn-primary px-8">
            Vertrag speichern
        </button>
    </div>
</form>
