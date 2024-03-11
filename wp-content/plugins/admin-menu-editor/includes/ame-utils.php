<?php

/**
 * Miscellaneous utility functions.
 */
class ameUtils {
	/**
	 * HTML tags allowed in WP_Error messages and titles.
	 *
	 * This is based on the default list of allowed tags in /wp-includes/kses.php.
	 */
	const ALLOWED_WP_ERROR_TAGS = array(
		'abbr'       => array(
			'title' => true,
		),
		'acronym'    => array(
			'title' => true,
		),
		'b'          => array(),
		'blockquote' => array(
			'cite' => true,
		),
		'cite'       => array(),
		'code'       => array(),
		'del'        => array(
			'datetime' => true,
		),
		'em'         => array(),
		'i'          => array(),
		'q'          => array(
			'cite' => true,
		),
		's'          => array(),
		'strong'     => array(),
	);

	/**
	 * Get a value from a nested array or object based on a path.
	 *
	 * @param array|object $array Get an entry from this array.
	 * @param array|string $path A list of array keys in hierarchy order, or a string path like "foo.bar.baz".
	 * @param mixed $default The value to return if the specified path is not found. Defaults to NULL.
	 * @param string $separator Path element separator. Only applies to string paths.
	 * @return mixed
	 */
	public static function get($array, $path, $default = null, $separator = '.') {
		if ( is_string($path) ) {
			$path = explode($separator, $path);
		}
		if ( empty($path) ) {
			return $default;
		}

		//Follow the $path into $input as far as possible.
		$currentValue = $array;
		$pathExists = true;
		foreach ($path as $node) {
			if ( ($currentValue instanceof ArrayAccess) && $currentValue->offsetExists($node) ) {
				$currentValue = $currentValue[$node];
			} else if ( is_array($currentValue) && array_key_exists($node, $currentValue) ) {
				$currentValue = $currentValue[$node];
			} else if ( is_object($currentValue) && property_exists($currentValue, $node) ) {
				$currentValue = $currentValue->$node;
			} else {
				$pathExists = false;
				break;
			}
		}

		if ( $pathExists ) {
			return $currentValue;
		}
		return $default;
	}

	/**
	 * Get the first non-root directory from a path.
	 *
	 * Examples:
	 *  "foo/bar"          => "foo"
	 *  "/foo/bar/baz.txt" => "foo"
	 *  "bar"              => null
	 *  "baz/"             => "baz"
	 *  "/"                => null
	 *
	 * @param string $fileName
	 * @return string|null
	 */
	public static function getFirstDirectory($fileName) {
		$fileName = ltrim($fileName, '/');

		$segments = explode('/', $fileName, 2);
		if ( (count($segments) > 1) && ($segments[0] !== '') ) {
			return $segments[0];
		}
		return null;
	}

	/**
	 * Capitalize the first character of every word. Supports UTF-8.
	 *
	 * @param string $input
	 * @return string
	 */
	public static function ucWords($input) {
		static $hasUnicodeSupport = null, $charset = 'UTF-8';
		if ( $hasUnicodeSupport === null ) {
			//We need the mbstring extension and PCRE UTF-8 support.
			$hasUnicodeSupport = function_exists('mb_list_encodings')
				&& (@preg_match('/\pL/u', 'a') === 1)
				&& function_exists('get_bloginfo');

			if ( $hasUnicodeSupport ) {
				//Technically, the encoding can change if something switches WP to a different site
				//in the middle of a request, but we'll ignore that possibility.
				$charset = get_bloginfo('charset');
				$hasUnicodeSupport = in_array($charset, mb_list_encodings()) && ($charset === 'UTF-8');
			}
		}

		if ( $hasUnicodeSupport ) {
			$totalLength = mb_strlen($input);
			$words = preg_split('/([\s\-_]++)/u', $input, -1, PREG_SPLIT_DELIM_CAPTURE);
			$output = array();
			foreach ($words as $word) {
				$firstCharacter = mb_substr($word, 0, 1, $charset);
				//In old PHP versions, you must specify a non-null length to get the rest of the string.
				$remainder = mb_substr($word, 1, $totalLength, $charset);
				$output[] = mb_strtoupper($firstCharacter, $charset) . $remainder;
			}
			return implode('', $output);
		}
		return ucwords($input);
	}

	/**
	 * Check if two arrays have the same keys and values. Arrays with string keys
	 * or mixed keys can be in different order and still be considered "equal".
	 *
	 * @param array $a
	 * @param array $b
	 * @return bool
	 */
	public static function areAssocArraysEqual($a, $b) {
		$secondArraySize = count($b);
		if ( count($a) !== $secondArraySize ) {
			return false;
		}
		$sameItems = array_intersect_assoc($a, $b);
		return count($sameItems) === $secondArraySize;
	}

	/**
	 * Escape a WP_Error object for passing it to wp_die().
	 *
	 * Converts special characters in error messages to HTML entities.
	 * Returns a new WP_Error instance. Does not modify the input object.
	 *
	 * @param WP_Error $error
	 * @return WP_Error New WP_Error instance.
	 */
	public static function escapeWpError($error) {
		return self::copyErrorWithFilter($error, 'esc_html');
	}

	/**
	 * Strip disallowed HTML from a WP_Error object.
	 *
	 * @param WP_Error $error
	 * @return WP_Error New WP_Error instance.
	 */
	public static function ksesWpError($error) {
		return self::copyErrorWithFilter($error, array(__CLASS__, 'ksesCallbackForErrors'));
	}

	protected static function ksesCallbackForErrors($message) {
		return wp_kses($message, self::ALLOWED_WP_ERROR_TAGS);
	}

	/**
	 * Copy a WP_Error object and apply a filter callback to each message.
	 *
	 * Also, if an error has a data item that's an array with a 'title' key,
	 * this escapes HTML in the title.
	 *
	 * @param \WP_Error $error
	 * @param callable $callback
	 * @return \WP_Error
	 */
	protected static function copyErrorWithFilter($error, $callback) {
		$result = new WP_Error();
		$canGetAllData = method_exists($error, 'get_all_error_data'); //WP 5.6+

		foreach ($error->get_error_codes() as $code) {
			foreach ($error->get_error_messages($code) as $message) {
				$result->add($code, call_user_func($callback, $message));
			}

			if ( $canGetAllData ) {
				$dataItems = $error->get_all_error_data($code);
			} else {
				$data = $error->get_error_data($code);
				if ( $data !== null ) {
					$dataItems = array($data);
				} else {
					$dataItems = array();
				}
			}

			foreach ($dataItems as $data) {
				//Page titles should never contain unescaped HTML tags.
				//As of this writing, this plugin doesn't put titles in error data,
				//but other code might, and wp_die() supports it.
				if ( isset($data['title']) ) {
					$data['title'] = esc_html($data['title']);
				}
				$result->add_data($data, $code);
			}
		}

		return $result;
	}

	/**
	 * Get the first element of an iterable collection.
	 *
	 * @param iterable $collection Array, Traversable, Generator, etc.
	 * @param mixed $defaultValue Value to return if the collection is empty.
	 * @return mixed
	 */
	public static function getFirstItem($collection, $defaultValue = null) {
		foreach ($collection as $value) {
			return $value;
		}
		return $defaultValue;
	}

	/**
	 * Get the first key of an iterable collection.
	 *
	 * @param iterable $collection
	 * @param iterable $defaultValue
	 * @return int|string|null
	 */
	public static function getFirstKey($collection, $defaultValue = null) {
		foreach ($collection as $key => $value) {
			return $key;
		}
		return $defaultValue;
	}

	/**
	 * Send HTTP caching headers.
	 *
	 * @param int|null $lastModified Unix timestamp for the last modification time.
	 * @param int $cacheLifetime Cache lifetime in seconds.
	 * @return bool True if the response body should be omitted because an If-Modified-Since header
	 *              was sent and the resource hasn't changed.
	 */
	public static function sendCachingHeaders($lastModified, $cacheLifetime = 30 * 24 * 3600) {
		//Support the If-Modified-Since header.
		$omitResponseBody = false;
		if (
			!empty($lastModified)
			&& !empty($_SERVER['HTTP_IF_MODIFIED_SINCE'])
			&& is_string($_SERVER['HTTP_IF_MODIFIED_SINCE'])
		) {
			//strtotime() should be able to handle invalid strings safely.
			//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$threshold = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
			if ( $threshold >= $lastModified ) {
				header('HTTP/1.1 304 Not Modified');
				$omitResponseBody = true;
			}
		}

		//Enable browser caching.
		//Note that admin-ajax.php always adds HTTP headers that prevent caching, so we will
		//override all of them even though we don't actually need some of them, like "Expires".
		if ( !empty($lastModified) ) {
			header('Last-Modified: ' . gmdate('D, d M Y H:i:s ', $lastModified) . 'GMT');
		}
		$expires = !empty($lastModified) ? ($lastModified + $cacheLifetime) : (time() + $cacheLifetime);
		header('Expires: ' . gmdate('D, d M Y H:i:s ', $expires) . 'GMT');
		header('Cache-Control: public, max-age=' . $cacheLifetime);

		return $omitResponseBody;
	}
}

/**
 * @see ameUtils::escapeWpError
 *
 * This function exists because the "EscapeOutput" sniff in the WordPress coding standards
 * doesn't understand class methods.
 *
 * @param \WP_Error $error
 * @return \WP_Error
 */
function wsAmeEscapeWpError($error) {
	return ameUtils::escapeWpError($error);
}

class ameFileLock {
	protected $fileName;
	protected $handle = null;

	public function __construct($fileName) {
		$this->fileName = $fileName;
	}

	//fopen() and flock() should be fine here because we only need read permissions.
	//phpcs:disable WordPressVIPMinimum.Functions.RestrictedFunctions.file_ops_flock,WordPress.WP.AlternativeFunctions.file_system_read_fopen
	public function acquire($timeout = null) {
		if ( $this->handle !== null ) {
			throw new RuntimeException('Cannot acquire a lock that is already held.');
		}
		if ( !function_exists('flock') ) {
			return false;
		}

		$this->handle = @fopen(__FILE__, 'r');
		if ( !$this->handle ) {
			$this->handle = null;
			return false;
		}

		$success = @flock($this->handle, LOCK_EX | LOCK_NB, $wouldBlock);

		if ( !$success && $wouldBlock && ($timeout !== null) ) {
			$timeout = max(min($timeout, 0.1), 600);
			$endTime = microtime(true) + $timeout;
			//Wait for a short, random time and try again.
			do {
				$canWaitMore = $this->waitRandom($endTime);
				$success = @flock($this->handle, LOCK_EX | LOCK_NB, $wouldBlock);
			} while (!$success && $wouldBlock && $canWaitMore);
		}

		if ( !$success ) {
			fclose($this->handle);
			$this->handle = null;
			return false;
		}
		return true;
	}

	public function release() {
		if ( $this->handle !== null ) {
			@flock($this->handle, LOCK_UN);
			fclose($this->handle);
			$this->handle = null;
		}
	}
	//phpcs:enable

	/**
	 * Wait for a random interval without going over $endTime.
	 *
	 * @param float|int $endTime Unix timestamp.
	 * @return bool TRUE if there's still time until $endTime, FALSE otherwise.
	 */
	protected function waitRandom($endTime) {
		$now = microtime(true);
		if ( $now >= $endTime ) {
			return false;
		}

		$delayMs = wp_rand(80, 300);
		$remainingTimeMs = ($endTime - $now) * 1000;
		if ( $delayMs < $remainingTimeMs ) {
			usleep($delayMs * 1000);
			return true;
		} else {
			usleep($remainingTimeMs * 1000);
			return false;
		}
	}

	public static function create($fileName) {
		return new self($fileName);
	}

	public function __destruct() {
		$this->release();
	}
}

class ameOrderedMap implements Iterator, Countable {
	/**
	 * @var ameLinkedListNode[]
	 */
	private $nodesByKey = array();

	/**
	 * @var ameLinkedListNode|null
	 */
	private $head = null;
	/**
	 * @var ameLinkedListNode|null
	 */
	private $tail = null;
	/**
	 * @var ameLinkedListNode|null
	 */
	private $currentNode = null;

	/**
	 * @param array $items
	 * @return $this
	 */
	public function addAll($items) {
		foreach ($items as $key => $item) {
			$this->set($key, $item);
		}
		return $this;
	}

	/**
	 * @param string $previousKey
	 * @param array $items
	 * @return $this
	 */
	public function insertAllAfter($previousKey, $items) {
		if ( !isset($this->nodesByKey[$previousKey]) ) {
			return $this->addAll($items);
		}

		$previousNode = $this->nodesByKey[$previousKey];
		foreach ($items as $key => $value) {
			if ( isset($this->nodesByKey[$key]) ) {
				$node = $this->nodesByKey[$key];
			} else {
				$node = new ameLinkedListNode($value, $key);
				$this->nodesByKey[$key] = $node;
			}

			$this->insertNodeAfter($previousNode, $node);
			$previousNode = $node;
		}

		return $this;
	}

	/**
	 * @param string $previousKey
	 * @param string $key
	 * @param mixed $item
	 * @return $this
	 */
	public function insertAfter($previousKey, $key, $item) {
		return $this->insertAllAfter($previousKey, array($key => $item));
	}

	private function insertNodeAfter($previousNode, $newNode) {
		$newNode->previous = $previousNode;
		$newNode->next = $previousNode->next;
		if ( $newNode->next !== null ) {
			$newNode->next->previous = $newNode;
		}

		$previousNode->next = $newNode;

		if ( $this->tail === $previousNode ) {
			$this->tail = $newNode;
		}
	}

	/**
	 * @param string $nextKey
	 * @param string $key
	 * @param $item
	 * @return $this
	 */
	public function insertBefore($nextKey, $key, $item) {
		if ( !isset($this->nodesByKey[$nextKey]) ) {
			return $this->set($key, $item);
		}

		$nextNode = $this->nodesByKey[$nextKey];
		$previousNode = $nextNode->previous;

		if ( isset($this->nodesByKey[$key]) ) {
			$node = $this->nodesByKey[$key];
		} else {
			$node = new ameLinkedListNode($item, $key);
			$this->nodesByKey[$key] = $node;
		}

		$node->next = $nextNode;
		$node->previous = $previousNode;

		$nextNode->previous = $node;
		if ( $previousNode !== null ) {
			$previousNode->next = $node;
		}

		if ( $this->head === $nextNode ) {
			$this->head = $node;
		}

		return $this;
	}

	public function set($key, $item) {
		if ( isset($this->nodesByKey[$key]) ) {
			$this->nodesByKey[$key]->value = $item;
		} else {
			$this->append($key, $item);
		}
		return $this;
	}

	private function append($key, $item) {
		$node = new ameLinkedListNode($item, $key);
		$this->nodesByKey[$key] = $node;

		if ( $this->tail === null ) {
			$this->head = $node;
			$this->tail = $node;
		} else {
			$this->insertNodeAfter($this->tail, $node);
			$this->tail = $node;
		}

		return $this;
	}

	#[\ReturnTypeWillChange]
	public function current() {
		return $this->currentNode->value;
	}

	#[\ReturnTypeWillChange]
	public function next() {
		if ( $this->currentNode !== null ) {
			$this->currentNode = $this->currentNode->next;
		}
	}

	#[\ReturnTypeWillChange]
	public function key() {
		return $this->currentNode->key;
	}

	#[\ReturnTypeWillChange]
	public function valid() {
		return ($this->currentNode !== null);
	}

	#[\ReturnTypeWillChange]
	public function rewind() {
		$this->currentNode = $this->head;
	}

	#[\ReturnTypeWillChange]
	public function count() {
		return count($this->nodesByKey);
	}

	/**
	 * Filter the map using a callback function.
	 * Returns a new map that contains only the items for which the callback function returns a truthy value.
	 *
	 * @param callable $predicate
	 * @return ameOrderedMap
	 */
	public function filter($predicate) {
		$result = new self();
		foreach ($this as $key => $value) {
			if ( call_user_func($predicate, $value, $key) ) {
				$result->append($key, $value);
			}
		}
		return $result;
	}
}

class ameLinkedListNode {
	/**
	 * @var string
	 */
	public $key;

	/**
	 * @var mixed
	 */
	public $value;

	/**
	 * @var self|null
	 */
	public $next = null;
	/**
	 * @var self|null
	 */
	public $previous = null;

	public function __construct($value, $key = '') {
		$this->value = $value;
		$this->key = $key;
	}
}

class ameMultiDictionary {
	const PATH_SEPARATOR = '.';
	const MAX_PATH_DEPTH = 64;

	/**
	 * Get a value from an array or object using a path.
	 *
	 * Supports multidimensional/nested arrays and objects.
	 *
	 * @param array|object $collection
	 * @param string|string[] $path
	 * @param mixed $defaultValue
	 * @param string $separator
	 * @return mixed|null The value at the specified path, or the default value
	 *                    if the path does not exist.
	 */
	public static function get($collection, $path, $defaultValue = null, $separator = self::PATH_SEPARATOR) {
		$path = self::parsePath($path, $separator);
		if ( empty($path) ) {
			return $collection;
		}

		//Follow the $path into the $collection as far as possible.
		$currentValue = $collection;
		$pathExists = true;
		foreach ($path as $key) {
			if ( ($currentValue instanceof ArrayAccess) && $currentValue->offsetExists($key) ) {
				//Caution: offsetExists() may return false if the key exists but is null.
				$currentValue = $currentValue[$key];
			} else if ( is_array($currentValue) && array_key_exists($key, $currentValue) ) {
				$currentValue = $currentValue[$key];
			} else if ( is_object($currentValue) && property_exists($currentValue, $key) ) {
				$currentValue = $currentValue->{$key};
			} else {
				$pathExists = false;
				break;
			}
		}

		if ( $pathExists ) {
			return $currentValue;
		}
		return $defaultValue;
	}

	public static function set(
		&$collection,
		$path,
		$value,
		$createArrays = true,
		$overwriteScalars = false,
		$separator = self::PATH_SEPARATOR
	) {
		$path = self::parsePath($path, $separator);
		if ( empty($path) ) {
			//An empty path doesn't make sense, we can't replace the collection itself.
			throw new InvalidArgumentException('Cannot set a value because the path is empty.');
		}

		if ( !self::isCollection($collection) ) {
			//The collection is not an array or an object, so we can't set a value in it.
			throw new InvalidArgumentException('Collection must be an array or an object.');
		}

		$lastKey = array_pop($path);
		if ( empty($path) ) {
			$target = &$collection;
		} else {
			$target = &self::acquireNestedCollection(
				$collection,
				$path,
				$createArrays,
				$overwriteScalars
			);
			if ( $target === null ) {
				return false;
			}
		}

		if ( is_array($target) || ($target instanceof ArrayAccess) ) {
			$target[$lastKey] = $value;
		} else if ( is_object($target) ) {
			$target->{$lastKey} = $value;
		}
		return true;
	}

	public static function delete(&$collection, $path, $separator = self::PATH_SEPARATOR) {
		$path = self::parsePath($path, $separator);
		if ( empty($path) ) {
			throw new InvalidArgumentException('Cannot delete an item because the path is empty.');
		}
		if ( !self::isCollection($collection) ) {
			throw new InvalidArgumentException('Collection must be an array or an object.');
		}

		$lastKey = array_pop($path);
		$target = &self::acquireNestedCollection($collection, $path, false);
		if ( $target !== null ) {
			if ( is_array($target) || ($target instanceof ArrayAccess) ) {
				unset($target[$lastKey]);
			} else if ( is_object($target) ) {
				unset($target->{$lastKey});
			}
		}
	}

	public static function parsePath($path, $separator = self::PATH_SEPARATOR) {
		if ( is_array($path) ) {
			return $path;
		} else if ( ($path === '') || ($path === $separator) ) {
			return array();
		}
		return explode($separator, $path, self::MAX_PATH_DEPTH);
	}

	/**
	 * @param array $prefix
	 * @param string|array $path
	 * @return array
	 */
	public static function addPrefixToPath($prefix, $path, $separator = self::PATH_SEPARATOR) {
		return array_merge($prefix, self::parsePath($path, $separator));
	}

	protected static function isCollection($collection) {
		return is_array($collection) || is_object($collection);
	}

	protected static function &acquireNestedCollection(
		&$collection,
		$parsedPath,
		$createArrays = true,
		$overwriteScalars = false
	) {
		$current = &$collection;
		$notFound = null;
		$previousNode = null;
		$previousKey = null;
		foreach ($parsedPath as $key) {
			//The array and object branches are functionally identical,
			//but they must be separated due to syntax differences.
			if ( is_array($current) || ($current instanceof ArrayAccess) ) {
				if ( !isset($current[$key]) ) {
					if ( $createArrays ) {
						$current[$key] = array();
					} else {
						return $notFound;
					}
				}
				$current = &$current[$key];
			} else if ( is_object($current) ) {
				if ( !isset($current->{$key}) ) {
					if ( $createArrays ) {
						$current->{$key} = array();
					} else {
						return $notFound;
					}
				}
				$current = &$current->{$key};
			}

			//Overwrite scalar values with associative arrays if necessary.
			if ( !is_array($current) && !is_object($current) ) {
				if ( $overwriteScalars && ($previousNode !== null) ) {
					if ( is_array($previousNode) || ($previousNode instanceof ArrayAccess) ) {
						$previousNode[$previousKey] = array();
					} else if ( is_object($previousNode) ) {
						$previousNode->{$previousKey} = array();
					}
					$current = &$previousNode[$previousKey];
				} else {
					return $notFound;
				}
			}

			$previousNode = &$current;
			$previousKey = $key;
		}

		return $current;
	}
}