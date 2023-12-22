<?php
namespace WPDRMS\ASP\Asset\Font;

use WPDRMS\ASP\Asset\GeneratorInterface;

defined('ABSPATH') or die("You can't access this file directly.");

if ( !class_exists(__NAMESPACE__ . '\Generator') ) {
	class Generator implements GeneratorInterface {
		function generate(): array {
			$imports = array();
			$font_sources = array("inputfont", "descfont", "titlefont", 'fe_sb_font',
				"authorfont", "datefont", "showmorefont", "groupfont",
				"exsearchincategoriestextfont", "groupbytextfont", "settingsdropfont",
				"prestitlefont", "presdescfont", "pressubtitlefont", "search_text_font");

			foreach (wd_asp()->instances->get() as $instance) {
				foreach($font_sources as $fs) {
					if (
						isset($instance['data']["import-".$fs]) &&
						!in_array(trim($instance['data']["import-".$fs]), $imports)
					) {
						$imports[] = trim($instance['data']["import-" . $fs]);
					}
				}
			}

			foreach ( $imports as $ik => $im ) {
				if ( $im == '' ) {
					unset($imports[$ik]);
				}
			}

			$imports = apply_filters('asp_custom_fonts', $imports);
			$fonts = array();
			foreach ($imports as $import) {
				$import = trim(str_replace(array("@import url(", ");", "https:", "http:"), "", $import));
				$import = trim(str_replace("//fonts.googleapis.com/css?family=", "", $import));
				if ( $import != '' ) {
					$fonts[] = $import;
				}
			}

			return $fonts;
		}
	}
}