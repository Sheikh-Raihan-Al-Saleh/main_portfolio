<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Seeded Admin Account
    |--------------------------------------------------------------------------
    |
    | The single owner account created by DatabaseSeeder. Registration is
    | disabled in Fortify, so this is how the admin account comes into being.
    |
    */

    'admin' => [
        'name' => env('ADMIN_NAME', 'Admin'),
        'email' => env('ADMIN_EMAIL', 'admin@example.com'),
        'password' => env('ADMIN_PASSWORD', 'password'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Landing Page Demo Embeds
    |--------------------------------------------------------------------------
    |
    | Hosts allowed in a landing page's "live embedded demo" section. The URL is
    | admin-entered, but an allowlist keeps a compromised or mistyped value from
    | framing an arbitrary origin. Leave empty to permit any https host.
    | Subdomains of a listed host are accepted.
    |
    */

    'embed_hosts' => array_values(array_filter(
        explode(',', (string) env('LANDING_EMBED_HOSTS', '')),
    )),

];
