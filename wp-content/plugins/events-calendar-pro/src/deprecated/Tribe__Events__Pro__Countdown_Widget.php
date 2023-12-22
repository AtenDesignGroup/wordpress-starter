<?php
_deprecated_file( __FILE__, '6.0.0' );

/**
 * @deprecated 6.0.0
 */
class Tribe__Events__Pro__Countdown_Widget extends \Tribe\Events\Pro\Views\V2\Widgets\Widget_Countdown {
	public function __construct( $id_base = '', $name = '', $widget_options = [], $control_options = [] ) {
		_deprecated_function( __METHOD__, '6.0.0' );

		parent::__construct();
	}

	public function update( $new_instance, $old_instance ) {
		_deprecated_function( __METHOD__, '6.0.0' );

		return parent::update( $new_instance, $old_instance );
	}

	public function form( $instance ) {
		_deprecated_function( __METHOD__, '6.0.0' );

		return parent::form( $instance );
	}

	public function widget( $args, $instance ) {
		_deprecated_function( __METHOD__, '6.0.0' );

		return parent::widget( $args, $instance );
	}

	public function get_output( $instance, $deprecated = null, $deprecated_ = null, $deprecated__ = null ) {
		_deprecated_function( __METHOD__, '6.0.0' );
	}

	public function generate_countdown_output( $seconds, $complete, $hour_format, $event, $deprecated = null ) {
		_deprecated_function( __METHOD__, '6.0.0' );
	}
}