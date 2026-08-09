<?php
declare(strict_types=1);

/**
 * Copy this file to `config.php` on your cPanel host and fill in your values.
 * config.php is git-ignored so your credentials never get committed.
 */
return [
    // --- MySQL / MariaDB (from cPanel > MySQL Databases) ---
    'db' => [
        'host'    => 'localhost',
        'name'    => 'cpaneluser_illgetthis',   // the database you created in cPanel
        'user'    => 'cpaneluser_iggt',          // the MySQL user you assigned to it
        'pass'    => 'CHANGE_ME',
        'charset' => 'utf8mb4',
    ],

    // Random long secret used to sign/seed auth tokens. Generate once, e.g.:
    //   php -r "echo bin2hex(random_bytes(32));"
    'app_secret' => 'CHANGE_ME_TO_A_LONG_RANDOM_STRING',

    // How long a login token stays valid.
    'token_ttl_days' => 90,

    // The URL path the API is served from, with NO trailing slash. The router
    // strips this from the request URI. Examples:
    //   API at https://example.com/api            -> '/api'
    //   API at https://example.com/iggt/backend/api -> '/iggt/backend/api'
    'base_path' => '/api',

    // Allowed CORS origin. Use '*' while developing; lock to your domain later.
    'cors_origin' => '*',
];
