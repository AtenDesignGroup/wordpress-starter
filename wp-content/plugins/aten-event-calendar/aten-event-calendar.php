<?php
/**
 * Plugin Name:       Aten Event Calendar
 * Description:       This custom plugin creates unique post types, custom fields, and taxonomies for the Aten Event Calendar. Requires the following plugins: ACF PRO.
 * Requires at least: 5.8
 * Requires PHP:      7.0
 * Version:           1.0.0
 * Author:            Aten Design Group
 * Author URI:        https://atendesigngroup.com/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package           Aten_Event_Calendar
 */

// Load the post type generator.
require_once plugin_dir_path( __FILE__ ) . 'inc/generate-custom-post-types.php';

// Load the taxonomy generator.
require_once plugin_dir_path( __FILE__ ) . 'inc/generate-custom-taxonomies.php';

// Load the custom field generator.
require_once plugin_dir_path( __FILE__ ) . 'inc/generate-custom-fields.php';
