<?php

namespace HighSolutions\GitHubHook\Services;

class GitHubHookService {
	
	protected $config = [
		'secret' => null,
		'branch' => 'master',
		'path' => '',
	];
	protected $payload = null;

	public function __construct($config) 
	{
		$this->config = array_merge($this->config, $config);
	}

	public function receive($rawPayload, $signature)
	{
		$this->payload = $rawPayload;

		$message = $this->checkSecretIfNecessary($signature);
		if($message !== true)
			return $this->returnError('Hook secret is invalid.');

		return $this->handle();
	}

	protected function checkSecretIfNecessary($signature)
	{
		if($this->config['secret'] == false)
			return true;

		if($signature === null)
			return $this->returnError('HTTP header "X-Hub-Signature" is missing.');

		list($algo, $hash) = explode('=', $signature) + ['', ''];
		$message = $this->checkHashLoaders($algo);
		if($message !== true)
			return $this->returnError($message);

		if ($hash !== hash_hmac($algo, $this->payload, $this->config['secret']))
			return $this->returnError('Hook secret does not match.');

		return true;
	}

	protected function checkHashLoaders($algo)
	{
		if (!extension_loaded('hash'))
			return 'Missing "hash" extension to check the secret code validity.';

		if (!in_array($algo, hash_algos(), true))
			return "Hash algorithm '$algo' is not supported.";

		return true;
	}

	protected function handle()
	{
		$this->payload = $this->decodePayload();

		if($this->isPing())
			return [
				'success' => true,
				'message' => 'Ping correct.'
			];

		if (!$this->isPayloadCorrect())
			return $this->returnError('Incorrect payload.');

		$branch = $this->getBranch();
		if ($this->config['branch'] !== $branch)
			return $this->returnError("Push concerns different branch: '{$branch}'.");

		$response = $this->triggerPull();
		if($response !== true)
			return $response;

		return [
			'success' => true,
			'message' => 'Deploy succeeded.',
			'payload' => $this->payload,
		];
	}

	protected function decodePayload()
	{
		$json = json_decode($this->payload);
		if (json_last_error() === JSON_ERROR_NONE) {
			if(isset($json->payload))
				return $json->payload;
			return $json;
		}

		// x-www-form-urlencoded
		$decoded = urldecode($this->payload);
		$transformed = str_replace('payload=', '{"payload":', $decoded) . '}';
		$json = json_decode($transformed);
		if (json_last_error() === JSON_ERROR_NONE && isset($json->payload))
			return $json->payload;

		return false;
	}

	protected function isPing()
	{
		return $this->payload !== false && isset($this->payload->zen);
	}

	protected function isPayloadCorrect()
	{
		return $this->payload !== false && isset($this->payload->ref);
	}

	protected function getBranch()
	{
		return substr($this->payload->ref, strlen('refs/heads/'));
	}

	protected function triggerPull()
	{ 
		$output = [];
        $exit = 0;
        $branch = $this->config['branch'];
        $path = $this->config['path'];
    	$cmd = 'git --git-dir='. escapeshellarg("{$path}/.git") .' --work-tree='. escapeshellarg($path) .' pull origin '. $this->config['branch'] .' --no-edit';

    	$this->displayLog("Start deploying for branch '{$branch}'.");
		exec($cmd, $output, $exit);

        $msg = join(PHP_EOL . "\t", $output);
		if ($exit !== 0)
			return $this->returnError("Error({$exit}): " . "\t" . $msg, ['payload' => $this->payload]);
        else
        	$this->displayLog('Git output:'. PHP_EOL . $msg . PHP_EOL); 

        return true;
	}

	protected function returnError($message, $additional = [])
	{
		return [
			'success' => false,
			'message' => $message,
		] + $additional;
	}

	public function displayLog($message)
	{
		echo $message . PHP_EOL;
	}	

	public static function simpleInit($config, $payload, $xHubSignature)
	{
		$service = new static($config);
		$response = $service->receive($payload, $xHubSignature);
		$service->displayLog($response['message']);
	}

}