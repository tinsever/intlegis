<!doctype html>
<html lang="en" class="h-full bg-[#fdfdfc]">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? config('app.name', 'IntLegis'), ENT_QUOTES) ?> | <?= htmlspecialchars(config('app.name', 'IntLegis'), ENT_QUOTES) ?></title>
    
    <!-- Tailwind CSS with Typography Plugin -->
    <script src="https://cdn.tailwindcss.com?plugins=typography,forms"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        serif: ['EB Garamond', 'Georgia', 'serif'],
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            primary: '<?= config('app.primary_color', '#1e3a8a') ?>',
                            secondary: '<?= config('app.secondary_color', '#4f46e5') ?>',
                            accent: '<?= config('app.accent_color', '#000000') ?>',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Google Fonts for more legal look -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400..800;1,400..800&display=swap" rel="stylesheet">
    
    <!-- HTMX & Alpine -->
    <script src="https://cdn.jsdelivr.net/npm/htmx.org@2.0.8/dist/htmx.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/focus@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <script>
        // Theme Management
        document.addEventListener('alpine:init', () => {
            Alpine.store('theme', {
                mode: localStorage.getItem('theme') || 'light',
                set(mode) {
                    this.mode = mode;
                    localStorage.setItem('theme', mode);
                    document.documentElement.setAttribute('data-theme', mode);
                }
            });
            // Initial set
            document.documentElement.setAttribute('data-theme', localStorage.getItem('theme') || 'light');
        });

        // Table of Contents Generator
        function generateToC() {
            const container = document.querySelector('.prose-law, .prose-treaty');
            const tocTarget = document.getElementById('toc-content');
            if (!container || !tocTarget) return;

            const headers = container.querySelectorAll('h1, h2, h3');
            if (headers.length === 0) {
                document.getElementById('toc-container')?.remove();
                return;
            }

            const list = document.createElement('ul');
            list.className = 'space-y-2';

            headers.forEach((header, index) => {
                const id = `header-${index}`;
                header.id = id;

                const li = document.createElement('li');
                if (header.tagName === 'H2') li.className = 'pl-4';
                if (header.tagName === 'H3') li.className = 'pl-8';
                
                const link = document.createElement('a');
                link.href = `#${id}`;
                link.textContent = header.textContent;
                link.className = 'text-[10px] font-bold uppercase tracking-widest text-gray-500 hover:text-brand-accent transition-colors';
                
                li.appendChild(link);
                list.appendChild(li);
            });

            tocTarget.appendChild(list);
        }

        window.addEventListener('DOMContentLoaded', generateToC);
    </script>
    
    <!-- Quill Editor -->
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>

    <style>
        :root {
            --bg-main: #fdfdfc;
            --text-main: #1a1a1a;
            --bg-card: #ffffff;
            --border-main: #e5e7eb;
            --text-muted: #6b7280;
        }
        [data-theme='sepia'] {
            --bg-main: #f4ecd8;
            --text-main: #433422;
            --bg-card: #fdf6e3;
            --border-main: #e1d5b5;
            --text-muted: #5e4b34;
        }
        [data-theme='dark'] {
            --bg-main: #1a1a1a;
            --text-main: #d1d5db;
            --bg-card: #242424;
            --border-main: #333333;
            --text-muted: #9ca3af;
        }

        body {
            background-color: var(--bg-main);
            color: var(--text-main);
            transition: background-color 0.3s, color 0.3s;
        }
        .bg-white { background-color: var(--bg-card) !important; }
        .bg-gray-50 { background-color: var(--bg-main) !important; }
        .text-gray-900 { color: var(--text-main) !important; }
        .text-gray-500, .text-gray-400 { color: var(--text-muted) !important; }
        .border-gray-200, .border-gray-100 { border-color: var(--border-main) !important; }

        .btn-secondary {
            background-color: var(--bg-card) !important;
            border-color: var(--border-main) !important;
            color: var(--text-main) !important;
        }
        .form-input {
            background-color: var(--bg-card) !important;
            border-color: var(--border-main) !important;
            color: var(--text-main) !important;
        }
        .badge {
            background-color: var(--bg-main) !important;
            border-color: var(--border-main) !important;
            color: var(--text-main) !important;
        }
        
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; }
            nav, footer, .btn, aside, form { display: none !important; }
            .print-header { display: block !important; margin-bottom: 2rem; border-bottom: 2px solid black; padding-bottom: 1rem; }
        }
        .print-header { display: none; }
    </style>

    <style type="text/tailwindcss">
        body {
            @apply antialiased;
        }
        .prose-treaty, .prose-law {
            @apply prose max-w-none prose-slate;
            @apply prose-headings:font-serif prose-headings:tracking-tight prose-headings:font-bold;
            @apply prose-p:font-serif prose-p:text-lg prose-p:leading-relaxed;
            @apply prose-table:border prose-table:border-collapse prose-td:border prose-td:border-gray-300 prose-td:p-3 prose-th:border prose-th:border-gray-300 prose-th:p-3 prose-th:bg-gray-50;
            @apply prose-strong:font-bold;
            @apply prose-hr:border-gray-300;
        }
        [data-theme='dark'] .prose-treaty, [data-theme='dark'] .prose-law {
            @apply prose-invert;
        }
        .btn {
            @apply inline-flex items-center px-5 py-2.5 border text-xs font-bold uppercase tracking-widest transition-all disabled:opacity-50 disabled:cursor-not-allowed;
        }
        .btn-primary {
            @apply bg-brand-accent border-brand-accent text-white hover:opacity-90 focus:ring-2 focus:ring-offset-2 focus:ring-brand-accent shadow-sm;
        }
        .btn-secondary {
            @apply inline-flex items-center px-5 py-2.5 border text-xs font-bold uppercase tracking-widest hover:opacity-80 transition-all;
        }
        .btn-danger {
            @apply bg-white border-red-200 text-red-600 hover:bg-red-50 focus:ring-2 focus:ring-offset-2 focus:ring-red-500;
        }
        .form-input {
            @apply mt-1 block w-full rounded-none shadow-none sm:text-sm focus:border-brand-accent focus:ring-brand-accent;
        }
        .badge {
            @apply inline-flex items-center px-2 py-0.5 border text-[10px] font-bold uppercase tracking-widest opacity-80;
        }
    </style>
</head>

<body class="h-full flex flex-col" x-data="{ syncStatus: 'idle', syncMessage: '' }">
    <?php if (!isPrimary()): ?>
        <div class="bg-gray-900 text-white text-center py-1.5 text-[10px] font-bold uppercase tracking-[0.2em]">
            Replikationsmodus (Schreibgeschützt)
            <button @click="syncStatus = 'syncing'; fetch('<?= url('/api/pull-sync') ?>').then(r => r.json()).then(d => { syncStatus = d.status; syncMessage = d.message; if(d.status === 'success' && d.count > 0) window.location.reload(); })" class="ml-4 border-b border-white hover:text-gray-300 transition-colors" :disabled="syncStatus === 'syncing'">
                <span x-show="syncStatus !== 'syncing'">Synchronisieren</span>
                <span x-show="syncStatus === 'syncing'">Lädt...</span>
            </button>
            <template x-if="syncMessage">
                <span class="ml-2 opacity-60" x-text="'(' + syncMessage + ')'"></span>
            </template>
        </div>
    <?php endif; ?>

    <nav class="bg-white border-b-4 border-brand-accent">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20">
                <div class="flex">
                <div class="flex-shrink-0 flex items-center">
                        <a href="<?= url('/') ?>" class="flex items-center md:border-r-2 border-gray-200 md:pr-6 md:mr-6">
                            <?php if ($logo = config('app.image')): ?>
                                <img src="<?= htmlspecialchars($logo) ?>" alt="<?= htmlspecialchars(config('app.name', 'IntLegis')) ?>" class="h-12 w-auto object-contain">
                            <?php else: ?>
                                <span class="text-xl md:text-3xl font-serif font-bold text-gray-900 tracking-tighter uppercase"><?= htmlspecialchars(config('app.name', 'IntLegis')) ?></span>
                            <?php endif; ?>
                        </a>
                </div>
                    <div class="hidden md:flex md:space-x-8">
                    <?php if (config('modules.treaties.enabled', true)): ?>
                            <a href="<?= url('/treaties') ?>" class="inline-flex items-center px-1 pt-1 text-[10px] font-bold uppercase tracking-widest <?= strpos($_SERVER['REQUEST_URI'], basePath() . '/treaties') === 0 || $_SERVER['REQUEST_URI'] === basePath() . '/' || $_SERVER['REQUEST_URI'] === basePath() || $_SERVER['REQUEST_URI'] === basePath() . '/index.php' ? 'text-gray-900 border-b-2 border-brand-accent' : 'text-gray-500 hover:text-gray-900' ?>">
                            <?= htmlspecialchars(config('modules.treaties.label', 'Verträge')) ?>
                        </a>
                            <a href="<?= url('/countries') ?>" class="inline-flex items-center px-1 pt-1 text-[10px] font-bold uppercase tracking-widest <?= strpos($_SERVER['REQUEST_URI'], basePath() . '/countries') === 0 ? 'text-gray-900 border-b-2 border-brand-accent' : 'text-gray-500 hover:text-gray-900' ?>">
                                <?= htmlspecialchars(config('modules.countries.label', 'Staaten')) ?>
                        </a>
                    <?php endif; ?>
                    
                    <?php if (config('modules.laws.enabled', true)): ?>
                            <a href="<?= url('/laws') ?>" class="inline-flex items-center px-1 pt-1 text-[10px] font-bold uppercase tracking-widest <?= strpos($_SERVER['REQUEST_URI'], basePath() . '/laws') === 0 ? 'text-gray-900 border-b-2 border-brand-accent' : 'text-gray-500 hover:text-gray-900' ?>">
                            <?= htmlspecialchars(config('modules.laws.label', 'Anzeiger')) ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
                
                <div class="flex items-center space-x-4 md:space-x-6">
                    <?php if ($_SERVER['REQUEST_URI'] !== basePath() . '/' && $_SERVER['REQUEST_URI'] !== basePath()): ?>
                        <div class="hidden lg:block w-64">
                            <?php 
                                $uri = $_SERVER['REQUEST_URI'];
                                $isLawPath = strpos($uri, basePath() . '/laws') === 0;
                                $isTreatyPath = strpos($uri, basePath() . '/treaties') === 0;
                                
                                if ($isLawPath) {
                                    $searchAction = url('/laws');
                                } elseif ($isTreatyPath) {
                                    $searchAction = url('/treaties/search');
                                } else {
                                    $searchAction = url('/search');
                                }
                                $searchPlaceholder = 'Suche...';
                            ?>
                            <form action="<?= $searchAction ?>" method="GET">
                                <div class="relative">
                                    <input id="search" name="q" class="block w-full pl-3 pr-10 py-1.5 border-b border-gray-300 rounded-none bg-transparent placeholder-gray-400 focus:outline-none focus:border-gray-900 sm:text-xs italic" placeholder="<?= $searchPlaceholder ?>" type="search" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                    </div>
                                </div>
                            </form>
                    </div>
                    <?php endif; ?>

                    <div class="flex items-center">
                        <!-- Theme Toggle -->
                        <div class="flex items-center bg-gray-50 border border-gray-200 rounded-none p-1 mr-6 no-print" 
                             :class="{ 'bg-[#eee2c5] border-[#e1d5b5]': $store.theme.mode === 'sepia', 'bg-[#2d2d2d] border-[#333333]': $store.theme.mode === 'dark' }">
                            <button @click="$store.theme.set('light')" class="px-2 py-1 text-[8px] font-bold uppercase tracking-widest" :class="$store.theme.mode === 'light' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-400'">Light</button>
                            <button @click="$store.theme.set('sepia')" class="px-2 py-1 text-[8px] font-bold uppercase tracking-widest" :class="$store.theme.mode === 'sepia' ? 'bg-[#fdf6e3] shadow-sm text-[#433422]' : 'text-gray-400'">Sepia</button>
                            <button @click="$store.theme.set('dark')" class="px-2 py-1 text-[8px] font-bold uppercase tracking-widest" :class="$store.theme.mode === 'dark' ? 'bg-[#242424] shadow-sm text-gray-100' : 'text-gray-400'">Dark</button>
                        </div>

                        <?php if ($user = currentUser()): ?>
                            <div class="flex items-center">
                                <span class="hidden md:inline text-[10px] font-bold uppercase tracking-widest text-gray-400 mr-4"><?= htmlspecialchars($user['username']) ?></span>
                            <form action="<?= url('/logout') ?>" method="POST" class="inline">
                                    <button type="submit" class="text-[10px] font-bold uppercase tracking-widest text-gray-900 hover:underline">Abmelden</button>
                            </form>
                            </div>
                        <?php else: ?>
                            <div class="flex space-x-4 md:space-x-6">
                                <a href="<?= url('/login') ?>" class="text-[10px] font-bold uppercase tracking-widest text-gray-500 hover:text-gray-900">Login</a>
                                <a href="<?= url('/register') ?>" class="hidden md:inline text-[10px] font-bold uppercase tracking-widest text-gray-900 border-b-2 border-brand-accent">Registrieren</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <!-- Mobile Menu -->
            <div class="flex md:hidden border-t border-gray-100 py-3 space-x-6 overflow-x-auto no-scrollbar">
                <?php if (config('modules.treaties.enabled', true)): ?>
                    <a href="<?= url('/treaties') ?>" class="text-[10px] font-bold uppercase tracking-widest whitespace-nowrap <?= strpos($_SERVER['REQUEST_URI'], basePath() . '/treaties') === 0 ? 'text-gray-900 underline underline-offset-4' : 'text-gray-500' ?>"><?= htmlspecialchars(config('modules.treaties.label', 'Verträge')) ?></a>
                    <a href="<?= url('/countries') ?>" class="text-[10px] font-bold uppercase tracking-widest whitespace-nowrap <?= strpos($_SERVER['REQUEST_URI'], basePath() . '/countries') === 0 ? 'text-gray-900 underline underline-offset-4' : 'text-gray-500' ?>"><?= htmlspecialchars(config('modules.countries.label', 'Staaten')) ?></a>
                <?php endif; ?>
                <?php if (config('modules.laws.enabled', true)): ?>
                    <a href="<?= url('/laws') ?>" class="text-[10px] font-bold uppercase tracking-widest whitespace-nowrap <?= strpos($_SERVER['REQUEST_URI'], basePath() . '/laws') === 0 ? 'text-gray-900 underline underline-offset-4' : 'text-gray-500' ?>"><?= htmlspecialchars(config('modules.laws.label', 'Anzeiger')) ?></a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="print-header px-8">
        <div class="flex justify-between items-end">
            <div>
                <h1 class="text-2xl font-serif font-bold"><?= htmlspecialchars(config('app.name', 'IntLegis')) ?></h1>
                <p class="text-xs uppercase tracking-widest text-gray-500">Amtliches Archiv für Rechtsdaten</p>
            </div>
            <div class="text-right text-[10px] uppercase tracking-widest text-gray-400">
                Ausdruck vom <?= date('d.m.Y') ?>
            </div>
        </div>
    </div>

    <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 md:py-12 w-full">
        <?php if ($msg = flash()): ?>
            <div class="mb-8 border-l-4 border-gray-900 bg-gray-50 p-4">
                <p class="text-sm font-bold uppercase tracking-widest text-gray-900"><?= htmlspecialchars($msg) ?></p>
            </div>
        <?php endif; ?>

        <div class="bg-white border border-gray-200 p-4 md:p-12 min-h-[600px]">
            <?= $content ?? '' ?>
        </div>
    </main>

    <footer class="bg-white border-t border-gray-100 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center text-[10px] font-bold uppercase tracking-[0.3em] text-gray-400">
                <div>&copy; <?= date('Y') ?> <?= htmlspecialchars(config('app.name', 'IntLegis')) ?></div>
                <div class="flex space-x-8">
                    <span>Amtliches Archiv</span>
                    <span>Systemstatus: Bereit</span>
                </div>
            </div>
        </div>
    </footer>
</body>

</html>

