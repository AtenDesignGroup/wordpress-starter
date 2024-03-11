<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Settings;

class NotificationSenderQueue {
	/**
	 * @var \SplObjectStorage<UpdateNotificationSender, bool>
	 */
	protected $isInQueue;
	/**
	 * @var \SplQueue<UpdateNotificationSender>
	 */
	protected $queue;

	public function __construct() {
		$this->queue = new \SplQueue();
		$this->isInQueue = new \SplObjectStorage();
	}

	public function enqueue(UpdateNotificationSender $setting) {
		if ( $this->isInQueue->contains($setting) ) {
			//Already in the queue. Let's just mark it as valid.
			$this->isInQueue[$setting] = true;
		} else {
			//Add to the queue.
			$this->isInQueue->attach($setting, true);
			$this->queue->enqueue($setting);
		}
	}

	public function dequeue() {
		//Find and return the first valid (non-removed) item.
		while (!$this->queue->isEmpty()) {
			$sender = $this->queue->dequeue();
			if ( $this->isInQueue[$sender] ) {
				$this->isInQueue->detach($sender);
				return $sender;
			}
		}

		return null;
	}

	public function remove(UpdateNotificationSender $setting) {
		if ( $this->isInQueue->contains($setting) ) {
			//There's not a quick way to remove an element from a SplQueue,
			//so we'll just mark the item as invalid. It will be removed
			//in dequeue().
			$this->isInQueue[$setting] = false;
		}
	}

	public function isEmpty() {
		return ($this->queue->isEmpty() || ($this->isInQueue->count() < 1));
	}
}