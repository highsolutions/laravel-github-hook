<?php

return [

    /*
    |--------------------------------------------------------------------------
    | GitHub Hook Configuration
    |--------------------------------------------------------------------------
    |
    */

    'url' => env('GITHUB_HOOK_URL', '/github/hook/'),
    'branch' => env('GITHUB_HOOK_BRANCH', 'master'),
    
    'secret' => env('GITHUB_HOOK_SECRET', null),

    'hooks' => [
        'migration' => env('GITHUB_HOOK_HOOK_MIGRATION', 'php artisan migrate --force'),
        'seed' => env('GITHUB_HOOK_HOOK_SEED', 'php artisan db:seed --force'),
        'refresh' => env('GITHUB_HOOK_HOOK_REFRESH', 'php artisan migrate:refresh --seed --force'),
        'composer' => env('GITHUB_HOOK_HOOK_COMPOSER', 'composer install --no-dev'),
    ],

    'slack' => [
        'sender' => env('GITHUB_HOOK_SLACK_SENDER', 'GitHub Hook'),
        'channel' => env('GITHUB_HOOK_SLACK_CHANNEL', ''),
        'webhook_url' => env('GITHUB_HOOK_SLACK_URL', ''),
    ],

];
