<div class="sm:flex sm:items-end mb-12 border-b-2 border-brand-accent pb-6">
    <div class="sm:flex-auto">
        <h1 class="text-4xl font-serif font-bold text-gray-900 tracking-tighter uppercase">Internationale Verträge</h1>
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-gray-500 mt-2">Datenbank der registrierten internationalen Verträge und Protokolle</p>
    </div>
    <?php if (isPrimary() && currentUser()): ?>
    <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
        <a href="<?= url('/treaties/create') ?>" class="btn btn-primary">
            Vertrag hinzufügen
        </a>
    </div>
    <?php endif; ?>
</div>

<div class="mt-8 flex flex-col">
    <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
            <div class="overflow-hidden border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="py-4 pl-4 pr-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-widest sm:pl-6">Stabiler Bezeichner</th>
                            <th scope="col" class="px-3 py-4 text-left text-[10px] font-bold text-gray-500 uppercase tracking-widest">Typ</th>
                            <th scope="col" class="px-3 py-4 text-left text-[10px] font-bold text-gray-500 uppercase tracking-widest">Status</th>
                            <th scope="col" class="px-3 py-4 text-left text-[10px] font-bold text-gray-500 uppercase tracking-widest">Unterzeichnung</th>
                            <th scope="col" class="relative py-4 pl-3 pr-4 sm:pr-6 text-right">
                                <span class="sr-only">Aktionen</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        <?php foreach ($treaties as $t): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm sm:pl-6">
                                    <div class="font-bold text-gray-900 uppercase tracking-tight"><?= htmlspecialchars($t['stable_id']) ?></div>
                                    <div class="text-[10px] font-mono text-gray-400">REF-<?= str_pad($t['id'], 5, '0', STR_PAD_LEFT) ?></div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-5">
                                    <span class="badge"><?= htmlspecialchars($t['type']) ?></span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-5">
                                    <?php
                                    $statusNames = [
                                        'in_force' => 'In Kraft',
                                        'draft' => 'Entwurf',
                                        'signed' => 'Unterzeichnet',
                                        'ratified' => 'Ratifiziert',
                                        'suspended' => 'Suspendiert',
                                        'terminated' => 'Beendet',
                                    ];
                                    $statusName = $statusNames[$t['status']] ?? str_replace('_', ' ', $t['status']);
                                    ?>
                                    <span class="text-[10px] font-bold uppercase tracking-widest <?= $t['status'] === 'in_force' ? 'text-green-700' : 'text-gray-500' ?>">
                                        <?= htmlspecialchars($statusName) ?>
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-5 text-sm font-serif italic text-gray-600">
                                    <?= htmlspecialchars($t['signature_date'] ?? '—') ?>
                                </td>
                                <td class="relative whitespace-nowrap py-5 pl-3 pr-4 text-right text-[10px] font-bold uppercase tracking-widest sm:pr-6">
                                    <a href="<?= url('/treaties/' . $t['id']) ?>" class="text-gray-900 hover:underline <?= (isPrimary() && currentUser()) ? 'mr-6' : '' ?>">Details</a>
                                    <?php if (isPrimary() && currentUser()): ?>
                                        <a href="<?= url('/treaties/' . $t['id'] . '/edit') ?>" class="text-gray-400 hover:text-gray-900">Bearbeiten</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if (empty($treaties)): ?>
                    <div class="text-center py-24 bg-gray-50 border-t border-gray-100">
                        <h3 class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em]">Keine Einträge in der Datenbank</h3>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
