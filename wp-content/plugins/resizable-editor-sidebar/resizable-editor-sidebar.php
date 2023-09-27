<?php /*
   * Plugin Name: Resizable Editor Sidebar
   * Description: Enables functionality to make the Gutenberg sidebar width resizable
   * Version: 1.0.1
   * Author: Toast Plugins
   * Author URI: https://www.toastplugins.co.uk/
   * Licence: GPLv2 or later
   */ ?>
<?php function toast_rs_enqueue(){
    wp_enqueue_script( 'jquery-ui-resizable');
    wp_enqueue_script( 'toast_rs_script', plugin_dir_url( __FILE__ ) . 'script.js', array('jquery-ui-resizable'), null, true);
    wp_enqueue_style( 'toast_rs_style', plugin_dir_url( __FILE__ ) . 'style.css');
}
add_action('admin_enqueue_scripts', 'toast_rs_enqueue');

