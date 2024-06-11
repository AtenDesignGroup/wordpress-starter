<?php

namespace YahnisElsts\AdminMenuEditor;
/**
 * A basic PSR-4 autoloader.
 */
class AmeAutoloader {
	protected $prefixes;

	/**
	 * @param array<string,string> $namespacePrefixes
	 */
	public function __construct($namespacePrefixes) {
		//Ensure that each prefix ends with a backslash and each path ends
		//with a forward slash.
		$this->prefixes = array();
		foreach ($namespacePrefixes as $prefix => $path) {
			$prefix = trim($prefix, '\\') . '\\';
			$path = rtrim($path, '/\\') . '/';
			$this->prefixes[$prefix] = $path;
		}
	}

	public function register() {
		spl_autoload_register([$this, 'loadClass']);
	}

	public function loadClass($class) {
		foreach ($this->prefixes as $prefix => $baseDirectory) {
			//Does the full class name start with this namespace prefix?
			$len = strlen($prefix);
			if ( strncmp($prefix, $class, $len) !== 0 ) {
				continue;
			}

			$relativeClassName = substr($class, $len);

			//Replace the prefix with the base directory, replace namespace separators
			//with directory separators, and append the ".php" extension.
			$fileName = $baseDirectory . str_replace('\\', '/', $relativeClassName) . '.php';

			if ( file_exists($fileName) ) {
				require $fileName;
			}
		}
	}
}