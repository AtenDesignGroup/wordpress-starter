<?php
namespace WPDRMS\ASP\Hooks\Ajax;

use WPDRMS\ASP\Misc\Statistics;
use WPDRMS\ASP\Utils\Ajax;

if (!defined('ABSPATH')) die('-1');


class DeleteKeyword extends AbstractAjax {
	function handle() {
		if ( 
			isset($_POST['asp_statistics_request_nonce']) &&
			wp_verify_nonce( $_POST['asp_statistics_request_nonce'], 'asp_statistics_request_nonce' ) &&
			current_user_can( 'administrator' )
		) {
			if (isset($_POST['keywordid'])) {
				echo Statistics::deleteKw($_POST['keywordid'] + 0);
				exit;
			}
			Ajax::prepareHeaders();
			echo 0;
		}
		die();
	}
}