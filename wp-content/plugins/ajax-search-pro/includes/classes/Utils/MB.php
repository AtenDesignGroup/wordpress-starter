<?php
namespace WPDRMS\ASP\Utils;

defined('ABSPATH') or die("You can't access this file directly.");

if ( !class_exists(__NAMESPACE__ . '\MB') ) {
	class MB {
		public static function strlen( ) {
			$args = func_get_args();
			if ( function_exists("mb_strlen") )
				return call_user_func_array("mb_strlen", $args);
			else
				return call_user_func_array("strlen", $args);
		}

		public static function strpos( ) {
			$args = func_get_args();
			if ( function_exists("mb_strpos") )
				return call_user_func_array("mb_strpos", $args);
			else
				return call_user_func_array("strpos", $args);
		}

		public static function substr( ) {
			$args = func_get_args();
			if ( function_exists("mb_substr") )
				return call_user_func_array("mb_substr", $args);
			else
				return call_user_func_array("substr", $args);
		}

		public static function strtolower( ) {
			$args = func_get_args();
			if ( function_exists("mb_strtolower") )
				return call_user_func_array("mb_strtolower", $args);
			else
				return call_user_func_array("strtolower", $args);
		}

		public static function convert_case( ) {
			$args = func_get_args();
			if ( function_exists("mb_convert_case") )
				return call_user_func_array("mb_convert_case", $args);
			else
				return $args[0] ?? '';
		}

		public static function strrev($string, $encoding = null): string {
			if (
				function_exists('mb_detect_encoding') &&
				function_exists('mb_strlen') &&
				function_exists('mb_substr')
			) {
				if ($encoding === null) {
					$encoding = mb_detect_encoding($string);
				}

				$length   = mb_strlen($string, $encoding);
				$reversed = '';
				while ($length-- > 0) {
					$reversed .= mb_substr($string, $length, 1, $encoding);
				}
				return $reversed;
			} else {
				return strrev($string);
			}
		}

		public static function trim($string, $charlist = null) {
			if (is_null($charlist)) {
				return trim ($string);
			}

			$charlist = str_replace ('/', '\/', preg_quote($charlist));
			return preg_replace ("/(^[$charlist]+)|([$charlist]+$)/us", '', $string);
		}

		public static function strcasecmp($str1, $str2, $encoding = null): int {
			if (null === $encoding) {
				$encoding = mb_internal_encoding();
			}
			return strcmp(
				mb_strtoupper(remove_accents($str1), $encoding),
				mb_strtoupper(remove_accents($str2), $encoding)
			);
		}
	}
}