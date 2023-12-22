<?php
/**
 * Service Provider for Linked_Posts functionality.
 *
 * @since 6.2.0
 *
 * @package TEC\Events_Pro\Linked_Posts
 */

namespace TEC\Events_Pro\Linked_Posts;

use TEC\Common\Contracts\Provider\Controller as Controller_Contract;
use TEC\Events_Pro\Linked_Posts\Venue;


use WP_Post;

/**
 * Class Provider
 *
 * @since 6.2.0

 * @package TEC\Events_Pro\Linked_Posts
 */
class Controller extends Controller_Contract {
	/**
	 * Register the controller.
	 *
	 * @since 6.2.0
	 */
	public function do_register(): void {
		$this->container->singleton( Thumbnail_Inclusion::class );
		$this->container->register( Organizer\Controller::class );
		$this->container->register( Venue\Controller::class );

		$this->add_actions();
		$this->add_filters();
	}

	/**
	 * Unregister the controller.
	 *
	 * @since 6.2.0
	 *
	 * @return void
	 */
	public function unregister(): void {
		$this->remove_actions();
		$this->remove_filters();
	}

	/**
	 * Add the filter hooks.
	 *
	 * @since 6.2.0
   */
	public function add_actions(): void {

	}

	/**
	 * Remove the action hooks.
	 *
	 * @since 6.2.0
	 */
	public function remove_actions(): void {

	}

	/**
	 * Unregister the controller.
	 *
	 * @since 6.2.0
	 */
	public function add_filters(): void {
		add_filter( 'tribe_get_organizer_object', [ $this, 'include_thumbnail_in_object' ], 15, 3 );
		add_filter( 'tribe_get_venue_object', [ $this, 'include_thumbnail_in_object' ], 15, 3 );
		add_filter( 'admin_post_thumbnail_html', [ $this, 'include_helper_text_post_metabox' ], 15 );
	}

	/**
	 * Remove the filter hooks.
	 *
	 * @since 6.2.0
	 */
	public function remove_filters(): void {
		remove_filter( 'tribe_get_organizer_object', [ $this, 'include_thumbnail_in_object' ], 15 );
		remove_filter( 'tribe_get_venue_object', [ $this, 'include_thumbnail_in_object' ], 15 );
		remove_filter( 'admin_post_thumbnail_html', [ $this, 'include_helper_text_post_metabox' ], 15 );
	}

	/**
	 * Include the thumbnail in an object of Linked Post.
	 *
	 * @since 6.2.0
	 *
	 * @param WP_Post $post   The organizer post object, decorated with a set of custom properties.
	 * @param string  $output The output format to use.
	 * @param string  $filter The filter, or context of the fetch.
	 *
	 * @return mixed
	 */
	public function include_thumbnail_in_object( $post, $output, $filter ) {
		if ( ! $post instanceof WP_Post ) {
			return $post;
		}
		return $this->container->make( Thumbnail_Inclusion::class )->include_thumbnail( $post, $output, $filter );
	}

	/**
	 * Filters the `admin_post_thumbnail_html` to add image aspect ratio recommendation.
	 *
	 * @since 6.2.0
	 *
	 * @param string $html The HTML for the featured image box.
	 *
	 * @return string The modified html, if required.
	 */
	public function include_helper_text_post_metabox( string $html ): string {
		return $this->container->make( Thumbnail_Inclusion::class )->include_helper_text_post_metabox( $html );
	}
}
