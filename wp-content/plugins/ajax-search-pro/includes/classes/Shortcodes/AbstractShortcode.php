<?php
namespace WPDRMS\ASP\Shortcodes;

if (!defined('ABSPATH')) die('-1');


abstract class AbstractShortcode {
	/**
	 * This function is called by the appropriate handler
	 *
	 * @param $atts
	 */
	abstract public function handle( $atts );
}