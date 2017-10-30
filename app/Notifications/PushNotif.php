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
		return [PusherChannel::class];
	}

	public function toPushNotification($notifiable) {
		return PusherMessage::create()
			->android()
			->title($this->data['title'])
			->setOption('custom', $this->data)
			->body($this->data['body']);
	}
}
