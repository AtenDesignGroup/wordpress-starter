<?php

namespace Tribe\Events\Pro\Admin\Manager;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

if ( ! class_exists( 'WP_Posts_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-posts-list-table.php' );
}

if ( ! function_exists( 'convert_to_screen' ) ) {
	require_once ABSPATH . 'wp-admin/includes/template.php';
}

if ( ! class_exists( 'WP_Screen' ) ) {
	/** WordPress Administration Screen API */
	require_once ABSPATH . 'wp-admin/includes/class-wp-screen.php';
	require_once ABSPATH . 'wp-admin/includes/screen.php';
}

use \WP_Posts_List_Table;
use \WP_Screen;

/**
 * Class Events_Table
 *
 * @since   5.9.0
 *
 * @package Tribe\Events\Pro\Admin\Manager
 */
class Events_Table extends WP_Posts_List_Table {
	/**
	 * Events_Table constructor.
	 *
	 * @since 5.9.0
	 *
	 * @param array $args Arguments that setup the Events Table.
	 */
	public function __construct( $args = [] ) {
		$screen_hook    = 'edit-' . \Tribe__Events__Main::POSTTYPE;
		$args['screen'] = $this->screen = WP_Screen::get( $screen_hook );
		$this->screen->set_current_screen();

		parent::__construct( $args );
	}

	/**
	 * Calls the handle_row_actions from the parent class to make it public.
	 *
	 * @since 5.9.0
	 *
	 * @param int|\WP_Post $post Which post we are dealing with.
	 *
	 * @return string Row actions HTML.
	 */
	public function get_row_actions( $post ) {
		$post = get_post( $post );

		return $this->handle_row_actions( $post, 'title', 'title' );
	}

	/**
	 * Modifies the row actions that will be printed.
	 *
	 * @since 5.9.0
	 *
	 * @param string[] $actions        Which are the current actions available.
	 * @param false    $always_visible Should be always visible or not?
	 *
	 * @return string
	 */
	protected function row_actions( $actions, $always_visible = false ) {
		if ( ! empty( $actions['quickedit'] ) ) {
			unset( $actions['quickedit'] );
		}
		if ( ! empty( $actions['inline hide-if-no-js'] ) ) {
			unset( $actions['inline hide-if-no-js'] );
		}

		return parent::row_actions( $actions, true );
	}
}