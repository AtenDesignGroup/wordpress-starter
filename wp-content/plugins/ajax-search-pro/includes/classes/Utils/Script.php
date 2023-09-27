<?php
namespace WPDRMS\ASP\Utils;

defined('ABSPATH') or die("You can't access this file directly.");

if ( !class_exists(__NAMESPACE__ . '\Script') ) {
	class Script {
		public static function objectToInlineScript($handle, $object_name, $data, $position = 'before', $safe_mode = false, $print = true) {
			// Taken from WP_Srcripts -> localize
			foreach ( (array) $data as $key => $value ) {
				if ( is_string($value) ) {
					$data[$key] = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
				}
			}
			if ( $safe_mode ) {
				// If the inline script was merged or moved by a minify, the object may already exist, so keep the properties
				$script = "if ( typeof window.$object_name == 'undefined') { window.$object_name = {";
				$atts = array();
				foreach ( (array) $data as $key => $value ) {
					if ( is_numeric($value) ) {
						$atts[] = "$key: $value";
					} else if ( is_bool($value) ) {
						if ( $value ) {
							$atts[] = "$key: true";
						} else {
							$atts[] = "$key: false";
						}
					} else {
						$atts[] = "$key: " . wp_json_encode($value);
					}
				}
				$script .= implode(', ', $atts);
				$script .= "}};";
			} else {
				$script = "window.$object_name = " . wp_json_encode( $data ) . ';';
			}
			if ( $print ) {
				wp_add_inline_script($handle, $script, $position);
			}
			return "<script type='text/javascript' id='".$handle."-js-".$position."'>" . $script . "</script>";
		}
	}
}