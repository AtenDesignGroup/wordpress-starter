<?php
// -- AJAX_SEARCH.PHP --
//mimic the actual admin-ajax
use WPDRMS\ASP\Hooks\AjaxManager;

define('DOING_AJAX', true);

if (!isset($_POST['action']))
    die('-1');

//make sure you update this line
//to the relative location of the wp-load.php
if ( file_exists('../../../wp-load.php') ) {
	require_once('../../../wp-load.php');
} else if ( file_exists(dirname('../../../index.php' ) . '/.wordpress/wp-load.php') ) {
	// FLYWHEEL hosting
	require_once( dirname('../../../index.php' ) . '/.wordpress/wp-load.php' );
} else {
	// DEFAULT
	require_once('../../../wp-load.php');
}

//Typical headers
header('Content-Type: text/plain');
send_nosniff_header();

//Disable caching
header('Cache-Control: no-cache');
header('Pragma: no-cache');

global $wd_asp;

$action = esc_attr(trim($_POST['action']));

//A bit of security
$allowed_actions = AjaxManager::getAll();
AjaxManager::registerAll(true);

if (in_array($action, $allowed_actions)) {
    if (is_user_logged_in())
        do_action('ASP_' . $action);
    else
        do_action('ASP_nopriv_' . $action);
} else {
    die('-1');
}