<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\PusherPushNotifications\PusherChannel;
use NotificationChannels\PusherPushNotifications\PusherMessage;

class DummyNotif extends Notification {
	public function via($notifiable) {
		return [PusherChannel::class];
	}

	public function toPushNotification($notifiable) {
		return PusherMessage::create()
			->android()
			->title('test')
			->body('dummy');
	}
}
