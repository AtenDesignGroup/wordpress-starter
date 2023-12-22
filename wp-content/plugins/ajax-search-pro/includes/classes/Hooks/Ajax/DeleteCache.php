<?php
/** @noinspection PhpMissingParamTypeInspection */
/** @noinspection PhpMissingReturnTypeInspection */

namespace WPDRMS\ASP\Hooks\Ajax;

use WPDRMS\ASP\Cache\TextCache;
use WPDRMS\ASP\Utils\Ajax;
use WPDRMS\ASP\Utils\FileManager;

if (!defined('ABSPATH')) die('-1');


class DeleteCache extends AbstractAjax {
	/**
	 * Deletes the Ajax Search Pro directory
	 */
	public function handle( $exit = true ) {
		// $exit can be an empty string "", so force boolean
		$exit = $exit !== false ? true : false;
		if ( 
			current_user_can( 'administrator' ) && 
			( !$exit || (
				isset($_POST['asp_delete_cache_request_nonce']) &&
				wp_verify_nonce( $_POST['asp_delete_cache_request_nonce'], 'asp_delete_cache_request_nonce' )
			) )
		) {
			
			if ( !empty(wd_asp()->cache_path) && wd_asp()->cache_path !== '' )
				$count = FileManager::_o()->deleteByPattern(wd_asp()->cache_path, '*.wpd');
			if ( !empty(wd_asp()->bfi_path) && wd_asp()->bfi_path !== '' ) {
				$count = $count +  FileManager::_o()->deleteByPattern(wd_asp()->bfi_path, '*.jpg');
				$count = $count +  FileManager::_o()->deleteByPattern(wd_asp()->bfi_path, '*.jpeg');
				$count = $count +  FileManager::_o()->deleteByPattern(wd_asp()->bfi_path, '*.png');
			}
	
			// Clear database cache
			$count = $count + TextCache::clearDBCache();
		}

		if ( $exit !== false ) {
			Ajax::prepareHeaders();
			print $count;
			die();
		}
	}
}