<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Gesetz bearbeiten</h1>
    <p class="text-sm text-gray-500">Aktualisieren Sie die Details des Gesetzes.</p>
</div>

<form action="<?= url('/laws/' . $law['id'] . '/update') ?>" method="POST">
    <?php include '_form.php'; ?>
</form>

