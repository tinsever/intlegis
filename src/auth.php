<?php
declare(strict_types=1);

require_once __DIR__ . '/app.php';

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$isDatabaseProvider = config('auth.provider', 'database') === 'database';

if ($path === '/register') {
    if (!$isDatabaseProvider) {
        http_response_code(403);
        die('Die Registrierung wird von einem externen Anbieter verwaltet.');
    }

    if ($method === 'GET') {
        render('auth/register', ['title' => 'Registrieren']);
    }

    if ($method === 'POST') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (strlen($username) < 3) {
            flash('Der Benutzername muss mindestens 3 Zeichen lang sein.');
            render('auth/register', ['title' => 'Registrieren']);
        }

        if (strlen($password) < 6) {
            flash('Das Passwort muss mindestens 6 Zeichen lang sein.');
            render('auth/register', ['title' => 'Registrieren']);
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = db()->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?)');
            $stmt->execute([$username, $passwordHash]);
            
            $userId = (int)db()->lastInsertId();
            login(['id' => $userId, 'username' => $username]);
            
            flash('Registrierung erfolgreich! Willkommen.');
            redirect('/treaties');
        } catch (PDOException $e) {
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), '1062')) { // Unique constraint violation (SQLite or MySQL)
                flash('Benutzername bereits vergeben.');
            } else {
                flash('Bei der Registrierung ist ein Fehler aufgetreten.');
            }
            render('auth/register', ['title' => 'Registrieren']);
        }
    }
}

if ($path === '/login') {
    if (!$isDatabaseProvider) {
        http_response_code(403);
        die('Der Login wird von einem externen Anbieter verwaltet.');
    }

    if ($method === 'GET') {
        if (currentUser()) redirect('/treaties');
        render('auth/login', ['title' => 'Anmelden']);
    }

    if ($method === 'POST') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = db()->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            login($user);
            flash('Willkommen zurück, ' . $username . '!');
            redirect('/treaties');
        } else {
            flash('Ungültiger Benutzername oder Passwort.');
            render('auth/login', ['title' => 'Anmelden']);
        }
    }
}

if ($path === '/logout' && $method === 'POST') {
    logout();
    flash('Sie wurden abgemeldet.');
    redirect('/login');
}
