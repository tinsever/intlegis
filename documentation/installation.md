# Installation Guide

IntLegis is designed to be easy to install, even on shared hosting environments.

## Requirements

*   PHP 8.1 or higher.
*   Extensions: `pdo_sqlite` or `pdo_mysql`, `mbstring`, `json`, `curl` (for replication).
*   Writable root directory (for `config.php` and `data.sqlite` creation).
*   **Domain Root**: IntLegis must be installed in the root directory of a domain or subdomain (e.g., `https://treaties.example.com/`). Installing in a subdirectory (e.g., `https://example.com/treaties/`) is not supported.

## Quick Setup (Wizard)

1.  **Upload**: Transfer all files to your web server.
2.  **Navigate**: Open your site's URL in a browser. You will be redirected to the `/install` wizard.
3.  **Step 1: Welcome**: The system checks for folder permissions.
4.  **Step 2: Database**:
    *   **SQLite**: Recommended for most setups. The database will be created as `data.sqlite` in the root.
    *   **MySQL**: Provide your database host, name, username, and password.
5.  **Step 3: Instance Mode for international law**:
    *   **PRIMARY**: The main instance where you can create and edit documents.
    *   **SECONDARY**: A read-only mirror. You must provide the URL of the Primary instance.
6.  **Step 4: Admin Account**: Create the first administrator account.

## Manual Configuration

If you prefer to configure the system manually, you can create a `config.php` file in the root directory. Use the following template:

```php
<?php
return [
    'app' => [
        'name' => 'My IntLegis Instance',
        'accent_color' => '#009EDB',
    ],
    'db' => [
        'driver' => 'sqlite', // or 'mysql'
        'sqlite_path' => __DIR__ . '/data.sqlite',
    ],
    'instance' => [
        'mode' => 'PRIMARY',
    ],
    'auth' => [
        'provider' => 'database',
    ],
];
```

## Security Recommendations

1.  **Restrict Root Access**: In production, point your web server's document root to the `public/` folder.
2.  **Permissions**: Once installation is complete, you can make `config.php` read-only for the web server user. Ensure the `storage/` folder remains writable.
3.  **HTTPS**: Always use HTTPS, especially if you are using the Replication API.

