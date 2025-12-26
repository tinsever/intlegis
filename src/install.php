<?php
declare(strict_types=1);

// Helper function to render installer views
function renderInstall(string $view, array $vars = []): void {
    extract($vars, EXTR_SKIP);
    
    // Define a local url helper for the installer views
    if (!function_exists('url')) {
        function url(string $path): string {
            global $basePath;
            return ($basePath ?? '') . '/' . ltrim($path, '/');
        }
    }

    ob_start();
    include __DIR__ . '/../views/install/' . $view . '.php';
    $content = ob_get_clean();
    
    // Simple layout for installer
    include __DIR__ . '/../views/install/layout.php';
    exit;
}

// Prevent re-running installer if config exists
if (file_exists(__DIR__ . '/../config.php')) {
    header('Location: ' . ($basePath ?? '') . '/');
    exit;
}

if ($path === '/install') {
    renderInstall('step1', ['title' => 'Welcome']);
}

if ($path === '/install/db') {
    if ($method === 'POST') {
        $driver = $_POST['driver'] ?? 'sqlite';
        $dbConfig = [
            'driver' => $driver,
            'sqlite_path' => "__DIR__ . '/data.sqlite'",
            'host' => $_POST['host'] ?? '127.0.0.1',
            'dbname' => $_POST['dbname'] ?? 'intlegis',
            'user' => $_POST['user'] ?? 'root',
            'pass' => $_POST['pass'] ?? '',
        ];
        
        $_SESSION['temp_db_config'] = $dbConfig;
        renderInstall('step2', ['title' => 'Instance Configuration']);
    } elseif (isset($_SESSION['temp_db_config'])) {
        renderInstall('step2', ['title' => 'Instance Configuration']);
    }
}

if ($path === '/install/instance') {
    if ($method === 'POST') {
        $mode = $_POST['mode'] ?? 'PRIMARY';
        $primaryUrl = $_POST['primary_url'] ?? '';
        
        $_SESSION['temp_instance_config'] = [
            'mode' => $mode,
            'primary_url' => $primaryUrl
        ];

        $_SESSION['temp_app_config'] = [
            'name' => $_POST['app_name'] ?? 'IntLegis',
            'primary_color' => $_POST['primary_color'] ?? '#1e3a8a',
            'secondary_color' => $_POST['secondary_color'] ?? '#4f46e5',
        ];
        
        if ($mode === 'SECONDARY') {
            // Skip admin creation for secondary, just finish
            completeInstallation();
        } else {
            renderInstall('step3', ['title' => 'Create Administrator']);
        }
    } elseif (isset($_SESSION['temp_instance_config'])) {
        if (($_SESSION['temp_instance_config']['mode'] ?? '') === 'PRIMARY') {
            renderInstall('step3', ['title' => 'Create Administrator']);
        } else {
            completeInstallation();
        }
    }
}

if ($path === '/install/admin' && $method === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (strlen($username) < 3 || strlen($password) < 6) {
        renderInstall('step3', ['title' => 'Create Administrator', 'error' => 'Invalid username or password.']);
    }
    
    $_SESSION['temp_admin'] = [
        'username' => $username,
        'password' => $password
    ];
    
    completeInstallation();
}

// If no route matched, redirect to step 1
header('Location: ' . ($basePath ?? '') . '/install');
exit;

function completeInstallation(): void {
    $dbConfig = $_SESSION['temp_db_config'];
    $instanceConfig = $_SESSION['temp_instance_config'];
    $appConfig = $_SESSION['temp_app_config'] ?? ['name' => 'IntLegis', 'primary_color' => '#1e3a8a', 'secondary_color' => '#4f46e5'];
    $admin = $_SESSION['temp_admin'] ?? null;
    
    // 1. Generate config.php
    $configContent = "<?php\n/**\n * Configuration\n */\n\nreturn [\n";
    $configContent .= "    'app' => [\n";
    $configContent .= "        'name' => '" . addslashes($appConfig['name']) . "',\n";
    $configContent .= "        'primary_color' => '{$appConfig['primary_color']}',\n";
    $configContent .= "        'secondary_color' => '{$appConfig['secondary_color']}',\n";
    $configContent .= "    ],\n\n";
    $configContent .= "    'db' => [\n";
    $configContent .= "        'driver' => '{$dbConfig['driver']}',\n";
    $configContent .= "        'sqlite_path' => " . $dbConfig['sqlite_path'] . ",\n";
    $configContent .= "        'host' => '{$dbConfig['host']}',\n";
    $configContent .= "        'dbname' => '{$dbConfig['dbname']}',\n";
    $configContent .= "        'user' => '{$dbConfig['user']}',\n";
    $configContent .= "        'pass' => '{$dbConfig['pass']}',\n";
    $configContent .= "    ],\n\n";
    $configContent .= "    'instance' => [\n";
    $configContent .= "        'mode' => '{$instanceConfig['mode']}',\n";
    $configContent .= "        'primary_url' => '{$instanceConfig['primary_url']}',\n";
    $configContent .= "    ],\n\n";
    $configContent .= "    'auth' => [\n";
    $configContent .= "        'provider' => 'database',\n";
    $configContent .= "    ],\n";
    $configContent .= "];\n";
    
    file_put_contents(__DIR__ . '/../config.php', $configContent);
    
    // 2. Initialize Database
    require_once __DIR__ . '/app.php';
    $pdo = db();
    
    // 3. Create Admin User if PRIMARY
    if ($admin) {
        $stmt = $pdo->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?)');
        $stmt->execute([$admin['username'], password_hash($admin['password'], PASSWORD_DEFAULT)]);
    }
    
    // 4. Clear session and redirect
    session_destroy();
    header('Location: ' . ($basePath ?? '') . '/login?installed=1');
    exit;
}

