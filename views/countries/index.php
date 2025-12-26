<div class="sm:flex sm:items-end mb-12 border-b-2 border-brand-accent pb-6">
    <div class="sm:flex-auto">
        <h1 class="text-4xl font-serif font-bold text-gray-900 tracking-tighter uppercase"><?= htmlspecialchars(config('modules.countries.label', 'Staaten')) ?></h1>
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-gray-500 mt-2">Verwaltung der völkerrechtlichen Akteure</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-12">
    <!-- Add Country Form -->
    <?php if (isPrimary() && currentUser()): ?>
    <div class="lg:col-span-1">
        <div class="bg-gray-50 border-2 border-gray-900 p-8 shadow-[8px_8px_0px_0px_rgba(0,0,0,0.05)]">
            <h3 class="text-[10px] font-bold uppercase tracking-widest text-gray-900 mb-8 border-b border-gray-200 pb-2">Neuer Eintrag</h3>
            <form hx-post="<?= url('/countries/store') ?>" hx-target="#countries-table-body" hx-swap="afterbegin" class="space-y-6">
                <div class="space-y-2">
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-500">Name</label>
                    <input type="text" name="name" required class="form-input bg-white" placeholder="z.B. Deutschland">
                </div>
                <div class="space-y-2">
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-500">Offizieller Name</label>
                    <input type="text" name="full_name" class="form-input bg-white" placeholder="z.B. Bundesrepublik Deutschland">
                </div>
                <div class="space-y-2">
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-500">Hauptstadt</label>
                    <input type="text" name="capital" class="form-input bg-white" placeholder="z.B. Berlin">
                </div>
                <div class="pt-4">
                    <button type="submit" class="btn btn-primary w-full justify-center py-3 text-[10px]">STAAT REGISTRIEREN</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- Countries List -->
    <div class="<?= (isPrimary() && currentUser()) ? 'lg:col-span-3' : 'lg:col-span-4' ?>">
        <div class="border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="py-4 pl-4 pr-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-widest sm:pl-6">Name des Staates</th>
                        <th scope="col" class="px-3 py-4 text-left text-[10px] font-bold text-gray-500 uppercase tracking-widest">Hauptstadt</th>
                        <th scope="col" class="relative py-4 pl-3 pr-4 sm:pr-6 text-right">
                            <span class="sr-only">Aktionen</span>
                        </th>
                    </tr>
                </thead>
                <tbody id="countries-table-body" class="divide-y divide-gray-100 bg-white">
                    <?php foreach ($countries as $country): ?>
                        <?php include __DIR__ . '/_row.php'; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
