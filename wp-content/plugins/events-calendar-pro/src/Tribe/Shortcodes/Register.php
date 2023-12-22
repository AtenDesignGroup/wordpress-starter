<?php


/**
 * Registers shortcodes handlers for each of the widget wrappers.
 */
class Tribe__Events__Pro__Shortcodes__Register {
	/**
	 * Variable that holds the name of the shortcodes being created
	 *
	 * @since 5.1.4
	 *
	 * @var array
	 */
	private $shortcodes = [
		'tribe_event_inline',
	];

	public function __construct() {
		add_shortcode( 'tribe_event_inline', array( $this, 'tribe_inline' ) );

		$this->hook();
	}

	/**
	 * Function used to attach the hooks associated with this class.
	 *
	 * @since 4.4.26
	 */
	public function hook() {
		add_filter( 'tribe_body_classes_should_add', [ $this, 'body_classes_should_add' ], 10, 4 );
	}

	/**
	 * Handler for Inline Event Content Shortcode
	 *
	 * @param $atts
	 * @param $content
	 * @param $tag
	 *
	 * @return string
	 */
	public function tribe_inline( $atts, $content, $tag ) {

		$shortcode = new Tribe__Events__Pro__Shortcodes__Tribe_Inline( $atts, $content, $tag );

		return $shortcode->output();
	}


	/**
	 * Hook into filter and add our logic for adding body classes.
	 *
	 * @since 5.1.4
	 *
	 * @param boolean $add              Whether to add classes or not.
	 * @param array   $add_classes      The array of body class names to add.
	 * @param array   $existing_classes An array of existing body class names from WP.
	 * @param string  $queue            The queue we want to get 'admin', 'display', 'all'.
	 *
	 * @return boolean Whether body classes should be added or not.
	 */
	public function body_classes_should_add( $add, $add_classes, $existing_classes, $queue ) {
		global $post;

		// If we're doing the tribe_events shortcode, add classes.
		if (
			is_singular()
			&& $post instanceof \WP_Post
		) {
			foreach ( $this->shortcodes as $shortcode ) {
				if ( has_shortcode( $post->post_content, $shortcode ) ) {
					return true;
				}
			}
		}

		return $add;
	}

	/**
	 * @deprecated 6.0.0
	 */
	public function mini_calendar( $atts ) {
		_deprecated_function( __METHOD__, '6.0.0' );
	}

	/**
	 * @deprecated 6.0.0
	 */
	public function events_list( $atts ) {
		_deprecated_function( __METHOD__, '6.0.0' );
	}

	/**
	 * @deprecated 6.0.0
	 */
	public function featured_venue( $atts ) {
		_deprecated_function( __METHOD__, '6.0.0' );
	}

	/**
	 * @deprecated 6.0.0
	 */
	public function event_countdown( $atts ) {
		_deprecated_function( __METHOD__, '6.0.0' );
	}

	/**
	 * @deprecated 6.0.0
	 */
	public function this_week( $atts ) {
		_deprecated_function( __METHOD__, '6.0.0' );
	}

	/**
	 * @deprecated 6.0.0
	 */
	public function tribe_events( $atts ) {
		_deprecated_function( __METHOD__, '6.0.0' );
	}

	/**
	 * @deprecated 6.0.0
	 */
	public function search_shortcodes() {
		_deprecated_function( __METHOD__, '6.0.0' );
	}

	/**
	 * @deprecated 6.0.0
	 */
	public function ical_events_list_args( $args = array() ) {
		_deprecated_function( __METHOD__, '6.0.0' );
	}

	/**
	 * @deprecated 6.0.0
	 */
	public function update_shortcode_main_calendar( $post_id ) {
		_deprecated_function( __METHOD__, '6.0.0' );
	}

	/**
	 * @deprecated 6.0.0
	 */
	public function maybe_reset_main_calendar( $post_id ) {
		_deprecated_function( __METHOD__, '6.0.0' );
	}

	/**
	 * @deprecated 6.0.0
	 */
	public function shortcode_main_calendar_link( $link, $type ) {
		_deprecated_function( __METHOD__, '6.0.0' );
	}
}
