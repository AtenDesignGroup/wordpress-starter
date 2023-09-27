<?php
namespace WPDRMS\ASP\Utils;

defined('ABSPATH') or die("You can't access this file directly.");

class Ajax {
	/**
	 * Prepares the headers for the ajax request
	 *
	 * @param string $content_type
	 */
	public static function prepareHeaders(string $content_type = 'text/plain') {
		if ( !headers_sent() ) {
			header('Content-Type: ' . $content_type);
		}
	}
}