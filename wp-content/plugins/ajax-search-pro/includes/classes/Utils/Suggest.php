<?php
namespace WPDRMS\ASP\Utils;

defined('ABSPATH') or die("You can't access this file directly.");

if ( !class_exists(__NAMESPACE__ . '\Suggest') ) {
	class Suggest {
		/**
		 * Returns a list of keywords most similar from $source_arr array to $string string
		 *
		 * @param array $source_arr
		 * @param string $string
		 * @param int $count
		 * @param int $threshold
		 * @param bool $lowercase
		 * @return array
		 */
		public static function getSimilarText(array $source_arr, string $string, int $count = 1, int $threshold = 34, bool $lowercase = true): array {
			$matches = array();
			if ( count($source_arr) > 0 && $string != '' ) {
				$count = $count > 100 ? 100 : $count;
				$i = 0;
				$source_arr = array_unique($source_arr);
				foreach ( $source_arr as $word ) {
					// Tested with an array of 500 000 items, ~1 sec execution time
					similar_text(MB::strtolower($word), MB::strtolower($string), $perc);
					$matches[] = array(
						'r' => $perc,
						'w' => $word
					);
					++$i;
					if ( ($i % 100) == 0 ) {
						$keys = array_column($matches, 'r');
						array_multisort ($keys, SORT_DESC, $matches);
						$matches = array_slice($matches, 0, $count);
					}
				}
				$keys = array_column($matches, 'r');
				array_multisort ($keys, SORT_DESC, $matches);
				$matches = array_slice($matches, 0, $count);
				foreach ( $matches as $k => &$match ) {
					if ( $match['r'] < $threshold ) {
						unset($matches[$k]);
					} else if ($lowercase) {
						$match['w'] = MB::strtolower($match['w']);
					}
				}
				return array_column($matches, 'w');
			} else {
				return $matches;
			}
		}
	}
}