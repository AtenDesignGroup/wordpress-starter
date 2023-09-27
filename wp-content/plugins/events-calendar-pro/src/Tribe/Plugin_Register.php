<?php
/**
 * Class Tribe__Events__Pro__Plugin_Register
 */
class  Tribe__Events__Pro__Plugin_Register extends Tribe__Abstract_Plugin_Register {

	protected $main_class   = 'Tribe__Events__Pro__Main';
	protected $dependencies = [
		'parent-dependencies' => [
			'Tribe__Events__Main' => '6.2.0-dev',
		],
	];

	public function __construct() {
		$this->base_dir = EVENTS_CALENDAR_PRO_FILE;
		$this->version  = Tribe__Events__Pro__Main::VERSION;

		$this->register_plugin();
	}
}
