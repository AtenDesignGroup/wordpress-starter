<?php
namespace WPDRMS\ASP\Asset\Script;

defined('ABSPATH') or die("You can't access this file directly.");

if ( !class_exists(__NAMESPACE__ . '\DevConfig') ) {
	class DevConfig {
		private $dev_scripts = array();
		private $dev_scripts_config = 'js/src/config.cfg';

		function __construct() {
			$blocks = $this->process_config(ASP_PATH . $this->dev_scripts_config);
			foreach($blocks as $handle => $block) {
				$this->dev_scripts[$handle] = array(
					'src' => $this->get_block_files_ordered($block)
				);
			}
		}

		function getDevScripts(): array {
			return $this->dev_scripts;
		}

		private function process_config($cfg) {
			$blocks = $all_blocks = parse_ini_file( $cfg, true);

			foreach ( $blocks as $name => $block ) {
				if ( isset($block['config']) ) {
					foreach ( $block['config'] as $config ) {
						$all_blocks = array_merge($all_blocks, $this->process_config( $config ));
					}
					unset($all_blocks[$name]);
				} else {
					if ( !isset($block['input_dir']) ) {
						$all_blocks[$name]['input_dir'] = dirname($cfg) . "/";
					}
				}
			}

			return $all_blocks;
		}

		private function get_block_files_ordered($block): array {
			$extensions = array('js');
			$parsed = array();
			$input_dir = $block['input_dir'] ?? $_SERVER['DOCUMENT_ROOT'] . '/';
			$exclude = $block['exclude'] ?? array();
			foreach ( $exclude as &$e ) {
				$e = $input_dir . $e;
			}
			foreach ( $block['input'] as $input ) {
				$path = $input_dir . $input;
				if ( is_dir($path) ) {
					$dir = array_diff(scandir($path),  array('..', '.'));
					foreach ( $dir as $file ) {
						$filepath = $path . '/' . $file;

						if ( is_dir($filepath) ) {
							continue;
						}

						if (
							in_array(pathinfo($filepath)['extension'], $extensions) &&
							!in_array($filepath, $exclude) &&
							!in_array($filepath, $parsed)
						) {
							$parsed[] = $filepath;
						}
					}
				} else {
					if ( !in_array($path, $parsed) ) {
						$parsed[] = $path;
					}
				}
			}

			return $parsed;
		}
	}
}