<?php

namespace HighSolutions\GitHubHook\Events;

use Illuminate\Queue\SerializesModels;

class RequestFailed
{
    use SerializesModels;
	
	public $message;
	public $payload;

	public function __construct($message, $payload = false)
	{
		$this->message = $message;
		$this->payload = $payload;
	}

	public function getRepository()
	{		
		return $this->payload->repository->full_name;
	}

	public function getBranch()
	{		
		return substr($this->payload->ref, strlen('refs/heads/'));
	}

	public function getCommitName()
	{		
		return $this->payload->head_commit->message;
	}

	public function getCommitSHA()
	{		
		return $this->payload->head_commit->id;
	}

	public function getSender()
	{		
		return $this->payload->head_commit->committer->name;
	}
}