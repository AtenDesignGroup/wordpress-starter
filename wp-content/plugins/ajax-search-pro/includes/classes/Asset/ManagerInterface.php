<?php
namespace WPDRMS\ASP\Asset;

/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

interface ManagerInterface {
	// To be called before (or within) wp_print_footer_scripts|admin_print_footer_scripts
	function enqueue( $force = false );

	// To be called on shutdown - backup print scripts for panic mode
	function printInline( $instances = array() );

	// Injection handler for the output buffer
	function injectToBuffer($buffer, $instances);
}