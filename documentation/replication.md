# Replication & Synchronization

> **Note**: Replication only supports **International Law** (Treaties and Countries). Local Laws ("Anzeiger") are not synchronized between instances and remain local to your instance.

IntLegis features a powerful, one-way replication system that allows you to run multiple read-only "Secondary" instances that stay in sync with a single "Primary" instance.

## Instance Modes

### Primary
*   The source of truth.
*   Allows all CRUD (Create, Read, Update, Delete) operations.
*   Exposes a synchronization API at `/api/sync`.

### Secondary
*   A read-only mirror.
*   Does not allow editing of documents.
*   Periodically pulls updates from the Primary via `/api/pull-sync`.

## How Replication Works

1.  **Audit Logging**: Every change on the Primary instance is recorded in the `audit_logs` table.
2.  **State Tracking**: Each Secondary instance tracks the `id` of the last audit log it successfully processed in the `sync_state` table.
3.  **The Sync Loop**:
    *   The Secondary calls the Primary's `/api/sync?last_id=X` endpoint.
    *   The Primary returns all audit logs since `last_id`, along with the current state of any modified entities (treaties, countries, versions, etc.).
    *   The Secondary applies these changes to its local database within a transaction.

## Setting Up Synchronization

### 1. Configure the Secondary
In the Secondary's `config.php`, set the mode and the Primary's URL:

```php
'instance' => [
    'mode' => 'SECONDARY',
    'primary_url' => 'https://primary-instance.example.com',
],
```

### 2. Manual Sync
You can trigger a manual sync by visiting `/api/pull-sync` in your browser or via `curl`.

### 3. Automatic Sync (Recommended)
To keep the Secondary up to date automatically, set up a cron job to call the pull-sync API every minute:

```bash
* * * * * curl -s https://secondary-instance.example.com/api/pull-sync > /dev/null
```

## Troubleshooting

*   **HTTP Errors**: Ensure the Secondary can reach the Primary URL via `curl`.
*   **Database Locks**: If using SQLite, ensure the web server has write permissions to the database file and the directory containing it.
*   **ID Mismatches**: Replication relies on consistent IDs. Do not manually edit the Secondary's database, as this can lead to synchronization conflicts.

