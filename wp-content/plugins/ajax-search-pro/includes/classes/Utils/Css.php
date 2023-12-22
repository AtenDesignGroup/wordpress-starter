<?php
namespace WPDRMS\ASP\Utils;

defined('ABSPATH') or die("You can't access this file directly.");

class Css {
	/**
	 * Helper method to be used before printing the font styles. Converts font families to apostrophed versions.
	 *
	 * @param $font
	 * @return mixed
	 */
	public static function font($font) {
		preg_match("/family:(.*?);/", $font, $fonts);
		if ( isset($fonts[1]) ) {
			$f = explode(',', str_replace(array('"', "'"), '', $fonts[1]));
			foreach ($f as &$_f) {
				if ( trim($_f) != 'inherit' )
					$_f = '"' . trim($_f) . '"';
				else
					$_f = trim($_f);
			}
			$f = implode(',', $f);
			$ret = preg_replace("/family:(.*?);/", 'family:'.$f.';', $font);
		} else {
			$ret = $font;
		}

		return apply_filters('asp_fonts_css', $ret);
	}
}