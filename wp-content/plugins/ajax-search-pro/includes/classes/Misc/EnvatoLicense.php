<?php /** @noinspection HttpUrlsUsage */

namespace WPDRMS\ASP\Misc;

use Exception;

if (!defined('ABSPATH')) die('-1');


class EnvatoLicense {

	static $url = "http://update.wp-dreams.com/a.php";

	static function activate( $license_key ) {
		$url = rawurlencode( $_SERVER['HTTP_HOST'] );
		$key = rawurlencode( $license_key );

		$url = self::$url . '?url=' . $url . '&key=' . $key . "&op=activate&p=asp";

		try {
			$response = @wp_remote_get( $url );
		} catch (Exception $e) {
			return false;
		}

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$data = json_decode( $response['body'], true );

		// something went wrong
		if ( empty($data) ) return false;

		if ( isset($data['status']) && $data['status'] == 1 )
			update_option("asp_update_data", array(
				"key"  => $license_key,
				"host" => $_SERVER['HTTP_HOST']
			));

		return $data;
	}

	static function deactivate( $remote_check = true ) {
		$data = false;

		if ( $remote_check )
			if (false !== ($key = self::isActivated())) {
				$url = rawurlencode( $_SERVER['HTTP_HOST'] );
				$key = rawurlencode( $key );

				$url = self::$url . '?url=' . $url . '&key=' . $key . "&op=deactivate";
				$response = wp_remote_get( $url );

				if ( is_wp_error( $response ) ) {
					return false;
				}
				$data = json_decode( $response['body'], true );
			}

		delete_option("asp_update_data");
		return $data;
	}

	static function deactivateRemote( $key, $url ) {
		$url = rawurlencode( $url );
		$key = rawurlencode( $key );

		$url = self::$url . '?url=' . $url . '&key=' . $key . "&op=deactivate";
		$response = wp_remote_get( $url );

		if ( is_wp_error( $response ) ) {
			return false;
		}
		return json_decode( $response['body'], true );
	}

	static function isActivated( $remote_check = false, $auto_local_deactivate = false ) {
		$data = get_option("asp_update_data");

		if ( $data === false || !isset($data['host']) || !isset($data['key']) ) return false;

		if ( $remote_check ) {
			$url = rawurlencode( $_SERVER['HTTP_HOST'] );
			$key = rawurlencode( $data['key'] );

			$url = self::$url . '?url=' . $url . '&key=' . $key . "&op=check";

			$response = wp_remote_get( $url );

			if ( is_wp_error( $response ) ) {
				return false;
			}

			$rdata = json_decode( $response['body'], true );

			$ret = $rdata['status'] == 1 ? $data['key'] : false;

			if ( $auto_local_deactivate && $ret == false ) {
				self::deactivate( false );
			}

			return $rdata['status'] == 1 ? $data['key'] : false;
		}

		return $data['key'];
	}

}