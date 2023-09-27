<?php
namespace WPDRMS\ASP\Patterns;
/**
 * This can be used in Abstract classes, can handle heritage by storing the singleton data in an array
 */
trait SingletonTrait {
	/**
	 * Use a very unspecific name for this to prevent any conflicts of attribute names
	 * @var array
	 */
	protected static $_singleton_object_instances = array();

	final public static function getInstance() {
		$class = get_called_class();
		if ( !isset(static::$_singleton_object_instances[$class]) ) {
			static::$_singleton_object_instances[$class] = new $class();
		}
		return static::$_singleton_object_instances[$class];
	}

	final public static function _o() {
		return static::getInstance();
	}

	private function __construct() {}

	final public function __wakeup() {}

	final public function __clone() {}
}