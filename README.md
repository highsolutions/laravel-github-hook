Laravel GitHub Hook
===================

Easy continuous integration based on GitHub.

![Laravel-GitHub-Hook by HighSolutions](https://raw.githubusercontent.com/highsolutions/laravel-github-hook/master/intro.jpg)

Installation
------------

Add the following line to the `require` section of your Laravel webapp's `composer.json` file:

```javascript
    "require": {
        "HighSolutions/github-hook": "*"
    }
```

Run `composer update` to install the package.

Then add ServiceProvider to `app\Providers\AppServiceProvider.php`:

```php

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        /* ... */

        $this->app->register(HighSolutions\GitHubHook\GitHubHookServiceProvider::class);
    }
```

Configuration
-------------

Publish vendor assets:

```bash
php artisan vendor:publish
```

and you can specify some essentials settings (in ENV and in config file called `github-hook.php`):

| Setting name      | Description                                                         | ENV variable               | Default value |
|-------------------|---------------------------------------------------------------------|----------------------------|---------------|
| url               | URL for webhook from GitHub to your.                                | GITHUB_HOOK_URL            | /github/hook/ |
| branch            | Pulled repository branch on server.                                 | GITHUB_HOOK_BRANCH         | master        |
| secret            | GitHub webook secret code.                                          | GITHUB_HOOK_SECRET         | null          |
| auto_migration    | If true, after successful deploy, migrate command will be executed. | GITHUB_HOOK_AUTO_MIGRATION | true          |
| auto_seed         | If true, after successful deploy, db:seed command will be executed. | GITHUB_HOOK_AUTO_SEED      | false         |
| slack.sender      | Name of sender of Slack notification.                               | GITHUB_HOOK_SLACK_SENDER   | GitHub Hook   |
| slack.channel     | Channel where Slack notification will be posted.                    | GITHUB_HOOK_SLACK_CHANNEL  |               |
| slack.webhook_url | Slack webhook url. If empty, notification won't be send.            | GITHUB_HOOK_SLACK_URL      |               |

Changelog
---------

0.1.0

- created package
- events
- slack notification
- hash verification

Roadmap
-------

* No Laravel alternative version
* More hooks
* Conditional hooks
* Unit tests!

Credits
-------

This package is developed by [HighSolutions](http://highsolutions.pl), software house from Poland in love in Laravel.