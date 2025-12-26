<tr id="country-<?= $country['id'] ?>" class="hover:bg-gray-50 transition-colors">
    <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm sm:pl-6">
        <div class="font-bold text-gray-900 uppercase tracking-tight"><?= htmlspecialchars($country['name']) ?></div>
        <div class="text-[10px] text-gray-400 font-serif italic"><?= htmlspecialchars($country['full_name'] ?? '—') ?></div>
    </td>
    <td class="whitespace-nowrap px-3 py-5 text-sm font-serif italic text-gray-600">
        <?= htmlspecialchars($country['capital'] ?? '—') ?>
    </td>
    <td class="relative whitespace-nowrap py-5 pl-3 pr-4 text-right text-[10px] font-bold uppercase tracking-widest sm:pr-6">
        <?php if (isPrimary() && currentUser()): ?>
            <button hx-get="<?= url('/countries/' . $country['id'] . '/edit') ?>" hx-target="#country-<?= $country['id'] ?>" hx-swap="outerHTML" class="text-gray-900 hover:underline mr-6">Bearbeiten</button>
        <?php endif; ?>
        <a href="<?= url('/countries/' . $country['id'] . '/history') ?>" class="text-gray-400 hover:text-gray-900">Verlauf</a>
    </td>
</tr>
