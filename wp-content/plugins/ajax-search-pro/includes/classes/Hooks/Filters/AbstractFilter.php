<?php
namespace WPDRMS\ASP\Hooks\Filters;

use WPDRMS\ASP\Patterns\SingletonTrait;

if (!defined('ABSPATH')) die('-1');

abstract class AbstractFilter {
	use SingletonTrait;
	/**
	 * The handler
	 *
	 * This function is called by the appropriate handler
	 */
	abstract public function handle();
}