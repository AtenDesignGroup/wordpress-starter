<?php
namespace WPDRMS\ASP\Hooks\Ajax;

use WPDRMS\ASP\Utils\Ajax;

if (!defined('ABSPATH')) die('-1');


class Preview extends AbstractAjax {
	function handle() {
		if ( 
			isset($_POST['asp_backend_preview_nonce']) &&
			wp_verify_nonce( $_POST['asp_backend_preview_nonce'], 'asp_backend_preview_nonce' ) &&
			current_user_can( 'administrator' )
		) {
			$o = \WPDRMS\ASP\Shortcodes\Search::getInstance();
			// Needs to be here, as the $o->handle(..) also prints out things :)
			Ajax::prepareHeaders();
			$out = $o->handle(array(
				"id" => $_POST['asid']
			));
			print $out;
		}
		die();
	}
}