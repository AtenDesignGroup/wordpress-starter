<?php
namespace WPDRMS\ASP;

class Autoloader {
	protected static $_instance;

	protected $aliases = array(
		'asp_indexTable' => 'WPDRMS\\ASP\\Index\\Manager',
		'ASP_Query' => 'WPDRMS\\ASP\\Query\\SearchQuery',
		//'ASP_Helpers' => 'WPDRMS\\ASP\\Utils\\Str'
	);

	private function __construct() {
		defined('ABSPATH') or die();

		spl_autoload_register(array(
			$this, 'loader'
		));
	}

	function loader( $class ) {

		// project-specific namespace prefix
		$prefix = 'WPDRMS\\ASP\\';

		// base directory for the namespace prefix
		$base_dir = ASP_CLASSES_PATH;

		// does the class use the namespace prefix?
		$len = strlen($prefix);

		if ( strncmp($prefix, $class, $len) !== 0 ) {
			// is this an alias?
			if ( isset($this->aliases[$class]) ) {
				if ( !class_exists($this->aliases[$class]) ) {
					$this->loader($this->aliases[$class]);
				}

				if ( class_exists($this->aliases[$class]) ) {
					/**
					 * Create class alias for old class names
					 */
					class_alias($this->aliases[$class], $class);
				}
			}
		} else {
			// get the relative class name
			$relative_class = substr($class, $len);

			// replace the namespace prefix with the base directory, replace namespace
			// separators with directory separators in the relative class name, append
			// with .php
			$file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

			// if the file exists, require it
			if ( file_exists($file) ) {
				require $file;
			}
		}
	}

	// ------------------------------------------------------------
	//   ---------------- SINGLETON SPECIFIC --------------------
	// ------------------------------------------------------------
	public static function getInstance() {
		if ( ! ( self::$_instance instanceof self ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}
}
Autoloader::getInstance();