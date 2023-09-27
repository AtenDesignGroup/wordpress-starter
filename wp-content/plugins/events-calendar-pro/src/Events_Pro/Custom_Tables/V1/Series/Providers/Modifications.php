<?php
/**
 * Handle the registration and hooking for series single view and single series view on the admin.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Series\Providers
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Series\Providers;

use TEC\Events_Pro\Custom_Tables\V1\Series\Post_Type as Series;
use TEC\Events_Pro\Custom_Tables\V1\Updates\Controller;

use Tribe\Events\Views\V2\View_Interface;
use Tribe\Utils\Theme_Compatibility;
use TEC\Common\Contracts\Service_Provider;

/**
 * Class Modifications
 *
 * Series provider to apply modifications to the single series view.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Series\Providers
 */
class Modifications extends Service_Provider {

	/**
	 * Register the callbacks or actions used on this service provider.
	 *
	 * @since 6.0.0
	 */
	public function register() {
		// Body classes for the single view of a series template.
		add_filter( 'body_class', [ $this, 'should_attach_body_classes' ] );
		add_filter( 'post_updated_messages', [ $this, 'series_updated_messages' ] );
		// Callback executed in any of the following filters.
		$disable_filter_bar_callback = [ $this, 'disable_filter_bar' ];
		// Filter Bar vertical
		add_filter( 'tribe_events_filter_bar_views_v2_should_display_filters', $disable_filter_bar_callback, 10, 2 );
		// Filter Bar Horizontal
		add_filter( 'tribe_events_filter_bar_views_v2_1_should_display_filters', $disable_filter_bar_callback, 10, 2 );
		// Redirect single view in some cases
		add_action( 'template_redirect', [ $this, 'redirect_single_view' ] );
	}

	/**
	 * Handle scenarios a single view page should be redirected elsewhere.
	 *
	 * @since 6.0.0
	 */
	public function redirect_single_view() {
		tribe( Controller::class )->redirect_single_view();
	}

	/**
	 * Prevent to render filter bar (horizontal/vertical) in the context of single series view.
	 *
	 * @since 6.0.0
	 *
	 * @param bool           $should_display_filters Boolean on whether to display filters or not.
	 * @param View_Interface $view                   The View currently rendering.
	 */
	public function disable_filter_bar( $should_display_filters, View_Interface $view ) {
		$related_series = $view->get_context()->get( 'related_series', 0 );

		// If the related series is not present let's the normal flow to continue otherwise prevent to render the template.
		return empty( $related_series ) ? $should_display_filters : false;
	}

	/**
	 * Make sure all the required compatibility class are injected into the body so all CSS applied to fix compatibility
	 * issues with the different themes is presented at the body level. Specifically this section applies the classes into
	 * the singular view of the series page.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string> $classes An array with all the existing classes to be applied to the body.
	 *
	 * @return array<string> An array with all the existing classes to be applied to the body.
	 */
	public function should_attach_body_classes( $classes ) {
		if ( ! is_singular( Series::POSTTYPE ) ) {
			return $classes;
		}

		if ( ! Theme_Compatibility::is_compatibility_required() ) {
			return $classes;
		}

		return array_merge( Theme_Compatibility::get_compatibility_classes(), $classes );
	}

	/**
	 * Populate updated messages array with the messages for the series post type so the custom edit messages for the
	 * series post type can be found.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string, array<string>> $messages An associative array with the set of  messages for post types.
	 *
	 * @return array<string, array<string>> $messages An associative array with the set of  messages for post types.
	 */
	public function series_updated_messages( $messages ) {
		if ( ! is_array( $messages ) ) {
			return $messages;
		}

		global $post, $post_ID;

		$post_ID   = isset( $post_ID ) ? (int) $post_ID : 0;
		$permalink = get_permalink( $post_ID );

		// View post link.
		$view_post_link_html = sprintf(
			' <a href="%1$s">%2$s</a>',
			esc_url( $permalink ),
			__( 'View Series', 'tribe-events-calendar-pro' )
		);

		// Scheduled post preview link.
		$scheduled_post_link_html = sprintf(
			' <a target="_blank" href="%1$s">%2$s</a>',
			esc_url( $permalink ),
			__( 'Preview Series', 'tribe-events-calendar-pro' )
		);

		$preview_url = get_preview_post_link( $post );

		// Preview post link.
		$preview_post_link_html = sprintf(
			' <a target="_blank" href="%1$s">%2$s</a>',
			esc_url( $preview_url ),
			__( 'Preview Series', 'tribe-events-calendar-pro' )
		);

		$scheduled_date = sprintf(
		/* translators: Publish box date string. 1: Date, 2: Time. */
			__( '%1$s at %2$s', 'tribe-events-calendar-pro' ),
			/* translators: Publish box date format, see https://www.php.net/manual/datetime.format.php */
			date_i18n( _x( 'M j, Y', 'publish box date format', 'tribe-events-calendar-pro' ), strtotime( $post->post_date ) ),
			/* translators: Publish box time format, see https://www.php.net/manual/datetime.format.php */
			date_i18n( _x( 'H:i', 'publish box time format', 'tribe-events-calendar-pro' ), strtotime( $post->post_date ) )
		);

		$series_singular_label = tribe( Series::class )->get_label_singular();

		$messages[ Series::POSTTYPE ] = [
			0  => '', // Unused. Messages start at index 1.
			1  => sprintf( __( '%s updated.', 'tribe-events-calendar-pro' ), $series_singular_label ) . $view_post_link_html,
			2  => __( 'Custom field updated.', 'tribe-events-calendar-pro' ),
			3  => __( 'Custom field deleted.', 'tribe-events-calendar-pro' ),
			4  => sprintf( __( '%s updated.', 'tribe-events-calendar-pro' ), $series_singular_label ),
			/* translators: %s: Date and time of the revision. */
			5  => isset( $_GET['revision'] ) ? sprintf( __( '%s restored to revision from %s.', 'tribe-events-calendar-pro' ), $series_singular_label, wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => sprintf( __( '%s published.', 'tribe-events-calendar-pro' ), $series_singular_label ) . $view_post_link_html,
			7  => sprintf( __( '%s saved.', 'tribe-events-calendar-pro' ), $series_singular_label ),
			8  => sprintf( __( '%s submitted.', 'tribe-events-calendar-pro' ), $series_singular_label ) . $preview_post_link_html,
			/* translators: %s: Scheduled date for the post. */
			9  => sprintf( __( '%s scheduled for: %s.', 'tribe-events-calendar-pro' ), $series_singular_label, '<strong>' . $scheduled_date . '</strong>' ) . $scheduled_post_link_html,
			10 => sprintf( __( '%s draft updated.', 'tribe-events-calendar-pro' ), $series_singular_label ) . $preview_post_link_html,
		];

		return $messages;
	}
}
