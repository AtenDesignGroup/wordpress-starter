<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Controls;

class PopupSlider {
	protected static $basicInstance = null;

	protected $options = [];

	public function __construct($options = []) {
		$this->options = $options;
	}

	public function render() {
		?>
		<div class="ame-popup-slider"
		     data-ame-popup-slider-options="<?php echo esc_attr(wp_json_encode($this->options)); ?>"
		     style="display: none">
			<div class="ame-popup-slider-tip ame-popup-slider-bottom-tip"></div>
			<div class="ame-popup-slider-tip ame-popup-slider-top-tip"></div>
			<div class="ame-popup-slider-bar">
				<div class="ame-popup-slider-groove"></div>
				<div class="ui-slider-handle ame-popup-slider-handle"></div>
			</div>
		</div>
		<?php
		static::enqueueDependencies();
	}

	public static function enqueueDependencies() {
		static $done = false;
		if ( $done ) {
			return;
		}
		$done = true;

		wp_enqueue_auto_versioned_script(
			'ame-customizable-popup-slider',
			plugins_url('assets/popup-slider.js', AME_CUSTOMIZABLE_BASE_FILE),
			['jquery', 'jquery-ui-slider', 'jquery-ui-position']
		);
	}

	/**
	 * @return PopupSlider
	 */
	public static function basic() {
		if ( static::$basicInstance === null ) {
			$instance = new static();
			static::$basicInstance = $instance;
			return $instance;
		}
		return static::$basicInstance;
	}
}