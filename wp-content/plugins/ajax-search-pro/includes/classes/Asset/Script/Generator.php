<?php
namespace WPDRMS\ASP\Asset\Script;

use WPDRMS\ASP\Asset\GeneratorInterface;
use WPDRMS\ASP\Utils\FileManager;

defined('ABSPATH') or die("You can't access this file directly.");

if ( !class_exists(__NAMESPACE__ . '\Generator') ) {
	class Generator implements GeneratorInterface {
		private
			$scripts;

		function __construct($scripts) {
			$this->scripts = $scripts;
		}

		function get() {
			if ( $this->verifyFiles() ) {
				return $this->filename();
			} else {
				$this->generate();
				if ( $this->verifyFiles() ) {
					return $this->filename();
				}
			}
			return '';
		}

		function generate(): bool {
			if ( !$this->verifyFiles() ) {
				$final_content = '';
				foreach ($this->scripts as $script) {
					if ( !isset($script['path']) ) {
						return false;
					} else {
						$content = file_get_contents($script['path']);
						if ( $content == '' ) {
							return false;
						} else {
							$final_content .= $content;
						}
					}
				}
				FileManager::_o()->write(wd_asp()->cache_path . $this->fileName(), $final_content);
				return $this->verifyFiles();
			}

			return true;
		}

		function verifyFiles(): bool {
			if (
				!file_exists(wd_asp()->cache_path . $this->filename()) ||
				@filesize(wd_asp()->cache_path . $this->filename()) < 1025
			) {
				return false;
			} else {
				return true;
			}
		}

		public static function deleteFiles(): int {
			if ( !empty(wd_asp()->cache_path) && wd_asp()->cache_path !== '' ) {
				return FileManager::_o()->deleteByPattern(wd_asp()->cache_path, '*.js');
			}
			return 0;
		}

		function fileName(): string {
			return $this->fileHandle() . '.js';
		}

		function fileHandle(): string {
			$concat = ASP_CURR_VER_STRING . ASP_CURR_VER;
			foreach ( $this->scripts  as $script ) {
				$concat .= $script['handle'];
			}
			return 'asp-' . substr(md5($concat), 0, 8);
		}
	}
}