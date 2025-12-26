<?php
declare(strict_types=1);

session_start();

/**
 * Get the base path for URLs (handles subfolders)
 */
function basePath(): string {
    static $basePath;
    if ($basePath !== null) return $basePath;

    $scriptName = $_SERVER['SCRIPT_NAME']; // z.B. /intlegis/index.php
    $basePath = str_replace('/public/index.php', '', $scriptName);
    $basePath = str_replace('/index.php', '', $basePath);
    return $basePath;
}

/**
 * Generate a full URL including the subfolder
 */
function url(string $path): string {
    return basePath() . '/' . ltrim($path, '/');
}

/**
 * Get configuration value
 */
function config(string $key, $default = null) {
    static $config;
    if (!$config) {
        $config = require __DIR__ . '/../config.php';
    }
    
    $parts = explode('.', $key);
    $value = $config;
    foreach ($parts as $part) {
        if (!isset($value[$part])) return $default;
        $value = $value[$part];
    }
    return $value;
}

/**
 * Database connection helper
 */
function db(): PDO {
    static $pdo;
    if ($pdo) return $pdo;

    $driver = config('db.driver', 'sqlite');
    
    if ($driver === 'sqlite') {
        $dsn = 'sqlite:' . config('db.sqlite_path');
        $user = null;
        $pass = null;
    } else {
        $dsn = sprintf("mysql:host=%s;dbname=%s;charset=utf8mb4", config('db.host'), config('db.dbname'));
        $user = config('db.user');
        $pass = config('db.pass');
    }

    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // Create tables if they don't exist (using common SQL)
    $pk = ($driver === 'sqlite') ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT AUTO_INCREMENT PRIMARY KEY';
    $textType = ($driver === 'sqlite') ? 'TEXT' : 'LONGTEXT';

    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id $pk,
        username VARCHAR(255) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS countries (
        id $pk,
        name VARCHAR(255) NOT NULL UNIQUE,
        full_name VARCHAR(255),
        capital VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS treaties (
        id $pk,
        stable_id VARCHAR(255) NOT NULL UNIQUE,
        type VARCHAR(50) NOT NULL,
        status VARCHAR(50) NOT NULL,
        signature_date DATE NULL,
        in_force_date DATE NULL,
        duration VARCHAR(255) NULL,
        content_html $textType,
        content_json $textType,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS treaty_parties (
        treaty_id INT NOT NULL,
        country_id INT NOT NULL,
        is_organization BOOLEAN DEFAULT 0,
        organization_name VARCHAR(255),
        PRIMARY KEY (treaty_id, country_id)
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS treaty_tags (
        treaty_id INT NOT NULL,
        tag VARCHAR(100) NOT NULL,
        PRIMARY KEY (treaty_id, tag)
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS audit_logs (
        id $pk,
        user_id INT NULL,
        entity_type VARCHAR(50) NOT NULL,
        entity_id INT NOT NULL,
        action VARCHAR(50) NOT NULL,
        details $textType,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS versions (
        id $pk,
        entity_type VARCHAR(50) NOT NULL,
        entity_id INT NOT NULL,
        user_id INT NULL,
        data_json $textType NOT NULL,
        version_number INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS local_laws (
        id $pk,
        title VARCHAR(255) NOT NULL,
        law_number VARCHAR(100),
        category VARCHAR(100),
        publication_date DATE,
        effective_date DATE,
        status VARCHAR(50) DEFAULT 'active',
        content_html $textType,
        content_json $textType,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS related_links (
        id $pk,
        entity_type VARCHAR(50) NOT NULL,
        entity_id INT NOT NULL,
        url VARCHAR(2048) NOT NULL,
        label VARCHAR(255) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS entity_amendments (
        id $pk,
        amending_entity_type VARCHAR(50) NOT NULL,
        amending_entity_id INT NOT NULL,
        amended_entity_type VARCHAR(50) NOT NULL,
        amended_entity_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(amending_entity_type, amending_entity_id, amended_entity_type, amended_entity_id)
    )");

    // Table for sync state
    $pdo->exec("CREATE TABLE IF NOT EXISTS sync_state (
        id $pk,
        last_log_id INT NOT NULL DEFAULT 0,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    return $pdo;
}

/**
 * Check if the request is an HTMX request
 */
function isHtmx(): bool {
    return isset($_SERVER['HTTP_HX_REQUEST']);
}

/**
 * Render a view file
 */
function render(string $view, array $vars = []): void {
    extract($vars, EXTR_SKIP);
    
    // Capture the view content
    ob_start();
    include __DIR__ . '/../views/' . $view . '.php';
    $content = ob_get_clean();

    if (isHtmx()) {
        echo $content;
    } else {
        include __DIR__ . '/../views/layout.php';
    }
    exit;
}

/**
 * Flash message helper
 */
function flash(?string $message = null): ?string {
    if ($message) {
        $_SESSION['flash'] = $message;
        return null;
    }
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

/**
 * Instance Helpers
 */
function isPrimary(): bool {
    return config('instance.mode', 'PRIMARY') === 'PRIMARY';
}

function requirePrimary(): void {
    if (!isPrimary()) {
        http_response_code(403);
        die('Diese Aktion ist nur auf der primären Instanz verfügbar.');
    }
}

/**
 * Auth Helpers (Pluggable)
 */
function authProvider() {
    static $provider;
    if (!$provider) {
        $name = config('auth.provider', 'database');
        $provider = require __DIR__ . "/auth/{$name}.php";
    }
    return $provider;
}

function currentUser(): ?array {
    return authProvider()['currentUser']();
}

function login(array $user): void {
    authProvider()['login']($user);
}

function logout(): void {
    authProvider()['logout']();
}

function requireAuth(): void {
    if (!currentUser()) {
        flash('Bitte melden Sie sich an, um auf diese Seite zuzugreifen.');
        header('Location: ' . url('/login'));
        exit;
    }
}

function redirect(string $path): void {
    header('Location: ' . url($path));
    exit;
}

/**
 * Amendment Helpers
 */
function getAmendedEntities(string $type, int $id): array {
    $stmt = db()->prepare("
        SELECT ea.*, 
               CASE 
                   WHEN ea.amended_entity_type = 'local_law' THEN ll.title 
                   WHEN ea.amended_entity_type = 'treaty' THEN t.stable_id 
               END as title
        FROM entity_amendments ea
        LEFT JOIN local_laws ll ON ea.amended_entity_type = 'local_law' AND ea.amended_entity_id = ll.id
        LEFT JOIN treaties t ON ea.amended_entity_type = 'treaty' AND ea.amended_entity_id = t.id
        WHERE ea.amending_entity_type = ? AND ea.amending_entity_id = ?
    ");
    $stmt->execute([$type, $id]);
    return $stmt->fetchAll();
}

function getAmendingEntities(string $type, int $id): array {
    $stmt = db()->prepare("
        SELECT ea.*, 
               CASE 
                   WHEN ea.amending_entity_type = 'local_law' THEN ll.title 
                   WHEN ea.amending_entity_type = 'treaty' THEN t.stable_id 
               END as title
        FROM entity_amendments ea
        LEFT JOIN local_laws ll ON ea.amending_entity_type = 'local_law' AND ea.amending_entity_id = ll.id
        LEFT JOIN treaties t ON ea.amending_entity_type = 'treaty' AND ea.amending_entity_id = t.id
        WHERE ea.amended_entity_type = ? AND ea.amended_entity_id = ?
    ");
    $stmt->execute([$type, $id]);
    return $stmt->fetchAll();
}
