<div class="mb-8">
    <nav class="flex mb-4" aria-label="Breadcrumb">
        <ol role="list" class="flex items-center space-x-4">
            <li>
                <a href="<?= url('/treaties') ?>" class="text-gray-400 hover:text-gray-500">Verträge</a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                    </svg>
                    <a href="<?= url('/treaties/' . $treaty['id']) ?>" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700"><?= htmlspecialchars($treaty['stable_id']) ?></a>
                </div>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                    </svg>
                    <span class="ml-4 text-sm font-medium text-gray-900">Audit-Verlauf</span>
                </div>
            </li>
        </ol>
    </nav>
    <h2 class="text-3xl font-bold text-gray-900">Versionsverlauf</h2>
    <p class="mt-2 text-sm text-gray-500">Jede Änderung am Vertrag wird unten protokolliert. <?php if (isPrimary() && currentUser()): ?>Sie können zu jeder vorherigen Version zurückkehren.<?php endif; ?></p>
</div>

<div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
    <table class="min-w-full divide-y divide-gray-300">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Version</th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Bearbeiter</th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Datum</th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Snapshot-Details</th>
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
                        <?php if ($index === 0): ?>
                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800">Aktuell</span>
                        <?php endif; ?>
                    </td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                        <div class="flex items-center">
                            <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 font-bold text-xs mr-2">
                                <?= strtoupper(substr($v['username'] ?? 'S', 0, 1)) ?>
                            </div>
                            <?= htmlspecialchars($v['username'] ?? 'System') ?>
                        </div>
                    </td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                        <div class="text-gray-900"><?= date('d. M Y', strtotime($v['created_at'])) ?></div>
                        <div class="text-xs text-gray-400"><?= date('H:i', strtotime($v['created_at'])) ?> Uhr</div>
                    </td>
                    <td class="px-3 py-4 text-sm text-gray-500 max-w-xs truncate">
                        <span class="badge uppercase tracking-tighter text-[10px]"><?= htmlspecialchars($data['type']) ?></span>
                        <span class="badge uppercase tracking-tighter text-[10px]"><?= htmlspecialchars($data['status']) ?></span>
                        <div class="mt-1 text-xs text-gray-400 italic">
                            <?= mb_strimwidth(strip_tags($data['content_html']), 0, 50, '...') ?>
                        </div>
                    </td>
                    <?php if (isPrimary() && currentUser()): ?>
                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                        <?php if ($index > 0): ?>
                            <form action="<?= url('/treaties/' . $treaty['id'] . '/revert/' . $v['id']) ?>" method="POST" onsubmit="return confirm('Sind Sie sicher, dass Sie auf Version #<?= $v['version_number'] ?> zurücksetzen möchten? Dies erstellt eine neue aktuelle Version.')">
                                <button type="submit" class="text-indigo-600 hover:text-indigo-900 font-bold">Wiederherstellen</button>
                            </form>
                        <?php else: ?>
                            <span class="text-gray-300 italic">Keine Aktion</span>
                        <?php endif; ?>
                    </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
