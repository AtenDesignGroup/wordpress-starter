<?php
/* @noinspection PhpComposerExtensionStubsInspection */
/* @noinspection RegExpRedundantEscape */
namespace WPDRMS\ASP\Utils;

defined('ABSPATH') or die("You can't access this file directly.");

if ( !class_exists(__NAMESPACE__ . '\Str') ) {
	class Str {
		/**
		 * Performs a safe sanitation and escape for strings and numeric values in LIKE type queries.
		 * This is not to be used on whole queries, only values.
		 *
		 * @param $string
		 * @param bool $remove_quotes
		 * @param string $remove
		 * @return array|mixed
		 *@uses wd_mysql_escape_mimic()
		 */
		public static function escape($string, bool $remove_quotes = false, string $remove = '' ) {

			// recursively go through if it is an array
			if ( is_array($string) ) {
				foreach ($string as $k => $v) {
					$string[$k] = self::escape($v, $remove_quotes , $remove);
				}
				return $string;
			}

			if ( is_float( $string ) )
				return $string + 0;

			if ( $remove_quotes ) {
				$string = str_replace(
					array(chr(145), chr(146), chr(147), chr(148), chr(150), chr(151),
						chr(133), "'", '"'
					),
					'', $string);
			}
			if ( !empty($remove) ) {
				$string = str_replace(str_split($remove), '', $string);
			}

			if ( function_exists( 'esc_sql' ) )
				return esc_sql( $string );

			// Okay, what? Not one function is present, use the one we have
			return wd_mysql_escape_mimic($string);
		}

		/**
		 * Checks if the given date matches the pattern
		 *
		 * @param $date
		 * @return bool
		 */
		public static function checkDate( $date ): bool {
			if ( MB::strlen( $date ) != 10 ) return false;

			return preg_match('/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}\z/', $date);
		}

		/**
		 * Converts a string to number, array of strings to array of numbers
		 *
		 * Since esc_like() does not escape numeric values, casting them is the easiest way to go
		 *
		 */
		public static function forceNumeric( $number ) {
			if ( is_array($number) ) {
				foreach ($number as $k => $v) {
					$number[$k] = self::forceNumeric($v);
				}
				return $number;
			} else {
				// Replace any non-numeric and decimal point character
				$number = preg_replace("/[^0-9\.\-]+/", "", $number);
				// PHP 7.x+ 'feature' - empty string, or non-numeric value throws an error
				// ...in this case it is either a number or an empty string at this point, so check for empty string
				$number = $number == '' ? 0 : $number; // Check explanation above
				// Still something fishy?
				$number = !is_numeric($number) ?
					strpos($number, '.') !== false ? floatval($number) : intval($number) :
					$number;
				$number = $number + 0;
			}

			return $number;
		}

		/**
		 * Generates a string reverse, support multibite strings, plus fallback if mbstring not avail
		 *
		 * @param $string
		 * @return string
		 */
		public static function reverse( $string ): string {
			/*
			 * Not sure if using extension_loaded(...) is enough.
			 */
			if (
				function_exists('mb_detect_encoding') &&
				function_exists('mb_strlen') &&
				function_exists('mb_substr')
			) {
				// Using mbstring
				$encoding = mb_detect_encoding($string);
				$length   = mb_strlen($string, $encoding);
				$reversed = '';
				while ($length-- > 0) {
					$reversed .= mb_substr($string, $length, 1, $encoding);
				}

				return $reversed;

			} else {
				// Good old regex method, still supporting fully UFT8
				preg_match_all('/./us', $string, $ar);
				return implode(array_reverse($ar[0]));
			}

		}

		public static function anyToString($any, $level = 0): string {
			$str = "";
			if ( is_array( $any ) ) {
				foreach ( $any as $sub_arr ) {
					$str .= self::anyToString( $sub_arr, $level + 1 );
				}
			} else {
				// Check for objects, as those yield a fatal error when converted to strings
				if ( !is_object($any) ) {
					$str = (string) $any;
				}
			}

			return $str;
		}

		/**
		 * Replaces the first occurrence of the $find string with $replace within the $subject.
		 *
		 * @param string $find
		 * @param string $replace
		 * @param $subject
		 * @return string
		 *@since 4.11
		 *
		 */
		public static function replaceFirst(string $find, string $replace, $subject): string {
			// From the comments at PHP.net/str_replace
			// Splits $subject into an array of 2 items by $find,
			// and then joins the array with $replace
			return implode($replace, explode($find, $subject, 2));
		}

		/** @noinspection HttpUrlsUsage */
		public static function fixSSLURLs($string) {
			if ( ASP_SITE_IS_PROBABLY_SSL ) {
				return str_replace('http://', 'https://', $string);
			}
			return $string;
		}

		/**
		 * Clears and trims a search phrase from extra slashes and extra space characters
		 *
		 * @param $s
		 * @return array|string|string[]|null
		 */
		public static function clear($s) {
			return preg_replace( '/\s+/', ' ', trim(stripcslashes($s)) );
		}

		public static function isJson($str): bool {
			$json = json_decode($str);
			return $json && $str != $json;
		}

		/**
		 * Removes given tags and it's contents from a text
		 *
		 * @param string|array $text
		 * @param $tags
		 * @return string
		 * @noinspection PhpMissingParamTypeInspection
		 */
		public static function stripTagsWithContent($text, $tags = array()) {
			if ( !is_array($tags) ) {
				$tags = str_replace(',', ' ', $tags);
				$tags = preg_replace('/\s+/', ' ',$tags);
				$tags = explode(' ', $tags);
			}
			foreach ($tags as $tag) {
				$text = preg_replace('/<'.$tag.'>(.*?)<\/'.$tag.'>/s', '', $text);
				$text = preg_replace("/<\\/?" . $tag . "(.|\\s)*?>/", '', $text);
				// New line is required to split non-blank lines
				$text = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $text);
			}
			return $text;
		}

		/**
		 * Removes gutemberg blocks from the given string by block names array
		 *
		 * @param $content
		 * @param string[] $block_names
		 * @return string
		 */
		public static function removeGutenbergBlocks($content, array $block_names = array('core-embed/*')): string {
			foreach ( $block_names as &$block_name ) {
				$block_name = str_replace('*', 'AAAAAAAAAAAAAAA', $block_name);
				$block_name =  preg_quote($block_name);
				$block_name = str_replace('AAAAAAAAAAAAAAA', '.*?', $block_name);
			}
			$block_names =  implode('|', $block_names);

			// preg_quote does not escape '/' by default - use the '~' or '#' as the regex delimiter
			return preg_replace(
				"~<!--\s+wp:($block_names)(\s+\{(.*?)\})?\s+-->(.*?)<!--\s+\/wp:($block_names)\s+-->~s",
				'',
				$content
			);
		}

		/**
		 * Resolves content with bracket syntax (such as no results fields etc...)
		 *
		 * @param $content
		 * @param array $fields fieldN=>contentN keypairs for replacements
		 * @param bool $empty_on_missing if any of the field contents is empty, leave the whole expression empty
		 * @return string
		 */
		public static function resolveBracketSyntax($content, array $fields = array(), bool $empty_on_missing = false ): string {

			if ( empty($fields) )
				return $content;

			// Find conditional patterns, like [prefix {field} suffix]
			preg_match_all( "/(\[.*?\])/", $content, $matches );
			if ( isset( $matches[0] ) && isset( $matches[1] ) && is_array( $matches[1] ) ) {
				foreach ( $matches[1] as $fieldset ) {
					// Pass on each section to this function again, the code will never get here
					$stripped_fieldset = str_replace(array('[', ']'), '', $fieldset);
					$processed_content = self::resolveBracketSyntax($stripped_fieldset, $fields, true);

					// Replace the original with the processed version, first occurrence, in case of duplicates
					$content = self::replaceFirst($fieldset, $processed_content, $content);
				}
			}

			preg_match_all( "/{(.*?)}/", $content, $matches );
			if ( isset( $matches[0] ) && isset( $matches[1] ) && is_array( $matches[1] ) ) {
				foreach ( $matches[1] as $field ) {
					$val = $fields[$field] ?? '';
					// For the recursive call to break, if any of the fields is empty
					if ( $empty_on_missing && $val == '')
						return '';
					$content = str_replace( '{' . $field . '}', $val, $content );
				}
			}

			return $content;
		}
	}
}