<?php
namespace WPDRMS\ASP\Asset\Css;

use WPDRMS\ASP\Asset\GeneratorInterface;
use WPDRMS\ASP\Asset\Script\Requirements;
use WPDRMS\ASP\Utils\FileManager;
use WPDRMS\ASP\Utils\Str;

defined('ABSPATH') or die("You can't access this file directly.");

if ( !class_exists(__NAMESPACE__ . '\Generator') ) {
	class Generator implements GeneratorInterface {
		private
			$basic_flags_string = '',
			$minify;

		function __construct( $minify = false ) {
			$this->minify = $minify;
		}

		function generate(): string {
			if ( wd_asp()->instances->exists() ) {
				$basic_css = $this->generateBasic();
				$instance_css_arr = $this->generateInstances();

				return $this->saveFiles($basic_css, $instance_css_arr);
			}
			return '';
		}

		function verifyFiles(): bool {
			if (
				!file_exists(wd_asp()->cache_path . $this->filename('basic')) ||
				!file_exists(wd_asp()->cache_path . $this->filename('instances')) ||
				@filesize(wd_asp()->cache_path . $this->filename('instances')) < 1025
			) {
				return false;
			} else {
				return true;
			}
		}

		function filename( $handle ) {
			$media_flags = get_site_option('asp_css_flags', array(
				'basic' => ''
			));
			$flag = Str::anyToString($media_flags['basic']);
			$files = array(
				'basic' => 'style.basic'.$flag.'.css',
				'wpdreams-asp-basics' => 'style.basic'.$flag.'.css',
				'instances' => 'style.instances'.$flag.'.css',
				'wpdreams-ajaxsearchpro-instances' => 'style.instances'.$flag.'.css'
			);
			return $files[$handle] ?? 'search' . $handle . '.css';
		}

		private function generateBasic() {
			// Basic CSS
			ob_start();
			include(ASP_PATH . "/css/style.basic.css.php");
			$basic_css = ob_get_clean();
			$unused_assets = Requirements::getUnusedAssets(false);
			foreach ( $unused_assets['internal'] as $flag ) {
				// Remove unneccessary CSS
				$basic_css = asp_get_outer_substring($basic_css, '/*[' . $flag . ']*/');
				$this->basic_flags_string .= '-' . substr($flag, 0, 2);
			}
			foreach ( $unused_assets['external'] as $flag ) {
				// Remove unneccessary CSS
				$basic_css = asp_get_outer_substring($basic_css, '/*[' . $flag . ']*/');
				$this->basic_flags_string .= '-' . substr($flag, 0, 2);
			}

			return $basic_css;
		}

		private function generateInstances(): array {
			// Instances CSS
			$css_arr = array();
			foreach (wd_asp()->instances->get() as $s) {
				// $style and $id needed in the include
				$style = &$s['data'];
				$id = $s['id'];
				ob_start();
				include(ASP_PATH . "/css/style.css.php");
				$out = ob_get_contents();
				$css_arr[$id] = $out;
				ob_end_clean();
			}
			return $css_arr;
		}

		private function minify($css) {
			// Normalize whitespace
			$css = preg_replace( '/\s+/', ' ', $css );
			// Remove spaces before and after comment
			$css = preg_replace( '/(\s+)(\/\*(.*?)\*\/)(\s+)/', '$2', $css );
			// Remove comment blocks, everything between /* and */, unless
			// preserved with /*! ... */ or /** ... */
			$css = preg_replace( '~/\*(?![\!|\*])(.*?)\*/~', '', $css );
			// Remove space after , : ; { } */ >
			$css = preg_replace( '/(,|:|;|\{|}|\*\/|>) /', '$1', $css );
			// Remove space before , ; { } ( ) >
			$css = preg_replace( '/ (,|;|\{|}|\(|\)|>)/', '$1', $css );
			// Add back the space for media queries operator
			$css = preg_replace( '/and\(/', 'and (', $css );
			// Strips leading 0 on decimal values (converts 0.5px into .5px)
			$css = preg_replace( '/(:| )0\.([0-9]+)(%|em|ex|px|in|cm|mm|pt|pc)/i', '${1}.${2}${3}', $css );
			// Strips units if value is 0 (converts 0px to 0)
			$css = preg_replace( '/(:| )(\.?)0(%|em|ex|px|in|cm|mm|pt|pc)/i', '${1}0', $css );
			// Converts all zeros value into short-hand
			$css = preg_replace( '/0 0 0 0;/', '0;', $css );
			$css = preg_replace( '/0 0 0 0\}/', '0}', $css );
			// Invisible inset box shadow
			$css = preg_replace( '/box-shadow:0 0 0(?: 0)? [a-fA-F0-9()#,rgb]+(?: inset)?([};])/i', 'box-shadow:none${1}', $css );
			// Transparent box shadow
			$css = preg_replace( '/box-shadow:[0-9px ]+ (transparent inset|transparent)([};])/i', 'box-shadow:none${2}', $css );
			// Invisible text shadow
			$css = preg_replace( '/text-shadow:0 0(?: 0)? [a-fA-F0-9()#,rgb]+([};])/i', 'text-shadow:none${1}', $css );
			// Transparent text shadow
			$css = preg_replace( '/text-shadow:[0-9px ]+ transparent([};])/i', 'text-shadow:none${1}', $css );
			// Shorten 6-character hex color codes to 3-character where possible
			$css = preg_replace( '/#([a-f0-9])\\1([a-f0-9])\\2([a-f0-9])\\3/i', '#\1\2\3', $css );
			// Remove ; before }
			$css = preg_replace( '/;(?=\s*})/', '', $css );
			return trim( $css );
		}

		private function saveFiles($basic_css, $instance_css_arr): string {
			// Too big, disabled...
			$css = implode(" ", $instance_css_arr);

			// Individual CSS rules by search ID
			foreach ($instance_css_arr as $sid => &$c) {
				if ( $this->minify ) {
					$c = $this->minify($c);
				}
				FileManager::_o()->write(wd_asp()->cache_path . "search" . $sid . ".css", $c);
			}

			// Save the style instances file nevertheless, even if async enabled
			if ( $this->minify ) {
				$css = $this->minify($css);
				$basic_css = $this->minify($basic_css);
			}

			FileManager::_o()->write(wd_asp()->cache_path . "style.instances.css", $basic_css . $css);
			FileManager::_o()->write(wd_asp()->cache_path . "style.basic.css", $basic_css);
			if ( $this->basic_flags_string != '' ) {
				FileManager::_o()->write(wd_asp()->cache_path . "style.basic" . $this->basic_flags_string . ".css", $basic_css);
				FileManager::_o()->write(wd_asp()->cache_path . "style.instances" . $this->basic_flags_string . ".css", $basic_css . $css);
			}

			update_site_option('asp_css_flags', array(
				'basic' => $this->basic_flags_string
			));


			update_site_option("asp_media_query", asp_gen_rnd_str());

			update_site_option('asp_css', array(
				'basic' => $basic_css,
				'instances' => $instance_css_arr
			));

			return $basic_css . $css;
		}
	}
}