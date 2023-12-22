<?php
/**
 * Handles the Custom Tables integration with ECP version of Views v2.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Views\V2
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Views\V2;

use TEC\Events\Custom_Tables\V1\Views\V2\By_Day_View_Compatibility as TEC_By_Day_View_Compatibility;
use TEC\Events_Pro\Custom_Tables\V1\Models\Series_Relationship;
use TEC\Events_Pro\Custom_Tables\V1\Templates\Templates;
use Tribe\Events\Views\V2\View;
use Tribe__Events__Pro__Main as Plugin;
use TEC\Common\Contracts\Service_Provider;
use Tribe__Template;
use WP_Post;

/**
 * Class Provider
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Views\V2
 */
class Provider extends Service_Provider {

	/**
	 * Key for the event archive group of assets.
	 *
	 * @since 6.0.0
	 *
	 * @var string
	 */
	public static $event_archive_group_key = 'tec-custom-tables-v1-views-v2-event-archive';

	/**
	 * Registers the handlers and modifiers required to make the plugin correctly work
	 * with Views v2.
	 *
	 * @since 6.0.0
	 */
	public function register() {
		// Hook late to replace the template files that might need replacing.
		add_filter( 'tribe_template_file', [ $this, 'replace_template_files' ], 50, 3 );

		// Replace the TEC implementation of the TEC By Day View compatibility class with an ECP geared one.
		$this->container->singleton( TEC_By_Day_View_Compatibility::class, By_Day_View_Compatibility::class );

		$this->register_assets();

		// Hook late to replace the template files that might need replacing.
		add_filter( 'tribe_template_file', [ $this, 'replace_template_files' ], 100, 3 );
		add_filter( 'tribe_template_context_get', [ $this, 'override_template_context_get' ], 10, 2 );

		add_action( 'tec_events_views_v2_after_get_events', [ $this, 'prime_series_relationship_cache' ] );
		add_filter( 'tec_events_prime_cache_post_ids', [ $this, 'include_series_input_posts_prime_cache' ], 10, 2 );
	}

	/**
	 * Registers the assets required by the service provider.
	 *
	 * @since 6.0.0
	 */
	public function register_assets() {
		$plugin = Plugin::instance();

		tribe_asset(
			$plugin,
			'tec-events-pro-archives-style',
			'custom-tables-v1/archives.css',
			[ 'tec-variables-full' ],
			null,
			[
				'priority' => 200,
				'groups'   => [ static::$event_archive_group_key ],
			]
		);
	}

	/**
	 * For Views V2 we need to prime the Series relationship cache based on which events we are pulling.
	 *
	 * @since 6.0.0
	 *
	 * @param array|WP_Post $events Which events were just selected.
	 */
	public function prime_series_relationship_cache( $events ) {
		Series_Relationship::prime_cache( (array) $events );
	}

	/**
	 * For each Event to prime the cache for, include the related Series.
	 *
	 * @since 6.0.0
	 *
	 * @param array<int>         $post_ids The set of post IDs to prime the cache for.
	 * @param array<int|WP_Post> $posts    The set of Event posts to prime the cache for.
	 *
	 * @return array<int> The set of post IDs to prime the cache for.
	 */
	public function include_series_input_posts_prime_cache( array $post_ids, array $posts ): array {
		// Prime the cache first.
		$all = Series_Relationship::prime_cache( $posts );

		$series_ids = wp_list_pluck( (array) $all, 'series_post_id' );

		if ( empty( $series_ids ) ) {
			return $post_ids;
		}

		return array_merge( $post_ids, $series_ids );
	}

	/**
	 * Replaces templates files with ones managed by the plugin.
	 *
	 * @since 6.0.0
	 *
	 * @param string          $file     The path to the file the template system resolved.
	 * @param array<string>   $name     The template name fragments.
	 * @param Tribe__Template $template A reference to the currently resolving template instance.
	 *
	 * @return string The template path, modified if required.
	 */
	public function replace_template_files( string $file, array $name, Tribe__Template $template ): string {
		$template_name = implode( '/', $name );

		$redirection_map = [
			'list/event/recurring'                                                    => '/components/series-relationship-marker-link.php',
			'widgets/widget-events-list/event/date/recurring'                         => '/components/series-relationship-icon-link.php',
			'photo/event/date-time/recurring'                                         => '/components/series-relationship-icon-link.php',
			'summary/date-group/event/date/recurring'                                 => '/components/series-relationship-icon-link.php',
			'month/mobile-event/recurring'                                            => '/components/series-relationship-icon-link.php',
			'month/calendar-event/multiday/recurring'                                 => '/components/series-relationship-icon.php',
			'month/calendar-event/tooltip/recurring'                                  => '/components/series-relationship-icon-link.php',
			'month/calendar-event/recurring'                                          => '/components/series-relationship-icon-link.php',
			'week/mobile-events/day/event/date/recurring'                             => '/components/series-relationship-icon-link.php',
			'week/grid-body/events-day/event/date/recurring'                          => '/components/series-relationship-icon.php',
			'week/grid-body/events-day/event/tooltip/date/recurring'                  => '/components/series-relationship-icon-link.php',
			'week/grid-body/multiday-events-day/multiday-event/bar/recurring'         => '/components/series-relationship-icon.php',
			'week/grid-body/multiday-events-day/multiday-event/hidden/link/recurring' => '/components/series-relationship-icon.php',
			'map/event-cards/event-card/event/date-time/recurring'                    => '/components/series-relationship-icon-link.php',
			'map/event-cards/event-card/tooltip/date-time/recurring'                  => '/components/series-relationship-icon-link.php',
			'day/event/recurring'                                                     => '/components/series-relationship-marker-link.php',
			'recurrence/hide-recurring'                                               => '/recurrence/hide-recurring.php',
			'widgets/widget-featured-venue/events-list/event/date/recurring'          => '/components/series-relationship-icon-link.php',
		];

		if ( empty( $redirection_map[ $template_name ] ) ) {
			return $file;
		}

		$root = EVENTS_CALENDAR_PRO_DIR . '/src/views/custom-tables-v1';

		// Note down the original file, should we need to render that.
		$template->set( 'original_file', $file );

		tribe_asset_enqueue_group( static::$event_archive_group_key );

		return $root . $redirection_map[ $template_name ];
	}

	/**
	 * Override the value from within the template when calling the context `template->get()` in order to prevent
	 * rendering the toggle inside the single series view. We can't use `single()` or `singular()` due we are inside the
	 * events loop at the time, so we use the context to find if a `related_series` key exists if that's the case we
	 * override the value with a `false` value.
	 *
	 * @since  6.0.0
	 *
	 * @param mixed        $value The value from within the context.
	 * @param array|string $index The key we are currently at.
	 */
	public function override_template_context_get( $value, $index ) {
		if ( $index !== 'display_recurring_toggle' ) {
			return $value;
		}

		// If the current context is for the single series view prevent to render the toggle.
		return tribe_context()->get( 'related_series' ) ? false : $value;
	}
}
