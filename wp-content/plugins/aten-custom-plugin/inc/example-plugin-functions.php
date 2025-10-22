<?php
/**
 * This file performs custom functions for the Aten Custom Plugin.
 *
 * @package Aten_Custom_Plugin
 */

/**
 * Example custom function.
 * This function outputs a simple message to demonstrate adding custom functionality.
 */
function aten_custom_plugin_example_function() {
	ob_start();
	?>

	<h1>Hello from the Aten Custom Plugin!</h1>

	<?php
	return ob_get_clean();
}

add_action( 'init', 'aten_custom_plugin_example_function' );

