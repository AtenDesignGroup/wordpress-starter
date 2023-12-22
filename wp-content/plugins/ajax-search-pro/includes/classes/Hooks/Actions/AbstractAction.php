<?php
namespace WPDRMS\ASP\Hooks\Actions;

use WPDRMS\ASP\Patterns\SingletonTrait;

if (!defined('ABSPATH')) die('-1');

abstract class AbstractAction {
	use SingletonTrait;
	/**
	 * The handler
	 *
	 * This function is called by the appropriate handler
	 */
	abstract public function handle();
}