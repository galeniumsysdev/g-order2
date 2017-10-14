<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class HelloPusherEvent implements ShouldBroadcast {
	use SerializesModels;

	/**
	 * Only (!) Public members will be serialized to JSON and sent to Pusher
	 **/
	public $message;

	/**
	 * Create a new event instance.
	 *
	 * @return void
	 */
	public function __construct($message) {
		$this->message = $message;
	}

	/**
	 * Get the channels the event should broadcast on.
	 *
	 * @return Channel|array
	 */
	public function broadcastOn() {
		return ['my-channel'];
	}
}
