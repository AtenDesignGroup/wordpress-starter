<?php /** @noinspection HttpUrlsUsage */

namespace WPDRMS\ASP\Misc;

use WPDRMS\ASP\Patterns\SingletonTrait;

class OutputBuffer {
	use SingletonTrait;

	private $found = false;

	function obStart() {
		ob_start(array($this, 'obCallback'));
	}

	function obCallback($buffer, $phase) {
		if ($phase & PHP_OUTPUT_HANDLER_FINAL || $phase & PHP_OUTPUT_HANDLER_END) {
			// Hook into this to change the buffer
			return apply_filters('asp_ob_end', $buffer);
		}
		return $buffer;
	}

	function obClose(): bool {
		$handlers = ob_list_handlers();
		$callback = self::class . '::obCallback';
		$found = in_array($callback, $handlers);
		if ( $found ) {
			for ($i = count($handlers) - 1; $i >= 0; $i--) {
				ob_end_flush();
				if ($handlers[$i] === $callback) {
					break;
				}
			}
		}

		// $this->found = found is not good. If this function is triggered multiple times, then may override "true" to "false"
		$this->found = $found == true ? true : $this->found;

		return $found;
	}

	function obFound(): bool {
		return $this->found;
	}
}