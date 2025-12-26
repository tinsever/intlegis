<h2 class="text-xl font-bold text-gray-900 border-b border-gray-100 pb-2">Schritt 3: Administrator erstellen</h2>

<form action="<?= url('/install/admin') ?>" method="POST" class="space-y-6 pt-4">
    <?php if (isset($error)): ?>
        <div class="bg-red-50 text-red-700 text-xs p-3 rounded-md"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div>
        <label class="block text-sm font-medium text-gray-700">Admin-Benutzername</label>
        <input type="text" name="username" required class="form-input" placeholder="admin">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Admin-Passwort</label>
        <input type="password" name="password" required class="form-input">
        <p class="mt-1 text-[10px] text-gray-400 font-medium tracking-tight">Muss mindestens 6 Zeichen lang sein.</p>
    </div>

    <div class="pt-4 border-t border-gray-100 flex gap-4">
        <button type="submit" class="w-full btn btn-primary justify-center uppercase tracking-widest text-xs font-black">Installation abschließen</button>
    </div>
</form>
