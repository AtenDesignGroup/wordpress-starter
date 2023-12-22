<?php
namespace WPDRMS\ASP\Hooks\Ajax;

use WPDRMS\ASP\Patterns\SingletonTrait;

if (!defined('ABSPATH')) die('-1');

abstract class AbstractAjax {
	use SingletonTrait;
	/**
	 * The handler
	 *
	 * This function is called by the appropriate handler
	 */
	abstract public function handle();
}