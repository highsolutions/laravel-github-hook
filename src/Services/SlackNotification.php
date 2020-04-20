<?php

namespace HighSolutions\GitHubHook\Services;

use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class SlackNotification extends Notification
{

	protected $config = [
		'channel' => '',
		'sender' => '',
	];

	public function __construct()
    {
        $this->config['channel'] = config('github-hook.slack.channel');
        $this->config['sender'] = config('github-hook.slack.sender');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['slack'];
    }

    /**
     * Get the Slack representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return SlackMessage
     */
    public function toSlack($notifiable)
    {
        return (new SlackMessage)
            ->error()
            ->from($this->config['sender'])
            ->to($this->config['channel'])
            ->attachment(function ($attachment) use ($notifiable) {
                $attachment->fields([
                	'Repository' => $notifiable->event->getRepository(),
                	'Branch' => $notifiable->event->getBranch(),
                	'Commit' => $notifiable->event->getCommitName(),
                	'SHA' => $notifiable->event->getCommitSHA(),
                	'Author' => $notifiable->event->getSender(),
                	'Server' => config('app.url') .' => '. config('app.env'),
                ])
                ->title('GitHubHook failed:'. PHP_EOL.  $notifiable->event->message);
            });
    }

}
