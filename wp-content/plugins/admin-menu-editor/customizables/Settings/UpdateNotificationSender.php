<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Settings;

interface UpdateNotificationSender {
	/**
	 * Notify subscribers that the setting or settings associated with this object have been updated.
	 *
	 * @return void
	 */
	public function notifyUpdated();
}