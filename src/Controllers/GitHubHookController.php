<?php

namespace HighSolutions\GitHubHook\Controllers;

use HighSolutions\GitHubHook\Events\AfterHooks;
use HighSolutions\GitHubHook\Events\BeforeHooks;
use HighSolutions\GitHubHook\Events\RequestFailed;
use HighSolutions\GitHubHook\Events\RequestReceived;
use HighSolutions\GitHubHook\Events\RequestSucceed;
use HighSolutions\GitHubHook\Services\GitHubHookCommands;
use HighSolutions\GitHubHook\Services\GitHubHookService;
use Illuminate\Http\Request;

class GitHubHookController
{
	protected $payload = null;

	public function fetch(Request $request)
	{
		$response = $this->manageRequest($request);
		
		$this->displayResponse($response);

		if($response['success'])
			$this->manageHooks();
	}

	private function manageRequest($request)
	{
		event(new RequestReceived($request));

		$xHubSignature = $request->header("x-hub-signature");
		$this->payload = $request->getContent();

		$service = new GitHubHookService([
			'secret' => config('github-hook.secret'),
			'branch' => config('github-hook.branch'),
			'path' => base_path(),
		]);

		return $service->receive($this->payload, $xHubSignature);
	}

	private function displayResponse($response)
	{
		if($response['success']) {
			$this->payload = $response['payload'];
			event(new RequestSucceed($this->payload));
			$this->displayLog($response['message']); 
		} else {
			event(new RequestFailed($response['message'], isset($response['payload']) ? $response['payload'] : false));
			$this->displayLog('Failed:'. PHP_EOL. $response['message']);
		}
	}

	private function manageHooks()
	{
        event(new BeforeHooks());

		$commands = new GitHubHookCommands([
			'migration' => config('github-hook.hooks.migration'),
			'seed' => config('github-hook.hooks.seed'),
			'refresh' => config('github-hook.hooks.refresh'),
			'composer' => config('github-hook.hooks.composer'),
		], base_path());
		$commands->handle($this->payload);

        event(new AfterHooks());

		$this->displayLog(PHP_EOL. 'Done.');
	}

	protected function displayLog($message)
	{
		echo $message . PHP_EOL;
	}
	
}