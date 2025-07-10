<?php
/**
 * Aten Resizable Sidebar Plugin
 *
 * @package Aten_Resizable_Sidebar
 */

/**
 * Plugin Name:       Aten Resizable Sidebar
 * Description:       This custom plugin provides functionality to adjust the sidebar width in the block editor, storing each user's preference in their browser's local storage.
 * Requires at least: 5.8
 * Requires PHP:      7.0
 * Version:           1.0.0
 * Author:            Aten Design Group
 * Author URI:        https://atendesigngroup.com/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package           Aten_Resizable_Sidebar
 */
function aten_resizable_sidebar_enqueue_assets() {
	wp_enqueue_script( 'jquery-ui-resizable' );
	wp_enqueue_script( 'aten-resizable-sidebar-script', plugin_dir_url( __FILE__ ) . '/dist/scripts.min.js', array( 'jquery-ui-resizable' ), null, true );
	wp_enqueue_style( 'aten-resiable-sidebar-styles', plugin_dir_url( __FILE__ ) . '/dist/styles.min.css', array(), time() );
}
add_action( 'admin_enqueue_scripts', 'aten_resizable_sidebar_enqueue_assets', 20 );
