<?php
/**
 * Handle the registration and hooking for custom modifications for specific themes.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Series\Providers
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Series\Providers;


use TEC\Events_Pro\Custom_Tables\V1\Series\Post_Type as Series;
use TEC\Common\Contracts\Service_Provider;

/**
 * Class Theme_Compatibility
 *
 * Series provider to apply modifications to specific themes.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Series\Providers
 */
class Theme_Compatibility extends Service_Provider {

	/**
	 * Register the callbacks or actions used on this service provider.
	 *
	 * @since 6.0.0
	 */
	public function register() {
		// twenty twenty
		add_filter(
			'twentytwenty_post_meta_location_single_top',
			[
				$this,
				'supported_post_meta_location_single_top',
			]
		);

		// Avada
		add_filter( 'fusion_post_metadata_markup', [ $this, 'avada_metadata' ] );
		add_filter( 'fusion_get_page_option_override', [ $this, 'avada_override_author_display' ], 10, 3 );
		// Genesis
		add_action( 'genesis_before_loop', [ $this, 'genesis_removal_of_author_box' ] );
	}

	/**
	 * Remove the author and post date from the single series view by making sure the theme removes the meta information
	 * from the template when rendering the single view of the template.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string> $meta An array with all the properties rendered at the top bar on the single view.
	 *
	 * @return array<string> An array with the allowed meta properties to be rendered in the array.
	 */
	public function supported_post_meta_location_single_top( $meta ) {
		if ( ! $this->is_singular_series() ) {
			return $meta;
		}

		// Remove post-date and author key, values are numeric indexed.
		return array_filter(
			$meta,
			static function ( $key ) {
				return $key !== 'post-date' && $key !== 'author';
			}
		);
	}

	/**
	 * On the context of a request for the single series page avoid rendering the metadata.
	 *
	 * @since 6.0.0
	 *
	 * @param string $html The markup to be rendered as the metadata.
	 *
	 * @return string The markup to be rendered as the metadata.
	 */
	public function avada_metadata( $html ) {
		return $this->is_singular_series() ? '' : $html;
	}

	/**
	 * Override the setting to display the author or not, for single series view.
	 *
	 * @since 6.0.0
	 *
	 * @param mixed  $override    Override the value from the settings, if the value returned is other than null.
	 * @param int    $post_id     The current post ID.
	 * @param string $page_option THe name of the option being affected.
	 *
	 * @return mixed If the returned value is any other than null the value would be overridden.
	 */
	public function avada_override_author_display( $override, $post_id, $page_option ) {
		if ( $page_option !== 'author_info' ) {
			return $override;
		}

		if ( ! tribe( Series::class )->is_same_type( get_post( $post_id ) ) ) {
			return $override;
		}

		return 'no';
	}

	/**
	 * Action in charge of removing the post_info with author and post date details for genesis template, if this is not
	 * inside  the singular series view the action is removed in order to avoid repeated execution of this hook
	 * when is not required.
	 *
	 * @since 6.0.0
	 */
	public function genesis_removal_of_author_box() {
		if ( $this->is_singular_series() ) {
			remove_action( 'genesis_entry_header', 'genesis_post_info', 12 );
		} else {
			remove_action( 'genesis_before_loop', [ $this, 'genesis_removal_of_author_box' ] );
		}
	}

	/**
	 * If the current query is happening on the single view of a series post type.
	 *
	 * @since 6.0.0
	 *
	 * @return bool `true` if the current query is being executed inside the single series view, `false` otherwise.
	 */
	private function is_singular_series() {
		return is_singular( Series::POSTTYPE );
	}
}
