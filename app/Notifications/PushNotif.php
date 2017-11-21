<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\PusherPushNotifications\PusherChannel;
use NotificationChannels\PusherPushNotifications\PusherMessage;

class PushNotif extends Notification {
	public function __construct($data) {
		$this->data = $data;
	}

	public function via($notifiable) {
		return [PusherChannel::class, 'database'];
	}

	public function toPushNotification($notifiable) {
		return PusherMessage::create()
			->android()
			->title($this->data['title'])
			->setOption('custom', $this->data)
			->body($this->data['message'])
			->withiOS(PusherMessage::create()
				->title($this->data['title'])
				->setOption('custom', $this->data));
	}

	public function toMail($notifiable)
	{
		if($this->data['email']['markdown']){
			return (new MailMessage)
			          ->subject($this->data['title'])
			          ->markdown($this->data['email']['markdown'], $this->data['email']['attibute']);
		}
		else{
			return (new MailMessage)
													->subject($this->data['title'])
													->greeting($this->data['email']['greeting'])
													->line($this->data['email']['content']);
		}
	}

	/**
     * Get the array representation of the notification.
     *pa
     * @param  mixed  $notifiable
     * @return array
     */
	public function toDatabase($notifiable)
	{
		$href = $this->data['href'].'/'.$this->data['id'].'/'.$this->id;
		return [
			'tipe'=> $this->data['title'],
			'subject'=> $this->data['message'],
			'id'=> $this->data['id'],
			'href'=> $href,
			'content'=> $this->data
		];
	}
}
