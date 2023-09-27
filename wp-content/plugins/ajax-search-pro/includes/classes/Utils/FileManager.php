<?php

namespace WPDRMS\ASP\Utils;
use InvalidArgumentException;
use WPDRMS\ASP\Patterns\SingletonTrait;

class FileManager {
	use SingletonTrait;

    function initialized(bool $include = false, string $check_method = ''): bool {
        global $wp_filesystem;
        if ( $include && empty($wp_filesystem) ) {
            require_once (ABSPATH . '/wp-admin/includes/file.php');
        }
        if ( function_exists('WP_Filesystem') && WP_Filesystem() === true && is_object($wp_filesystem) ) {
            if ( $check_method != '' ) {
                return method_exists($wp_filesystem, $check_method);
            }
            return true;
        }
        // Did not init
        return false;
    }

    function wrapper(string $function) {
        global $wp_filesystem;
        $args = func_get_args();
        array_shift($args);
        return $this->initialized(false, $function) ?
            call_user_func_array(array($wp_filesystem, $function), $args) :
            call_user_func_array($function, $args);
    }

    function mtime($file) {
        global $wp_filesystem;
        // Did it fail?
        if ( $this->initialized(false, 'mtime') ) {
            return $wp_filesystem->mtime($file);
        }
        return filemtime( $file );
    }

    function isFile($file) {
        return $this->wrapper('is_file', $file);
    }

    function isDir($file) {
        return $this->wrapper('is_dir', $file);
    }

    function read($filename) {
        global $wp_filesystem;
        // Replace double
        $filename = str_replace(array('\\\\', '//'), array('\\', '/'), $filename);

        if ( !file_exists($filename) )
            return '';

        if ( $this->initialized(false, 'get_contents') ) {
            // All went well, return
            return $wp_filesystem->get_contents( $filename );
        }

        return @file_get_contents($filename);
    }

    function write(string $filename, string $contents): bool {
        global $wp_filesystem;
        // Replace double
        $filename = str_replace(array('\\\\', '//'), array('\\', '/'), $filename);
		$dir = $this->inAllowedDirectory($filename);

		if ( $dir !== false ) {
			// Make sure that the directory exists
			$this->createRequiredDirectories( array($dir) );

			// Did it fail?
			if ( !$this->initialized(false, 'put_contents') ) {
				/* any problems and we exit */
				return !(@file_put_contents($filename, $contents) === false);
			}

			// It worked, use it!
			if ( defined('FS_CHMOD_FILE') ) {
				if ( !$wp_filesystem->put_contents($filename, $contents, FS_CHMOD_FILE) ) {
					return !(@file_put_contents($filename, $contents) === false);
				}
			} else {
				if ( !$wp_filesystem->put_contents($filename, $contents) ) {
					return !(@file_put_contents($filename, $contents) === false);
				}
			}
		}

        return true;
    }

    function delFile($filename) {
        global $wp_filesystem;
		if ( $this->inAllowedDirectory($filename) ) {
			// Did it fail?
			if ( !$this->initialized(false, 'delete') ) {
				/* any problems and we exit */
				return @unlink($filename);
			}
			return $wp_filesystem->delete($filename);
		} else {
			return false;
		}
    }

	/**
	 * Delete files in directory according to a pattern
	 *
	 * @param $dir string
	 * @param $file_arg string
	 * @return int files and directories deleted
	 */
	public function deleteByPattern(string $dir, string $file_arg = '*.*'): int {
		if ( $dir != '' && $dir != '/' ) {
			$count = 0;
			$files = @glob($dir . $file_arg, GLOB_MARK);
			// Glob can return FALSE on error
			if ( is_array($files) ) {
				foreach ($files as $file) {
					$this->delFile($file);
					$count++;
				}
			}
			return $count;
		}
		return 0;
	}

    function rmdir(string $dir, bool $recursive = false, bool $force = false): bool {
        global $wp_filesystem;
		if ( $this->inAllowedDirectory($dir) ) {
			if ( $force ) {
				$this->recursiveRmdir($dir);
				return true;
			}

			// Did it fail?
			if ( !$this->initialized(false, 'rmdir') ) {
				// $recursive is not supported in the default php rmdir function
				return rmdir($dir);
			}

			$wp_filesystem->rmdir($dir, $recursive);
		} else {
			return false;
		}

		return false;
    }

    function recursiveRmdir( string $dirPath ) {
        if ( !is_dir($dirPath) ) {
            throw new InvalidArgumentException("$dirPath must be a directory");
        }
        if ( !str_ends_with($dirPath, '/') ) {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if ( is_dir($file) ) {
                $this->recursiveRmdir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }

	function createRequiredDirectories( $directories = false ): bool {
		global $wp_filesystem;
		$directories = $directories === false ? array(wd_asp()->upload_path, wd_asp()->cache_path) : $directories;

		if ( $this->initialized(true, 'is_dir') ) {
			foreach ( $directories as $directory ) {
				if ( !$wp_filesystem->is_dir( $directory ) ) {
					if ( !$wp_filesystem->mkdir( $directory, 0755 ) ) {
						@mkdir( $directory, '0777', true );
					}
					if ( $wp_filesystem->is_dir( $directory ) && !@chmod($directory, 0755) ) {
						if ( !@chmod($directory, 0664 ) ){
							@chmod($directory, 0644 );
						}
					}
				}
			}
		} else {
			foreach ( $directories as $directory ) {
				if ( !is_dir($directory) ) {
					if ( !@mkdir($directory, '0777', true) ) {
						return false;
					} else {
						if ( !@chmod($directory, 0755) ) {
							if ( !@chmod($directory, 0664) ) {
								@chmod($directory, 0644);
							}
						}
					}
				}
			}
		}

		return true;
	}

	function removeRequiredDirectories() {
		$directories = array(wd_asp()->upload_path, wd_asp()->cache_path);
		foreach ( $directories as $directory ) {
			if ( $this->pathSafetyCheck($directory) && $this->isDir($directory) ) {
				$this->rmdir( $directory  );
				if ( $this->isDir( $directory ) ) {
					$this->rmdir( $directory, true);
					if ( $this->isDir( $directory ) ) {
						// Last attempt, with force
						$this->rmdir( $directory, true, true);
					}
				}
			}
		}
	}

	private function pathSafetyCheck($path): bool {
		if (
			isset($path) &&
			$path != '' &&
			$path != '/' &&
			$path != './' &&
			str_replace('/', '', get_home_path()) != str_replace('/', '', $path) &&
			strpos($path, 'wp-content') > 5 &&
			strpos($path, 'plugins') === false &&
			strpos($path, 'wp-includes') === false &&
			strpos($path, 'wp-admin') === false &&
			is_dir( $path )
		) {
			return true;
		}

		return false;
	}

	private function inAllowedDirectory($path) {
		if ( strpos($path, wd_asp()->upload_path) !== false ) {
			return wd_asp()->upload_path;
		} else if ( strpos($path, wd_asp()->cache_path) !== false ) {
			return wd_asp()->cache_path;
		}

		return false;
	}
}