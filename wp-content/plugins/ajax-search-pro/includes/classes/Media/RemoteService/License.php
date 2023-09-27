<?php
namespace WPDRMS\ASP\Media\RemoteService;

use WPDRMS\ASP\Patterns\SingletonTrait;

if (!defined('ABSPATH')) die('-1');

class License {
	use SingletonTrait;

	private $data, $fp_license_pk = "d19SA)r4_o21:s91e8AS_dm,aks7=12--f4812-,d,da102a8sdAS";
	private $url = "https://fastspring.wp-dreams.com/";

	private function __construct() {
		$this->data = get_option('_asp_media_service_data', array(
			'license' => false,
			'active' => false,
			'stats' => array(
				/**
				 * free => false,
				 * ends => (timestamp)
				 * max_files_usage => (int)
				 * max_files => (int)
				 * max_filesize => (int) [in MB]
				 */
			)
		));
		$this->refresh();
	}

	function active() {
		return $this->data['license'] !== false && $this->data['active'];
	}

	function valid() {
		if (
			(int)$this->data['stats']['max_files_usage'] >= (int)$this->data['stats']['max_files']
		) {
			/**
			 * The "stats" are updated ONLY during indexing. If the max_file threshold was met during a recent
			 * index, then max_files < max_files_usage forever, and this function would return "false" all the time.
			 * If the last check was performed over 5 minutes ago, the report "true" even if the files
			 * threshold was met, so a request will be made to the media server to verify that.
			 */
			if ( ( time() - (int)$this->data['last_check'] ) > 300 ) {
				return true;
			} else {
				return false;
			}
		} else {
			return true;
		}
	}

	function refresh() {
		if ( $this->active() ) {
			if ( ( time() - (int)$this->data['last_check'] ) > 300 ) {
				$this->activate($this->data['license']);
			}
		}
	}

	function activate( $license ) {
		$success = 0;
		if (
			strlen($license) == 36 ||
			preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $license) === 1
		) {
			$response = wp_safe_remote_post(
				$this->url,
				array(
					'body' => array(
						'license' => $license,
						'hash' =>  base64_encode(hash_hmac('sha256', $license, $this->fp_license_pk, true))
					)
				)
			);
			if ( !is_wp_error($response) ) {
				$data = json_decode($response['body'], true);
				if ( !$data['success'] ) {
					$text = $data['text'];
				} else {
					$this->set($license, 1, $data['stats']);
					$success = 1;
					$text = "License successfully activated!";
				}
			} else {
				$text = $response->get_error_message();
			}
		} else {
			$text = __("Invalid license key length or missing characters. Please make sure to copy the 36 character license key here.", 'ajax-search-pro');
		}

		return array(
			"success" => $success,
			"text" => $text
		);
	}

	function deactivate() {
		$this->data['active'] = false;
		update_option('_asp_media_service_data', $this->data);
	}

	function delete() {
		delete_option('_asp_media_service_data');
	}

	function get() {
		return $this->data['license'] !== false ? $this->data['license'] : '';
	}

	function getData() {
		return $this->data;
	}

	function set($license, $active, $stats) {
		$this->data = array(
			'license' => $license,
			'active' => $active,
			'last_check' => time(),
			'stats' => $stats
		);
		update_option('_asp_media_service_data', $this->data);
	}

	function setStats( $stats = false ) {
		if ( $stats !== false && count($stats) > 0 && $this->data['license'] !== false ) {
			$this->data['stats'] = $stats;
			update_option('_asp_media_service_data', array(
				'license' => $this->data['license'],
				'active' => $this->data['active'],
				'last_check' => time(),
				'stats' => $stats
			));
		}
	}
}