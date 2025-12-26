<div class="mb-8">
    <nav class="flex mb-4" aria-label="Breadcrumb">
        <ol role="list" class="flex items-center space-x-4">
            <li>
                <a href="<?= url('/countries') ?>" class="text-gray-400 hover:text-gray-500 text-sm font-medium"><?= htmlspecialchars(config('modules.countries.label', 'Staaten')) ?></a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                    </svg>
                    <span class="ml-4 text-sm font-medium text-gray-900"><?= htmlspecialchars($country['name']) ?> Verlauf</span>
                </div>
            </li>
        </ol>
    </nav>
    <h2 class="text-3xl font-bold text-gray-900"><?= htmlspecialchars($country['name']) ?></h2>
    <p class="mt-2 text-sm text-gray-500">Audit-Trail für die Aufzeichnungen der Vertragsstaaten.</p>
</div>

<div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
    <table class="min-w-full divide-y divide-gray-300">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Version</th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Benutzer</th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Datum</th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Snapshot-Daten</th>
                <?php if (isPrimary() && currentUser()): ?>
                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6 text-right">
                    <span class="sr-only">Aktionen</span>
                </th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            <?php foreach ($history as $index => $v): ?>
                <?php $data = json_decode($v['data_json'], true); ?>
                <tr class="<?= $index === 0 ? 'bg-indigo-50/30' : '' ?>">
                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                        <span class="inline-flex items-center justify-center h-8 w-8 rounded-full <?= $index === 0 ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-500' ?> font-bold text-xs">
                            #<?= $v['version_number'] ?>
                        </span>
                    </td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                        <?= htmlspecialchars($v['username'] ?? 'System') ?>
                    </td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                        <?= date('d.m.Y H:i', strtotime($v['created_at'])) ?> Uhr
                    </td>
                    <td class="px-3 py-4 text-sm text-gray-500">
                        <div class="font-bold text-gray-900"><?= htmlspecialchars($data['name']) ?></div>
                        <div class="text-xs text-gray-400 italic"><?= htmlspecialchars($data['full_name'] ?? '—') ?></div>
                        <div class="text-xs text-gray-400">Hauptstadt: <?= htmlspecialchars($data['capital'] ?? '—') ?></div>
                    </td>
                    <?php if (isPrimary() && currentUser()): ?>
                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                        <?php if ($v['version_number'] < count($history)): ?>
                            <form action="<?= url('/countries/' . $country['id'] . '/revert/' . $v['id']) ?>" method="POST" onsubmit="return confirm('Auf diese Version zurücksetzen?')">
                                <button type="submit" class="text-indigo-600 hover:text-indigo-900 font-bold">Wiederherstellen</button>
                            </form>
                        <?php else: ?>
                            <span class="text-gray-300 italic">Aktuell</span>
                        <?php endif; ?>
                    </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
