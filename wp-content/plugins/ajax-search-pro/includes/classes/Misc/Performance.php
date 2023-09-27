<?php
namespace WPDRMS\ASP\Misc;

defined('ABSPATH') or die("You can't access this file directly.");

class Performance {

	/**
	 * @var array of performance values
	 */
	private $records;
	/**
	 * @var array default values for the records array
	 */
	private $default = array(
		'run_count' => 0,
		'average_runtime' => 0,
		'average_memory' => 0,
		'last_runtime' => 0,
		'last_memory' => 0
	);
	/**
	 * @var int current runtime
	 */
	private $runtime;
	/**
	 * @var int actual memory usage
	 */
	private $memory;
	/**
	 * @var string the name of the storage
	 */
	private $key;

	/**
	 * Setup Class
	 *
	 * @param string $key
	 */
	function __construct(string $key = "plugin_performance") {
		$this->key = $key;
		$this->records = get_option($key, $this->default);
	}

	/**
	 * Deletes the storage
	 */
	function reset() {
		delete_option($this->key);
	}

	/**
	 * Gets the storage
	 *
	 * @return array
	 */
	function get_data(): array {
		return $this->records;
	}

	/**
	 * Starts the measurement
	 */
	function start_measuring() {
		$this->runtime = microtime(true);
		$this->memory = memory_get_usage(true);
	}

	/**
	 * Stops the measurement
	 */
	function stop_measuring() {
		$this->runtime = microtime(true) - $this->runtime;
		$this->memory = memory_get_peak_usage(true) - $this->memory;
		$this->save();
	}

	/**
	 * Saves the values
	 */
	private function save() {
		$this->count_averages();

		$this->records['last_runtime'] = $this->runtime > 15 ? 15 : $this->runtime;
		$this->records['last_memory'] = $this->memory;
		++$this->records['run_count'];

		update_option($this->key, $this->records);
	}

	/**
	 * Calculates the final averages before writing to database
	 */
	private function count_averages() {
		$this->records['average_runtime'] =
			($this->records['average_runtime'] * $this->records['run_count'] + $this->runtime) / ($this->records['run_count'] + 1);
		$this->records['average_memory'] =
			($this->records['average_memory'] * $this->records['run_count'] + $this->memory) / ($this->records['run_count'] + 1);
	}
}