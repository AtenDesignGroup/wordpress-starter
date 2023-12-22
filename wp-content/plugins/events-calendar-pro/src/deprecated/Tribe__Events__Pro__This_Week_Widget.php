<?php
_deprecated_file( __FILE__, '6.0.0' );

/**
 * @deprecated 6.0.0
 */
class Tribe__Events__Pro__This_Week_Widget extends \Tribe\Events\Pro\Views\V2\Widgets\Widget_Week {
	public function __construct( $id_base = '', $name = '', $widget_options = [], $control_options = [] ) {
		_deprecated_function( __METHOD__, '6.0.0' );

		parent::__construct();
	}

	public function load_scripts( $hook ) {
		_deprecated_function( __METHOD__, '6.0.0' );
	}

	public function widget( $args, $instance ) {
		_deprecated_function( __METHOD__, '6.0.0' );

		return parent::widget( $args, $instance );
	}

	public function form( $instance ) {
		_deprecated_function( __METHOD__, '6.0.0' );

		return parent::form( $instance );
	}

	public function update( $new_instance, $old_instance ) {
		_deprecated_function( __METHOD__, '6.0.0' );

		return parent::update( $new_instance, $old_instance );
	}
}