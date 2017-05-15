<?php

namespace HighSolutions\GitHubHook;

use HighSolutions\GitHubHook\Services\GitHubHookListener;
use HighSolutions\GitHubHook\Services\GitHubHookService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class GitHubHookServiceProvider extends ServiceProvider 
{

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
        $this->publishAssets();
        $this->listenEvents();
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->manageRoutes();
        $this->manageControllers();
        $this->registerServices();
	}

    /**
     * Publishes assets of package.
     * 
     * @return void
     */
    protected function publishAssets()
    {
        $this->publishes([
            __DIR__ . '/config/config.php' => config_path('github-hook.php'),
        ], 'config');
    }

    /**
     * Registers Event's listeners.
     * 
     * @return void
     */
    protected function listenEvents()
    {
        Event::listen('HighSolutions\GitHubHook\Events\RequestFailed', function ($event) {
            (new GitHubHookListener)->handle($event);
        });
    }

    /**
     * Sets the routing of package.
     * 
     * @return void
     */
    protected function manageRoutes()
    {
        include __DIR__ . '/routes/routes.php';
    }

    /**
     * Loads package's controllers.
     * 
     * @return void
     */
    protected function manageControllers()
    {
         $this->app->make(__NAMESPACE__ .'\Controllers\\GitHubHookController');
    }

    /**
     * Registers additional services.
     * 
     * @return void
     */
    protected function registerServices()
    {
		$this->app->singleton('GitHubHookService', function() {
			return GitHubHookService;
		});
    }

}
