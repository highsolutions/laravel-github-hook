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

    'auto_migration' => env('GITHUB_HOOK_AUTO_MIGRATION', true),
    'auto_seed' => env('GITHUB_HOOK_AUTO_SEED', false),

    'slack' => [
        'sender' => env('GITHUB_HOOK_SLACK_SENDER', 'GitHub Hook'),
        'channel' => env('GITHUB_HOOK_SLACK_CHANNEL', ''),
        'webhook_url' => env('GITHUB_HOOK_SLACK_URL', ''),
    ],

];