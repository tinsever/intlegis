<tr id="country-<?= $country['id'] ?>" class="bg-indigo-50/50">
    <td colspan="3" class="p-6">
        <form hx-post="<?= url('/countries/' . $country['id'] . '/update') ?>" hx-target="#country-<?= $country['id'] ?>" hx-swap="outerHTML" class="flex flex-col md:flex-row items-end gap-4">
            <div class="flex-1">
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Kurzbezeichnung</label>
                <input type="text" name="name" value="<?= htmlspecialchars($country['name']) ?>" required class="form-input">
            </div>
            <div class="flex-1">
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Vollständiger Name</label>
                <input type="text" name="full_name" value="<?= htmlspecialchars($country['full_name'] ?? '') ?>" class="form-input">
            </div>
            <div class="flex-1">
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Hauptstadt</label>
                <input type="text" name="capital" value="<?= htmlspecialchars($country['capital'] ?? '') ?>" class="form-input">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary text-xs">Speichern</button>
                <button hx-get="<?= url('/countries/' . $country['id']) ?>" hx-target="#country-<?= $country['id'] ?>" hx-swap="outerHTML" class="btn btn-secondary text-xs">Abbrechen</button>
            </div>
        </form>
    </td>
</tr>
