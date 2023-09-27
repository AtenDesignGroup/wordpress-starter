<?php
namespace WPDRMS\ASP\Hooks\Ajax;

use WPDRMS\ASP\Misc\EnvatoLicense;

if (!defined('ABSPATH')) die('-1');


class License extends AbstractAjax {
	function handle() {
		if ( 
			isset($_POST['asp_license_request_nonce']) &&
			wp_verify_nonce( $_POST['asp_license_request_nonce'], 'asp_license_request_nonce' ) &&
			current_user_can( 'administrator' )
		) {
			if ( !isset($_POST['op']) ) die(-2);

			if ( ASP_DEMO ) {
				print_r(json_encode(array("status"=>0, "msg"=>"This functions is disabled on this demo.")));
				die();
			}
	
			if ( $this->excessiveUsage() ) {
				print_r(json_encode(array("status"=>0, "msg"=>"WP Excessive usage Warning: Please wait a few seconds before the next request.")));
				die();
			}
	
			if ( $_POST['op'] == "activate" && !empty($_POST['asp_key']) ) {
				$key = $this->preValidateKey( $_POST['asp_key'] );
				if ( $key === false ) {
					print_r(json_encode(array("status"=>0, "msg"=>"WP: Invalid key specified.")));
					die();
				}
				$res = EnvatoLicense::activate( $key );
				if ($res === false)
					print_r(json_encode(array("status"=>0, "msg"=>"WP: Connection error, please try again later.")));
				else
					print_r(json_encode($res));
	
				die();
			} else if ($_POST['op'] == "deactivate") {
				$res = EnvatoLicense::deactivate();
				if ($res === false)
					print_r(json_encode(array("status"=>0, "msg"=>"WP: Connection error, please try again later.")));
				else
					print_r(json_encode($res));
	
				die();
			} else if ($_POST['op'] == "deactivate_remote") {
				if ( empty($_POST['site_url']) || empty($_POST['asp_key']) ) {
					print_r(json_encode(array("status" => 0, "msg" => "Site url or purchase key was not specified.")));
				} else {
					if ( strpos($_POST['site_url'], "//") === false)
						$_POST['site_url'] = "//".$_POST['site_url'];
					$host = parse_url($_POST['site_url'], PHP_URL_HOST);
					if ( !empty($host) ) {
						$key = $this->preValidateKey( $_POST['asp_key'] );
						if ( $key === false ) {
							print_r(json_encode(array("status"=>0, "msg"=>"WP: Invalid key specified.")));
							die();
						}
						$res = EnvatoLicense::deactivateRemote( $key, $host);
						if ($res === false)
							print_r(json_encode(array("status"=>0, "msg"=>"WP: Connection error, please try again later.")));
						else
							print_r(json_encode($res));
					} else {
						print_r(json_encode(array("status"=>0, "msg"=>"Invalid URL." . $host)));
					}
				}
				die();
			}
			// We reached here, something is missing..
			print_r(json_encode(array("status"=>0, "msg"=>"WP: Missing information, please check the input fields.")));
		}
		die();
	}

	function preValidateKey( $key ) {
		$key = trim($key);
		if ( strlen($key)!=36 )
			return false;
		return $key;
	}

	function excessiveUsage(): bool {
		$usage = get_option("_asp_update_usage", array());
		$n_usage = array();

		// Leave only recent usages
		foreach ($usage as $u) {
			if ($u > (time() - 60))
				$n_usage[] = $u;
		}

		if ( count($n_usage) <= 10 ) {
			$n_usage[] = time();
			update_option("_asp_update_usage", $n_usage);
			return false;
		} else {
			return true;
		}
	}
}