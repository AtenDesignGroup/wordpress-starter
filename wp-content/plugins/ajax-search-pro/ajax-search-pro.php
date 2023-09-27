<?php
/*
Plugin Name: Ajax Search Pro
Plugin URI: https://ajaxsearchpro.com
Description: The most powerful live search engine for WordPress.
Version: 4.26.2
Requires PHP: 7.0
Requires at least: 4.9
Author: Ernest Marcinko
Author URI: https://codecanyon.net/user/wpdreams
Text Domain: ajax-search-pro
Domain Path: /languages/
*/

use WPDRMS\ASP\Core\Globals;
use WPDRMS\ASP\Core\Manager;

defined('ABSPATH') or die("You can't access this file directly.");
define('ASP_FILE', __FILE__);
define('ASP_PLUGIN_BASE', plugin_basename( ASP_FILE ) );
define('ASP_PATH', plugin_dir_path(__FILE__));
define('ASP_CSS_PATH', plugin_dir_path(__FILE__)."/css/");
define('ASP_INCLUDES_PATH', plugin_dir_path(__FILE__)."/includes/");
define('ASP_CLASSES_PATH', plugin_dir_path(__FILE__)."/includes/classes/");
define('ASP_EXTERNALS_PATH', plugin_dir_path(__FILE__)."/includes/externals/");
define('ASP_FUNCTIONS_PATH', plugin_dir_path(__FILE__)."/includes/functions/");
define('ASP_DIR', 'ajax-search-pro');
define('ASP_PLUGIN_NAME', 'ajax-search-pro/ajax-search-pro.php');
define('ASP_SITE_IS_PROBABLY_SSL', strpos(home_url('/'), 'https://') !== false || strpos(plugin_dir_url(__FILE__), 'https://') !== false);
define(
    'ASP_URL',
    ASP_SITE_IS_PROBABLY_SSL ?
    str_replace('http://', 'https://', plugin_dir_url(__FILE__)) : plugin_dir_url(__FILE__)
);
define('ASP_URL_NP',  str_replace(array("http://", "https://"), "//", plugin_dir_url(__FILE__)));
define('ASP_CURR_VER', 5060);
define('ASP_CURR_VER_STRING', "4.26.2");
define('ASP_PLUGIN_SLUG', plugin_basename(__FILE__) );
define('ASP_DEBUG', 0);
define('ASP_DEMO', get_option('wd_asp_demo', 0) );
// The one and most important global
global $wd_asp;

require_once(ASP_CLASSES_PATH . "Autoloader.php");
$wd_asp = new Globals();

if ( !function_exists("wd_asp") ) {
    /**
     * Easy access of the global variable reference
     *
     * @return Globals
     */
    function wd_asp() {
        global $wd_asp;
        return $wd_asp;
    }
}

// Initialize the plugin
$wd_asp->manager = Manager::getInstance();