<?php
namespace WPDRMS\ASP\Utils;

defined('ABSPATH') or die("You can't access this file directly.");

class Taxonomy {
	/**
	 * Gets a list of taxonomy terms, separated by a comma (or as defined)
	 *
	 * @param $taxonomy
	 * @param int $count
	 * @param string $separator
	 * @param array $args arguments passed to get_terms() or wp_get_post_terms() functions
	 * @return string
	 */
	public static function getTermsList($taxonomy, int $count = 5, string $separator = ', ', array $args = array()): string {
		// Additional keys
		$args = array_merge($args, array(
			'taxonomy' => $taxonomy,
			'fields' => 'names',
			'number' => $count
		));
		$terms = wpd_get_terms($args);
		if ( !is_wp_error($terms) && !empty($terms) ) {
			return implode($separator, $terms);
		} else {
			return '';
		}
	}
}