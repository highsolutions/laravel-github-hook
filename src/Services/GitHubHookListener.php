<?php

namespace HighSolutions\GitHubHook\Services;

use HighSolutions\GitHubHook\Events\RequestFailed;
use HighSolutions\GitHubHook\Services\SlackNotification;
use Illuminate\Notifications\Notifiable;

class GitHubHookListener
{
	use Notifiable;
	
	protected $config = [
		'url' => '',
	];

	public $event;

	public function __construct()
    {
        $this->config['url'] = config('github-hook.slack.webhook_url');
    }

    /**
     * Route notifications for the Slack channel.
     *
     * @return string
     */
    public function routeNotificationForSlack()
    {
        return $this->config['url'];
    }

    /**
     * Handle the event.
     *
     * @param  RequestFailed  $event
     * @return void
     */
    public function handle(RequestFailed $event)
    {
    	if($event->payload === false || $this->config['url'] === '')
    		return;
    	$this->event = $event;

        $this->sendNotification();
    }

    protected function sendNotification()
    {
		$this->notify(new SlackNotification());
    }
}