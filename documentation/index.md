# IntLegis Documentation

Welcome to the documentation for **IntLegis**, a distributed document management system designed for international treaties, laws, and official announcements.

## Table of Contents

1.  **[Architecture](architecture.md)**
    *   Folder Structure
    *   Database Schema
    *   Core Concepts
2.  **[Installation](installation.md)**
    *   Requirements
    *   Setup Wizard
    *   Manual Configuration
3.  **[Replication & Synchronization](replication.md)**
    *   Primary vs. Secondary Instances
    *   Replication API
    *   Automation with Cron
4.  **[Authentication](authentication.md)**
    *   Built-in Database Auth
    *   Creating Custom Providers
5.  **[laws and treaties](laws_and_treaties.md)** (Coming Soon)
    *   Managing Treaties
    *   The "Anzeiger" (Local Laws)
    *   Country Database

## Key Features

*   **Distributed Architecture**: Run multiple read-only mirrors (Secondary) that sync with a main (Primary) instance. Note: This only applies to International Law data.
*   **Audit Trails**: Every change is tracked in an audit log and versioned.
*   **Flexible Auth**: Easily swap out the authentication system to integrate with existing user databases.
*   **Modern UI**: Fast, responsive interface powered by HTMX.
*   **Dual Database Support**: Supports both SQLite for ease of use and MySQL for production performance.

