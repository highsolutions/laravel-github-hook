<?php

namespace HighSolutions\GitHubHook\Events;

use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;

class RequestReceived
{
    use SerializesModels;
	
	public $request;

	public function __construct(Request $request)
	{
		$this->request = $request;
	}
}