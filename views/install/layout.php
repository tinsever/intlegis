<!doctype html>
<html lang="de" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars(config('app.name', 'IntLegis')) ?> Setup - <?= htmlspecialchars($title ?? 'Installation') ?></title>
    <script src="<?= url('/assets/vendor/js/tailwind.min.js') ?>"></script>
    <style type="text/tailwindcss">
        @layer components {
            .btn {
                @apply inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors;
            }
            .btn-primary {
                @apply bg-indigo-600 text-white hover:bg-indigo-700 focus:ring-indigo-500;
            }
            .form-input {
                @apply mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm;
            }
        }
    </style>
</head>
<body class="h-full flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-xl shadow-lg border border-gray-100">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 text-center tracking-tight"><?= htmlspecialchars(config('app.name', 'IntLegis')) ?> Setup</h1>
            <p class="mt-2 text-center text-sm text-gray-600">Schrittweise Konfiguration</p>
        </div>
        
        <?= $content ?>
    </div>
</body>
</html>
