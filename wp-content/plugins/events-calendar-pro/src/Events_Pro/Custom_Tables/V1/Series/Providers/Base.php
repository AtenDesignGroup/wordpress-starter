<?php
/**
 * Manages the registration of the post types used by the plugin.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Series\Providers
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Series\Providers;

use TEC\Events_Pro\Custom_Tables\V1\Series\Post_Type as Series;
use TEC\Common\Contracts\Service_Provider;

use WP_User;

/**
 * Class Provider
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Series\Providers
 */
class Base extends Service_Provider {


	/**
	 * Binds and sets up implementations.
	 *
	 * @since 6.0.0
	 */
	public function register() {
		$this->container->singleton( static::class, $this );

		$this->container->singleton( Series::class );

		add_action( 'init', $this->container->callback( Series::class, 'register_post_type' ), 1 );
		add_action( 'init', [ $this, 'flush_rewrite' ], 100 );

		add_filter( 'get_user_option_meta-box-order_' . Series::POSTTYPE, [ $this, 'reorder_series_meta_boxes' ], 10, 3 );

		add_filter( 'tribe_post_types', [ $this, 'add_series_post_type' ] );
		add_filter( 'tribe_events_linked_post_type_container', [ $this, 'linked_post_type_container' ], 10, 2 );
		add_filter( 'tribe_events_linked_post_name_field_index', [ $this, 'linked_post_name_field_index' ], 10, 2 );
		add_filter( 'tribe_events_linked_post_type_args', [ $this, 'filter_linked_post_type_args' ], 10, 2 );
	}

	/**
	 * Will potentially flush rewrite rules for the new Series post type.
	 *
	 * @since 6.0.0
	 */
	public function flush_rewrite() {
		remove_action( 'init', [ $this, 'flush_rewrite' ] );
		$this->container->make( Series::class )->flush_rewrite();
	}

	/**
	 * By default change the order of the author meta box.
	 *
	 * The dynamic portion of the hook name, `$option`, refers to the user option name.
	 *
	 * @since 2.5.0
	 *
	 * @param mixed   $result Value for the user's option.
	 * @param string  $option Name of the option being retrieved.
	 * @param WP_User $user   WP_User object of the user whose option is being retrieved.
	 */
	public function reorder_series_meta_boxes( $result, $option, $user ) {
		// If this is not an array it means it has not been set already, set the side one as is the value that we are interested in.
		if ( ! is_array( $result ) ) {
			return [
				'normal' => implode( ',', [ 'tec_series_event_relationship', 'tec_series_events_list' ] ),
				'side'   => implode( ',', [ 'submitdiv', 'authordiv', 'tec_series_event_title_display' ] ),
			];
		}

		return $result;
	}

	/**
	 * Filters the list of post types that are considered managed, or owned, by The Events Calendar
	 * to add the Series one.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string> $post_types The input list of post types that are considered managed by
	 *                                  The Events Calendar.
	 *
	 * @return array<string> The filtered list of post types.
	 */
	public function add_series_post_type( array $post_types = [] ) {
		$post_types[] = Series::POSTTYPE;

		return $post_types;
	}

	/**
	 * Use series as post type container
	 *
	 * @since 6.0.0
	 *
	 * @param string $container The post type container.
	 * @param string $post_type The current Post Type.
	 *
	 * @return string
	 */
	public function linked_post_type_container( $container, $post_type ) {
		if ( Series::POSTTYPE === $post_type ) {
			return 'series';
		}

		return $container;
	}

	/**
	 * Use Series as post name field index
	 *
	 * @since 6.0.0
	 *
	 * @param string $name_field The current field name.
	 * @param string $post_type  The current Post Type.
	 *
	 * @return string
	 */
	public function linked_post_name_field_index( $name_field, $post_type ) {
		if ( Series::POSTTYPE === $post_type ) {
			return 'Series';
		}

		return $name_field;
	}

	/**
	 * Filters the linked post type args for the series post type
	 *
	 * @since 6.0.0
	 *
	 * @param array  $args      Array of linked post type arguments
	 * @param string $post_type Linked post type
	 *
	 * @return array
	 */
	public function filter_linked_post_type_args( $args, $post_type ) {
		if ( Series::POSTTYPE !== $post_type ) {
			return $args;
		}

		$args['allow_multiple'] = false;

		return $args;
	}
}
