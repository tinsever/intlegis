<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Neues Gesetz erfassen</h1>
    <p class="text-sm text-gray-500">Erstellen Sie ein neues lokales Gesetz oder eine Verordnung.</p>
</div>

<form action="<?= url('/laws/store') ?>" method="POST">
    <?php include '_form.php'; ?>
</form>

