<?php

namespace YahnisElsts\AdminMenuEditor\Options;

/**
 * A simplified PHP version of the Option class from Scala.
 *
 * It is also heavily inspired by the phpoption/phpoption package by Johannes M. Schmitt,
 * though this version is designed to support PHP 5.6 and does not require PHP 7.0+.
 *
 * @template T
 */
abstract class Option implements \IteratorAggregate {
	/**
	 * @return boolean
	 */
	abstract public function isDefined();

	/**
	 * @return boolean
	 */
	public function isEmpty() {
		return !$this->isDefined();
	}

	public function nonEmpty() {
		return $this->isDefined();
	}

	/**
	 * @return T
	 * @throws \RuntimeException If the option is empty.
	 */
	abstract public function get();

	/**
	 * @param T $default
	 * @return T
	 */
	abstract public function getOrElse($default);

	/**
	 * @param callable():T $callable
	 * @return T
	 */
	abstract public function getOrCall($callable);

	/**
	 * @param Option<T> $alternative
	 * @return Option<T>
	 */
	abstract public function orElse(self $alternative);

	/**
	 * @template R
	 * @param callable(T):R $callable
	 * @return Option<R>
	 */
	abstract public function map($callable);

	/**
	 * @template R
	 * @param callable(T):Option<R> $callable
	 * @return Option<R>
	 */
	abstract public function flatMap($callable);

	/**
	 * Apply the given function to the option's value, if it's not empty.
	 *
	 * This is called "each" and not "forEach" because "foreach" is a keyword,
	 * which means it can't be used as a function name before PHP 7.0.
	 *
	 * @param callable(T):void $callable
	 * @return $this The same option instance.
	 */
	abstract public function each($callable);

	/**
	 * Check if the option contains the specified value.
	 *
	 * @param mixed $value
	 * @return boolean
	 */
	abstract public function contains($value);


	/**
	 * @template A
	 * @param A $value
	 * @param mixed $emptyValue
	 * @return Option<A>
	 */
	public static function fromValue($value, $emptyValue = null) {
		if ( $value === $emptyValue ) {
			return None::getInstance();
		} else {
			return new Some($value);
		}
	}

	/**
	 * @template A
	 * @param callable():A $callable
	 * @return Option<A>
	 */
	public static function fromCallable($callable, $arguments = array()) {
		return new LazyOption($callable, $arguments);
	}

	/**
	 * @template A
	 * @param A $value
	 * @return Option<A>
	 */
	public static function some($value) {
		return new Some($value);
	}

	/**
	 * @return Option<T>
	 */
	public static function none() {
		return None::getInstance();
	}
}

/**
 * @template T
 * @extends Option<T>
 */
final class Some extends Option {
	/**
	 * @var mixed
	 */
	private $value;

	public function __construct($value) {
		$this->value = $value;
	}

	public function isDefined() {
		return true;
	}

	public function get() {
		return $this->value;
	}

	public function getOrElse($default) {
		return $this->value;
	}

	public function getOrCall($callable) {
		return $this->value;
	}

	public function orElse(Option $alternative) {
		return $this;
	}

	public function map($callable) {
		return new self($callable($this->value));
	}

	public function flatMap($callable) {
		return $callable($this->value);
	}

	public function each($callable) {
		$callable($this->value);
		return $this;
	}

	public function contains($value) {
		return ($this->value === $value);
	}

	#[\ReturnTypeWillChange]
	public function getIterator() {
		return new \ArrayIterator([$this->value]);
	}
}

final class None extends Option {
	/**
	 * @var null|self
	 */
	private static $instance = null;

	private function __construct() {
		//Prevent others from instantiating this class.
	}

	public static function getInstance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function isDefined() {
		return false;
	}

	public function get() {
		throw new \RuntimeException('Option is empty.');
	}

	public function getOrElse($default) {
		return $default;
	}

	public function getOrCall($callable) {
		return $callable();
	}

	public function orElse(Option $alternative) {
		return $alternative;
	}

	public function map($callable) {
		return $this;
	}

	public function flatMap($callable) {
		return $this;
	}

	public function each($callable) {
		//Intentionally does nothing.
		return $this;
	}

	public function contains($value) {
		return false;
	}

	#[\ReturnTypeWillChange]
	public function getIterator() {
		return new \EmptyIterator();
	}
}

/**
 * Lazy version of the Option class.
 *
 * This class just has an internal, lazy-initialized option, and it forwards all
 * method calls to that option.
 *
 * @template T
 * @extends Option<T>
 */
class LazyOption extends Option {
	/**
	 * @var callable
	 */
	private $callback;
	/**
	 * @var array
	 */
	private $arguments;

	/**
	 * @var Option<T>|null
	 */
	private $innerOption = null;

	public function __construct($callback, $arguments = array()) {
		if ( !is_callable($callback) ) {
			throw new \InvalidArgumentException('$callback must be a valid callable.');
		}

		$this->callback = $callback;
		$this->arguments = $arguments;
	}

	private function resolve() {
		if ( $this->innerOption === null ) {
			$value = call_user_func_array($this->callback, $this->arguments);
			if ( $value instanceof Option ) {
				$this->innerOption = $value;
			} else {
				$this->innerOption = Option::fromValue($value);
			}
		}
		return $this->innerOption;
	}

	public function isDefined() {
		return $this->resolve()->isDefined();
	}

	public function get() {
		return $this->resolve()->get();
	}

	public function getOrElse($default) {
		return $this->resolve()->getOrElse($default);
	}

	public function getOrCall($callable) {
		return $this->resolve()->getOrCall($callable);
	}

	public function orElse(Option $alternative) {
		return $this->resolve()->orElse($alternative);
	}

	public function map($callable) {
		return $this->resolve()->map($callable);
	}

	public function flatMap($callable) {
		return $this->resolve()->flatMap($callable);
	}

	public function each($callable) {
		$this->resolve()->each($callable);
		return $this;
	}

	public function contains($value) {
		return $this->resolve()->contains($value);
	}

	#[\ReturnTypeWillChange]
	public function getIterator() {
		return $this->resolve()->getIterator();
	}
}