<?php
namespace WPDRMS\ASP\Hooks\Ajax;

use WPDRMS\ASP\Index\Database;
use WPDRMS\ASP\Utils\Ajax;

if (!defined('ABSPATH')) die('-1');


class Maintenance extends AbstractAjax {
	public function handle() {
		if ( 
			current_user_can( 'administrator' )
		) {
			if (ASP_DEMO) {
				print "Maintenance !!!ASP_MAINT_START!!!";
				print_r(json_encode(array(
					'status'    => 0,
					'action'    => '',
					'msg'       => 'Not allowed in demo mode!'
				)));
				Ajax::prepareHeaders();
				print "!!!ASP_MAINT_STOP!!!";
				die();
			}
	
			$status = 0;
			$msg = 'Missing POST information, please try again!';
			$action = 'none';
			$nonce = false;
			if ( isset($_POST, $_POST['data']) ) {
				if (is_array($_POST['data']))
					$data = $_POST['data'];
				else
					parse_str($_POST['data'], $data);
				if ( isset($data['asp_reset_nonce']) ) {
					$nonce = 'asp_reset_nonce';
				} else if ( isset($data['asp_wipe_nonce']) ) {
					$nonce = 'asp_wipe_nonce';
				} else if ( isset($data['asp_index_defrag_nonce']) ) {
					$nonce = 'asp_index_defrag_nonce';
				}
				if (
					$nonce !== false &&
					isset($data[$nonce]) &&
					wp_verify_nonce( $data[$nonce], $nonce )
				) {
					if ( $nonce == 'asp_reset_nonce' ) { // Reset
						wd_asp()->init->pluginReset();
						$status = 1;
						$action = 'refresh';
						$msg = 'The plugin data was successfully reset!';
					} else if ( $nonce == 'asp_wipe_nonce' ) {  // Wipe
						wd_asp()->init->pluginWipe();
						$status = 1;
						$action = 'redirect';
						$msg = 'All plugin data was successfully wiped, you will be redirected in 5 seconds!';
					} else {
						$it = new Database();
						$it->optimize();
						$status = 1;
						$action = 'nothing';
						$msg = 'Index Table Optimized and Defragmented!';
					}
				} else {
					$msg = 'Missing or invalid NONCE, please <strong>reload this page</strong> and try again!';
				}
			}
			$ret = array(
				'status'    => $status,
				'action'    => $action,
				'msg'       => $msg
			);
			Ajax::prepareHeaders();
			print "Maintenance !!!ASP_MAINT_START!!!";
			print_r(json_encode($ret));
			print "!!!ASP_MAINT_STOP!!!";
		}
		die();
	}
}