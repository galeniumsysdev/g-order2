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
			->body($this->data['message']);
	}
	/**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
	public function toDatabase($notifiable)
	{
		// return $this->data;
		return [
			'tipe'=> $this->data['title'],
			'subject'=> $this->data['message'],
			'konten'=> $this->data
		];
	}
}
