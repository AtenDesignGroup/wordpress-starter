<?php
// Load up a configuration file with the paths
// This config is set via the back-end during saving the options
include(dirname(__FILE__) . '/sc-config.php');
die(); // Disabled yet

function _asp_do_sc_resume_admin_ajax() {
	if ( file_exists(ASP_SC_ADMIN_AJAX_PATH) ) {
		require_once(ASP_SC_ADMIN_AJAX_PATH);
	} else if ( file_exists(dirname('../../../index.php' ) . '/.wordpress/wp-admin/admin-ajax.php') ) {
		// FLYWHEEL hosting
		require_once( dirname('../../../index.php' ) . '/.wordpress/wp-admin/admin-ajax.php' );
	} else {
		// DEFAULT
		require_once('../../../wp-admin/admin-ajax.php');
	}
}

function _asp_do_sc_ajax_call() {
	if ( !isset($_POST, $_POST['options'], $_POST['aspp'], $_POST['asid']) ) {
		return false;
	}
	$call_num = $_POST['asp_call_num'] ?? 0;

	if (is_array($_POST['options'])) {
		$options = $_POST['options'];
	} else {
		parse_str($_POST['options'], $options);
	}
	unset($options['filters_initial'], $options['filters_changed']);
	$file_name = "xasp_" . md5(json_encode($options) . $call_num . $_POST['aspp'] . (int)$_POST['asid']) . ".wpd";
	$cache_file = ASP_SC_CACHE_PATH . $file_name;
	if ( file_exists($cache_file) ) {
		$filetime = filemtime($cache_file);
		if ( $filetime === false || (time() - $filetime) > ASP_SC_CACHE_INTERVAL ) {
			echo "File expired: " . (time() - $filetime);
			_asp_do_sc_resume_admin_ajax();
		} else {
			// return the cached file contents
			echo "Cached: " . time();
			echo file_get_contents($cache_file);
			return false;
		}
	} else {
		_asp_do_sc_resume_admin_ajax();
	}
}

_asp_do_sc_ajax_call();
