<?php
/**
 * Default Database Auth Provider
 */

return [
    'currentUser' => function(): ?array {
        return $_SESSION['user'] ?? null;
    },

    'login' => function(array $user): void {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username']
        ];
    },

    'logout' => function(): void {
        unset($_SESSION['user']);
        session_destroy();
    }
];

