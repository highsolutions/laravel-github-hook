<?php

namespace HighSolutions\GitHubHook\Services;

use Illuminate\Support\Arr;

class GitHubHookCommands
{

	protected $hooks = [
		'before' => false,
		'migration' => false,
		'seed' => false,
		'refresh' => false,
		'composer' => false,
		'cache' => false,
		'view' => false,
		'after' => false,
	];
	protected $changes;
	protected $path;

	public function __construct($hooks, $path)
	{
		$this->hooks = $hooks;
		$this->changes = $this->initChanges();
		$this->path = $path;
	}

	public function handle($payload)
	{
		$this->displayLog('Post-hook commands started.'. PHP_EOL);
	
		$this->analyzePayload($payload);

		$this->launchHooks();
	}

	protected function initChanges()
	{
		return [
			'migration' => [
				'add' => false,
				'update' => false,
			],
			'seed' => [
				'update' => false,
			],
			'composer' => [
				'install' => false,
			],
		];
	}

	protected function analyzePayload($payload)
	{		
		foreach($payload->commits as $commit) {
			$this->checkAddedFiles($commit);
			$this->checkUpdatedFiles($commit);
			$this->checkDeletedFiles($commit);
		}
	}

	protected function checkAddedFiles($commit)
	{
		foreach($commit->added as $file) {
			if ($this->isMigration($file))
				$this->changes['migration']['add'] = true;
			elseif ($this->isSeed($file))
				$this->changes['seed']['update'] = true;
		}
	}

	protected function checkUpdatedFiles($commit)
	{
		foreach($commit->modified as $file) {
			if ($this->isMigration($file))
				$this->changes['migration']['update'] = true;
			elseif ($this->isSeed($file))
				$this->changes['seed']['update'] = true;
			elseif ($this->isComposer($file))
				$this->changes['composer']['install'] = true;
		}
	}

	protected function checkDeletedFiles($commit)
	{
		foreach($commit->removed as $file) {
			if ($this->isMigration($file))
				$this->changes['migration']['update'] = true;
			elseif ($this->isSeed($file))
				$this->changes['seed']['update'] = true;
		}
	}

	protected function isMigration($file)
	{
		return $this->contains($file, 'database/migrations/');
	}

	protected function isSeed($file)
	{
		return $this->contains($file, 'database/seeds/');
	}

	protected function isComposer($file)
	{
		return $file == 'composer.json';
	}

	protected function contains($file, $path)
	{
		return strpos($file, $path) !== false;
	}

	protected function launchHooks()
	{
		$this->triggerHooks('before');

		if($this->isChanged('composer') && $this->isHookActive('composer'))
			$this->launchHook('composer');

		if($this->isChanged('seed') && $this->isHookActive('seed')) {
			if($this->isChanged('migration', 'update') && $this->isHookActive('refresh'))
				$this->launchHook('refresh');
			elseif($this->isChanged('migration', 'add') && $this->isHookActive('migration')) {
				$this->launchHook('migration');
				$this->launchHook('seed');
			}
			else {
				$this->launchHook('seed');
			}
		} elseif ($this->isChanged('migration') && $this->isHookActive('migration')) {
			if($this->isChanged('migration', 'update') && $this->isHookActive('refresh'))
				$this->launchHook('refresh');
			elseif($this->isChanged('migration', 'add') && $this->isHookActive('migration')) {
				$this->launchHook('migration');
			}
		}

		if($this->isHookActive('cache')) {
			$this->launchHook('cache');
		}

		if($this->isHookActive('view')) {
			$this->launchHook('view');
		}

		$this->triggerHooks('after');
	}

	protected function isChanged($key, $operation = null)
	{
		if($operation !== null)
			return isset($this->changes[$key][$operation]) ? $this->changes[$key][$operation] : false;

		foreach($this->changes[$key] as $row) {
			if($row === true)
				return true;
		}

		return false;
	}

	protected function isHookActive($key)
	{
		return isset($this->hooks[$key]) && $this->hooks[$key] != false;
	}

	protected function launchHook($command)
	{
		$exitCode = 0;
		$output = [];
		exec($this->prepareCommand($command), $output, $exitCode);

		$this->displayLog("Hook '{$command}' launched ({$exitCode}): ". join(PHP_EOL, $output));
	}

	protected function prepareCommand($command)
	{
		if(!isset($this->hooks[$command]))
			return $command;
		
		return str_replace(
			['artisan', 'composer.phar', 'composer install'], 
			[$this->path .'/artisan', $this->path .'/composer.phar', 'composer install -d '. $this->path], 
			$this->hooks[$command]
		);
	}

	protected function displayLog($message)
	{
		echo $message . PHP_EOL;
	}

	protected function triggerHooks($type)
	{
		collect(explode('###', Arr::get($this->hooks, $type)))
			->each(function ($command) {
				$this->launchHook($command);
			});
	}

}
