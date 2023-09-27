<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */
/** @noinspection PhpIncludeInspection */

namespace WPDRMS\ASP\Hooks\Ajax;

use WPDRMS\ASP\Utils\Ajax;
use WPDRMS\ASP\Media\RemoteService;

if (!defined('ABSPATH')) die('-1');


class MediaService extends AbstractAjax {
	function handle() {
		if ( 
			isset($_POST['asp_mediaservice_request_nonce']) &&
			wp_verify_nonce( $_POST['asp_mediaservice_request_nonce'], 'asp_mediaservice_request_nonce' ) &&
			current_user_can( 'administrator' )
		) {
			if ( isset($_POST['ms_deactivate']) ) {
				RemoteService\License::getInstance()->delete();
				Ajax::prepareHeaders();
				print 0;
			} else {
				$success = 0;
				if ( isset($_POST['ms_license_key']) ) {
					$r = RemoteService\License::getInstance()->activate($_POST['ms_license_key']);
					$success = $r['success'];
					$text = $r['text'];
				} else {
					$text = "License key is missing or invalid.";
				}
				Ajax::prepareHeaders();
				print_r(json_encode(array(
					'success' => $success,
					'text' => $text
				)));
			}
		}
		exit;
	}
}