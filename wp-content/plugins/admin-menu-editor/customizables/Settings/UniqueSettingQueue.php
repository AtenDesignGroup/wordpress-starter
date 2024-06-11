<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Settings;

class UniqueSettingQueue {
	/**
	 * @var NotificationSenderQueue[]
	 */
	protected $internalQueues = [];

	public function __construct() {
		$this->internalQueues = [
			'basic'    => new NotificationSenderQueue(),
			'deferred' => new NotificationSenderQueue(),
		];
	}

	public function enqueue(AbstractSetting $setting) {
		$this->internalQueues['basic']->enqueue($setting);
	}

	public function enqueueDeferred(UpdateNotificationSender $setting) {
		$this->internalQueues['deferred']->enqueue($setting);
	}

	public function dequeue() {
		foreach ($this->internalQueues as $queue) {
			$sender = $queue->dequeue();
			if ( $sender !== null ) {
				return $sender;
			}
		}
		return null;
	}

	public function remove(UpdateNotificationSender $setting) {
		foreach ($this->internalQueues as $queue) {
			$queue->remove($setting);
		}
	}

	public function isEmpty() {
		foreach ($this->internalQueues as $queue) {
			if ( !$queue->isEmpty() ) {
				return false;
			}
		}
		return true;
	}

	public function processAll() {
		while (!$this->isEmpty()) {
			$sender = $this->dequeue();
			$sender->notifyUpdated();
		}
	}
}