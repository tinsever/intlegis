# Authentication

IntLegis uses a pluggable authentication system, allowing you to easily integrate with existing user databases or use the built-in provider.

## Built-in Provider: `database`

By default, IntLegis uses the `database` provider, which stores users in the local `users` table.

*   **Config**: `'auth' => ['provider' => 'database']`
*   **Storage**: `users` table.
*   **Functionality**: Standard session-based login/logout.

## Creating a Custom Provider

You can create a custom authentication provider to connect IntLegis with another system (e.g., WordPress, LDAP, or a custom forum).

### 1. Create the Provider File
Create a new PHP file in `src/auth/` (e.g., `src/auth/my_system.php`).

### 2. Implement the Interface
The file must **return an array** with three specific closures:

```php
<?php
// src/auth/my_system.php

return [
    /**
     * Check if a user is currently logged in.
     * Should return an array with 'id' and 'username' if logged in, otherwise null.
     */
    'currentUser' => function(): ?array {
        // Example: check a specific session key
        return $_SESSION['my_custom_user'] ?? null;
    },

    /**
     * Start a session for the given user.
     * @param array $user Data retrieved from the login process.
     */
    'login' => function(array $user): void {
        $_SESSION['my_custom_user'] = [
            'id' => $user['id'],
            'username' => $user['username']
        ];
    },

    /**
     * End the user session.
     */
    'logout' => function(): void {
        unset($_SESSION['my_custom_user']);
    }
];
```

### 3. Update Configuration
In your `config.php`, change the `provider` name to match your file:

```php
'auth' => [
    'provider' => 'my_system', // refers to src/auth/my_system.php
],
```

## Security Considerations

*   **Password Hashing**: If implementing your own login logic within the provider, always use `password_hash()` and `password_verify()`.
*   **Session Security**: IntLegis calls `session_start()` in `app.php`. Ensure your provider doesn't start a second session.
*   **Authorization**: Currently, IntLegis assumes any logged-in user on a Primary instance has full administrative rights.

