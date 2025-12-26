<h2 class="text-xl font-bold text-gray-900 border-b border-gray-100 pb-2">Schritt 2: Instanz-Konfiguration</h2>

<form action="<?= url('/install/instance') ?>" method="POST" class="space-y-6 pt-4" x-data="{ mode: 'PRIMARY' }">
    <div>
        <label class="block text-sm font-medium text-gray-700">Instanz-Modus</label>
        <select name="mode" x-model="mode" class="form-input">
            <option value="PRIMARY">Primär (Schreibzugriff erlaubt)</option>
            <option value="SECONDARY">Sekundär (Nur Lesezugriff / Replikation)</option>
        </select>
    </div>

    <div x-show="mode === 'PRIMARY'" class="bg-indigo-50 p-4 rounded-md">
        <p class="text-xs text-indigo-700">Diese Instanz erlaubt die Erstellung von Dokumenten und dient als Quelle für Repliken.</p>
    </div>

    <div x-show="mode === 'SECONDARY'" class="space-y-4">
        <div class="bg-amber-50 p-4 rounded-md">
            <p class="text-xs text-amber-700">Repliken rufen Daten von einem Primärknoten ab. Sie können auf dieser Instanz keine Dokumente bearbeiten.</p>
            <p class="text-[10px] text-amber-600 mt-2 font-bold uppercase tracking-tight">Hinweis: Nur Völkerrecht (Verträge & Staaten) wird repliziert. Der Anzeiger bleibt lokal.</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Primäre URL</label>
            <input type="url" name="primary_url" class="form-input" placeholder="https://main-instance.com">
        </div>
    </div>

    <div class="pt-6 border-t border-gray-100 space-y-4">
        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Anpassung</h3>
        <div>
            <label class="block text-sm font-medium text-gray-700">Name der Anwendung</label>
            <input type="text" name="app_name" class="form-input" value="<?= htmlspecialchars(config('app.name', 'IntLegis')) ?>">
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Primärfarbe</label>
                <div class="flex items-center gap-2 mt-1">
                    <input type="color" name="primary_color" class="h-8 w-8 rounded border-gray-300" value="#1e3a8a">
                    <input type="text" name="primary_color_text" class="form-input !mt-0" value="#1e3a8a" x-on:input="$event.target.previousElementSibling.value = $event.target.value">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Sekundärfarbe</label>
                <div class="flex items-center gap-2 mt-1">
                    <input type="color" name="secondary_color" class="h-8 w-8 rounded border-gray-300" value="#4f46e5">
                    <input type="text" name="secondary_color_text" class="form-input !mt-0" value="#4f46e5" x-on:input="$event.target.previousElementSibling.value = $event.target.value">
                </div>
            </div>
        </div>
    </div>

    <div class="pt-4 border-t border-gray-100 flex gap-4">
        <a href="<?= url('/install') ?>" class="w-1/2 btn border border-gray-300 text-gray-700 justify-center">Zurück</a>
        <button type="submit" class="w-1/2 btn btn-primary justify-center">
            <span x-text="mode === 'PRIMARY' ? 'Weiter zum Administrator' : 'Installation abschließen'"></span>
        </button>
    </div>
</form>

<script src="<?= url('/assets/vendor/js/alpine.min.js') ?>" defer></script>
