<?php
namespace WPDRMS\ASP\Cache;

/* Prevent direct access */

use WPDRMS\ASP\Utils\FileManager;

defined('ABSPATH') or die("You can't access this file directly.");

class TextCache {

	private $interval;
	private $cache_name;
	private $cache_path;
	private $last_file_mtime = 0;
	private $last_file_path = "";
	private static $unique_db_prefix = '_aspdbcache_';

	public function __construct($cache_path, $cache_name = "txt", $interval = 36000) {
		$this->cache_name = $cache_name;
		$this->cache_path = $cache_path;
		$this->interval = $interval;
	}

	public function getCache($file = "") {
		$file = $this->filePath($file);
		$this->last_file_path = $file;

		if ( FileManager::_o()->isFile($file) ) {
			$filetime = FileManager::_o()->mtime($file);
		} else {
			return false;
		}
		if ( $filetime === false || (time() - $filetime) > $this->interval )
			return false;
		$this->last_file_mtime = $filetime;
		return FileManager::_o()->read($file);
	}

	public function getDBCache($handle) {
		$this->last_file_path = $handle;
		$data = get_option(self::$unique_db_prefix . $handle, '');
		if ( isset($data['time'], $data['content']) && (time() - $data['time']) <= $this->interval ) {
			$this->last_file_mtime = $data['time'];
			return $data['content'];
		} else {
			return false;
		}
	}

	public function getLastFileMtime(): int {
		return $this->last_file_mtime;
	}

	public function setCache($content, $file = "") {
		if ( $file === '' ) {
			$file = $this->last_file_path;
			if ( $file === '' )
				return;
		} else {
			$file = $this->filePath($file);
		}


		if ( FileManager::_o()->isFile($file) ) {
			$filetime = FileManager::_o()->mtime($file);
		} else {
			$filetime = 0;
		}

		if ( (time() - $filetime) > $this->interval ) {
			FileManager::_o()->write($file, $content);
		}
	}

	public function setDBCache($content, $handle = '') {
		if ( $handle === '' ) {
			$handle = $this->last_file_path;
			if ( $handle === '' )
				return;
		}

		$data = get_option(self::$unique_db_prefix . $handle, '');
		if ( isset($data['time']) ) {
			if ( (time() - $data['time']) > $this->interval ) {
				update_option(self::$unique_db_prefix . $handle, array(
					'time' => time(),
					'content' => $content
				));
			}
		} else {
			update_option(self::$unique_db_prefix . $handle, array(
				'time' => time(),
				'content' => $content
			));
		}
	}

	public static function generateSCFiles() {
		$content = FileManager::_o()->read(ASP_PATH . "sc-config.php");
		if ( $content !== '' ) {
			$content = preg_replace('/ASP_SC_CACHE_INTERVAL = (.*?);/', 'ASP_SC_CACHE_INTERVAL = ' . wd_asp()->o['asp_caching']['cachinginterval'] . ';', $content);
			$content = preg_replace('/ASP_SC_CACHE_PATH = (.*?);/', "ASP_SC_CACHE_PATH = '" . wd_asp()->cache_path . "';", $content);
			$content = preg_replace('/ASP_SC_ADMIN_AJAX_PATH = (.*?);/', "ASP_SC_ADMIN_AJAX_PATH = '" . ABSPATH . 'wp-admin/admin-ajax.php' . "';", $content);
			FileManager::_o()->write(ASP_PATH . "sc-config.php", $content);
		}
	}

	public static function clearDBCache(): int {
		global $wpdb;
		$query = $wpdb->prepare("DELETE FROM $wpdb->options WHERE option_name LIKE '%s'", self::$unique_db_prefix . '%');
		$res = $wpdb->query($query);
		if ( !is_wp_error($res) ) {
			return intval($res);
		} else {
			return 0;
		}
	}

	private function filePath($file): string {
		return trailingslashit($this->cache_path) . $this->cache_name . "_" . $file . ".wpd";
	}
}