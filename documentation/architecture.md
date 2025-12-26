# Architecture

IntLegis is built with a lightweight, custom PHP framework designed for speed and portability. It avoids heavy dependencies, making it easy to deploy in various environments.

## Folder Structure

*   `assets/`: Static files (CSS, JS).
*   `documentation/`: Project documentation.
*   `public/`: The web server document root. Contains the main `index.php`.
*   `src/`: Core logic and helper functions.
    *   `auth/`: Authentication providers.
    *   `app.php`: Global bootstrapping, database connection, and utility functions.
    *   `auth.php`, `laws.php`, `treaties.php`, etc.: Module-specific logic.
*   `storage/`: Temporary files, caches, and view-related storage.
*   `views/`: PHP-based view templates (used with the `render()` helper).
*   `config.php`: Central configuration file.

## Database Schema

IntLegis uses a shared schema compatible with both SQLite and MySQL. Key tables include:

### Core Tables
*   `users`: Stores user credentials (if using the `database` auth provider).
*   `countries`: A directory of countries and organizations.
*   `treaties`: International treaties, including stable IDs, status, and content.
*   `local_laws`: National laws and announcements ("Anzeiger").

### Metadata & Relationships
*   `treaty_parties`: Many-to-many relationship between treaties and countries.
*   `treaty_tags`: Tags associated with treaties for categorization.
*   `related_links`: External URLs linked to entities.
*   `entity_amendments`: Tracks relationships where one law or treaty amends another.

### System Tables
*   `audit_logs`: Detailed log of every action (create, update, delete).
*   `versions`: JSON snapshots of entities at different points in time.
*   `sync_state`: Tracks the synchronization progress for secondary instances.

## Core Principles

1.  **Stateless Components**: Logic is mostly functional, relying on the `db()` helper for persistence.
2.  **HTMX Integration**: Most UI interactions are handled via HTMX, allowing for partial page updates without full reloads.
3.  **Versioning**: Entities are versioned on every update, allowing for history tracking and potential rollbacks.

