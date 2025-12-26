# Laws and Treaties

IntLegis is organized around three main modules: Treaties, Laws (Anzeiger), and Countries.

## Treaties (Verträge)

The Treaties module is designed for international agreements.

*   **Stable ID**: Each treaty has a permanent, unique identifier.
*   **Parties**: Multiple countries or organizations can be linked to a treaty.
*   **Metadata**: Tracks signature dates, in-force dates, duration, and status (active, superseded, etc.).
*   **Tags**: Treaties can be tagged for easier categorization and searching.

## Laws (Anzeiger)

The Laws module (often referred to as the "Anzeiger") is used for national laws, ordinances, and official announcements.

### Configuration
The structure of the Anzeiger is highly configurable in `config.php`. You can define Parts (Teile) and Subcategories:

> **Important**: Unlike Treaties, Local Laws are **not replicated** to secondary instances. They exist only on the instance where they were created.

```php
'laws' => [
    'structure' => [
        'Teil A' => [
            'label' => 'Hauptteil A: Gesetze & Verordnungen',
            'subcategories' => ['Verfassung', 'Zivilrecht', 'Strafrecht'],
        ],
        // ...
    ],
],
```

*   **Law Number**: Each entry can have an official publication number.
*   **Dates**: Tracks publication date and effective date.
*   **Amendments**: You can link laws to show which legal acts amend others.

## Countries (Staaten)

A central directory of sovereign states and international organizations.

*   **Full Name & Capital**: Basic metadata for each country.
*   **Treaty History**: Each country profile displays all treaties where they are a party.

## Common Features

### Versioning
Every time a Treaty or Law is updated, a JSON snapshot of the previous state is saved in the `versions` table. This allows users to view the historical state of a document.

### Amendments
Both Laws and Treaties support an amendment system. You can specify that "Document A" amends "Document B". These relationships are visible on the "Show" page of both documents, providing a clear audit trail of legal changes.

