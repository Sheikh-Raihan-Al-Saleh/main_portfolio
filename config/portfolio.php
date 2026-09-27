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
    | Public Identity
    |--------------------------------------------------------------------------
    |
    | The person and inbox shown on the public site. Deliberately separate from
    | the admin account above: the account you log in with is not the name that
    | belongs on a public portfolio, and sharing the two leaks placeholder
    | credentials ("Admin", "admin@example.com") into rendered pages.
    |
    */

    'public_identity' => [
        'founder_name' => env('FOUNDER_NAME', 'Sheikh Nabil'),
        'contact_email' => env('CONTACT_EMAIL', 'hello@sheikhnabil.com'),
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
