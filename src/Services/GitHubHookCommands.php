<?php

namespace HighSolutions\GitHubHook\Services;

use Illuminate\Support\Facades\Artisan;

class GitHubHookCommands {
	
	protected $config;

	public function __construct($config)
	{
		$this->config = $config;
	}

	public function handle($rawPayload)
	{
		$this->displayLog('Post-hook commands started.'. PHP_EOL);

		if($this->getConfig('migration') && $this->getConfig('seed')) {
			$this->launchCommand('migrate:refresh', [
				'--seed' => true,
	            '--force' => true,
	        ]);
	    } else {
			if($this->getConfig('migration')) {
				$this->launchCommand('migrate', [
		            '--force' => true
		        ]);
		    }

			if($this->getConfig('seed')) {
				$this->launchCommand('db:seed', [
		            '--force' => true
		        ]);
		    }
	    }
	}

	protected function getConfig($key)
	{
		return isset($this->config[$key]) && $this->config[$key];
	}

	protected function launchCommand($command, $params)
	{
		Artisan::call($command, $params);
        print_r(Artisan::output());
	}

	protected function displayLog($message)
	{
		echo $message . PHP_EOL;
	}

}