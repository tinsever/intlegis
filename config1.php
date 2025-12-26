<?php
/**
 * IntLegis Configuration
 */

return [
    // Application Branding
    'app' => [
        'name' => 'KDN Archiv',
        'image' => 'https://kdn.mn-netz.de/kdn-blau.png',
        'description' => 'Zentralarchiv für internationale Verträge und amtliche Bekanntmachungen',
        'accent_color' => '#009EDB',
    ],

    // Database Configuration
    'db' => [
        'driver' => 'sqlite', // 'sqlite' or 'mysql'
        'sqlite_path' => __DIR__ . '/data.sqlite',
        // MySQL settings (only used if driver is 'mysql')
        'host' => '127.0.0.1',
        'dbname' => 'intlegis',
        'user' => 'root',
        'pass' => '',
    ],

    // Instance Configuration
    'instance' => [
        'mode' => 'PRIMARY', // 'PRIMARY' or 'SECONDARY'
        'primary_url' => 'https://main-instance.example.com',
    ],

    // Auth Configuration
    'auth' => [
        'provider' => 'database', // name of the file in src/auth/
    ],

    // Module Configuration
    'modules' => [
        'treaties' => [
            'enabled' => true,
            'label' => 'Verträge',
        ],
        'laws' => [
            'enabled' => true,
            'label' => 'Anzeiger',
        ],
        'countries' => [
            'label' => 'Staaten',
        ],
    ],

    // Anzeiger (National Law) Configuration
    'laws' => [
        'structure' => [
            'Teil A' => [
                'label' => 'Hauptteil A: Gesetze & Verordnungen',
                'subcategories' => ['Verfassung', 'Zivilrecht', 'Strafrecht', 'Verwaltungsrecht'],
            ],
            'Teil B' => [
                'label' => 'Hauptteil B: Amtliche Bekanntmachungen',
                'subcategories' => ['Personalien', 'Ausschreibungen', 'Sonstiges'],
            ],
            'Teil C' => [
                'label' => 'Hauptteil C: Lokale Satzungen',
                'subcategories' => [], // Sections without subcategories are also possible
            ],
        ],
    ],
];

