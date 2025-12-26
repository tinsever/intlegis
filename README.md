# IntLegis: International Treaty & Law Database

IntLegis is a distributed document management system designed for tracking international treaties, sovereign states, and national laws ("Anzeiger"). It features a built-in audit trail, versioning system, and a one-way replication mechanism for international law data.

## 📚 Documentation

Detailed documentation is available in the `documentation/` folder:

*   **[Installation Guide](documentation/installation.md)** - How to set up IntLegis on your server.
*   **[Architecture Overview](documentation/architecture.md)** - Understanding the project structure and database.
*   **[Replication & Sync](documentation/replication.md)** - How Secondary instances stay in sync.
*   **[Authentication System](documentation/authentication.md)** - Pluggable auth and custom providers.
*   **[Laws & Treaties](documentation/laws_and_treaties.md)** - Managing documents and relationships.

## 🚀 Key Features

*   **Distributed Sync**: Run mirror instances that automatically pull updates for international law (Treaties & Countries).
*   **Audit Trail**: Every change is logged with details on what was changed and by whom.
*   **Document Versioning**: Full history for every treaty and law, allowing you to see past states of a document.
*   **Pluggable Auth**: Easily swap the default database auth with your own (e.g., LDAP, Forum sync).
*   **HTMX Powered**: A fast, responsive user interface without the bloat of a heavy JS framework.
*   **Dual DB Support**: Works out-of-the-box with SQLite, or can be configured for MySQL/MariaDB.

## 🛠 Tech Stack

*   **Backend**: PHP 8.1+ (Native, no heavy frameworks)
*   **Frontend**: HTMX, Alpine.js, Tailwind CSS
*   **Database**: SQLite or MySQL
