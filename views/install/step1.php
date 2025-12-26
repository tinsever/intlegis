<h2 class="text-xl font-bold text-gray-900 border-b border-gray-100 pb-2">Schritt 1: Datenbank-Setup</h2>

<form action="<?= url('/install/db') ?>" method="POST" class="space-y-6 pt-4" x-data="{ driver: 'sqlite' }">
    <div>
        <label class="block text-sm font-medium text-gray-700">Datenbank-Treiber</label>
        <select name="driver" x-model="driver" class="form-input">
            <option value="sqlite">SQLite (Lokale Datei)</option>
            <option value="mysql">MySQL / MariaDB</option>
        </select>
    </div>

    <div x-show="driver === 'sqlite'" class="bg-indigo-50 p-4 rounded-md">
        <p class="text-xs text-indigo-700">Eine lokale Datei namens <span class="font-mono font-bold">data.sqlite</span> wird im Projektverzeichnis erstellt. Keine weitere Konfiguration erforderlich.</p>
    </div>

    <div x-show="driver === 'mysql'" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Host</label>
            <input type="text" name="host" class="form-input" value="127.0.0.1">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Datenbankname</label>
            <input type="text" name="dbname" class="form-input" value="intlegis">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Benutzername</label>
            <input type="text" name="user" class="form-input" value="root">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Passwort</label>
            <input type="password" name="pass" class="form-input">
        </div>
    </div>

    <div class="pt-4 border-t border-gray-100">
        <button type="submit" class="w-full btn btn-primary justify-center">Weiter zur Instanz-Konfiguration</button>
    </div>
</form>

<script src="<?= url('/assets/vendor/js/alpine.min.js') ?>" defer></script>
