<?php
/**
 * Handles the modifications to the Single Event template in Classic and Blocks editor context.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Templates
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Templates;

use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events_Pro\Custom_Tables\V1\Models\Provisional_Post;
use TEC\Events_Pro\Custom_Tables\V1\Templates\Templates as Templates_Loader;
use WP_Error;
use WP_Post;
use WP_Term;
use WP_Term_Query;

/**
 * Class Single_Event_Modifications
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Templates
 */
class Single_Event_Modifications {
	/**
	 * A reference to an instance of the template loader.
	 *
	 * @since 6.0.0
	 *
	 * @var Templates_Loader
	 */
	private $templates;

	/**
	 * A reference to the provisional post handler.
	 *
	 * @since 6.0.0
	 *
	 * @var \TEC\Events_Pro\Custom_Tables\V1\Models\Provisional_Post
	 */
	private $provisional_post;

	/**
	 * A map from a list of post IDs to the hidden status of the Recurrence tooltip.
	 *
	 * @since 6.0.0
	 *
	 * @var array<int,bool>
	 */
	private $hidden_recurrence_tooltips = [];

	/**
	 * Single_Event_Modifications constructor.
	 *
	 * @since 6.0.0
	 *
	 * @param Templates        $templates        A reference to the templates loader the instance should use.
	 * @param Provisional_Post $provisional_post A reference to the provisional posts handler the class
	 *                                           should use to resolve provisional post IDs.
	 */
	public function __construct( Templates_Loader $templates, Provisional_Post $provisional_post ) {
		$this->templates        = $templates;
		$this->provisional_post = $provisional_post;
	}

	/**
	 * Returns the HTML code that will print the Series Relationship marker text on the page
	 * if the Event is in a relation with a Series.
	 *
	 * @since 6.0.0
	 *
	 * @param int $id The post ID of the Event to print the marker for.
	 *
	 * @return string The HTML markup code for the Series relationship marker.
	 */
	public function get_series_relationship_text_marker( $id ) {
		$id          = $this->normalize_post_id( $id );
		$series_post = tec_event_series( $id );

		if ( ! $series_post instanceof WP_Post ) {
			// The relation is there, but the Series post cannot be fetched: removed, cap?
			return '';
		}

		$event = get_post( $id );

		if ( ! tec_should_show_series_title( $series_post, $event ) ) {
			// If should not show series title, return early as we do not show text marker without series title.
			return '';
		}

		$context = [
			'event'                     => $event,
			'series_relationship_label' => _x( 'Event Series:', 'Series relationship marker prefix, with colon.', 'tribe-events-calendar-pro' ),
			'series_title'              => apply_filters( 'the_title', $series_post->post_title, $series_post->ID ),
			'series_link'               => get_post_permalink( $series_post->ID ),
		];

		$html = $this->templates->template( 'single/series-relationship-marker', $context, false );

		return $html;
	}

	/**
	 * Returns the HTML code that will print the Series Relationship marker pill on the page
	 * if the Event is in a relation with a Series.
	 *
	 * @since 6.0.0
	 *
	 * @param int $id The post ID of the Event to print the marker for.
	 *
	 * @return string The HTML markup code for the Series relationship marker.
	 */
	public function get_series_relationship_pill_marker( $id ) {
		$id          = $this->normalize_post_id( $id );
		$series_post = tec_event_series( $id );

		if ( ! $series_post instanceof WP_Post ) {
			// The relation is there, but the Series post cannot be fetched: removed, cap?
			return '';
		}

		$event = get_post( $id );

		if ( tec_should_show_series_title( $series_post, $event ) ) {
			// If should show series title, return early as we do not show pill marker with series title.
			return '';
		}

		$context = [
			'event'                     => $event,
			'series_relationship_label' => _x( 'Event Series', 'Series relationship marker prefix, w/o colon.', 'tribe-events-calendar-pro' ),
			'series_title'              => _x( '(See All)', 'Parenthetical text for the link to the entire series.', 'tribe-events-calendar-pro' ),
			'series_link'               => get_post_permalink( $series_post->ID ),
			'modifier'                  => 'pill',
		];

		$html = $this->templates->template( 'single/series-relationship-marker', $context, false );

		return $html;
	}

	/**
	 * Returns the HTML code that will print the Series Relationship icon on the page
	 * if the Event is in a relation with a Series.
	 *
	 * @since 6.0.0
	 *
	 * @param int $id The post ID of the Event to print the icon for.
	 *
	 * @return string The HTML markup code for the Series relationship icon.
	 */
	public function get_series_relationship_icon( $id ) {
		$id    = $this->normalize_post_id( $id );
		$event = get_post( $id );

		if ( ! $event instanceof WP_Post ) {
			return '';
		}

		$context = [
			'event' => $event,
		];

		$html = $this->templates->template( 'components/series-relationship-icon-link-pill', $context, false );

		return $html;
	}

	/**
	 * Normalizes the post ID to one that will map to an actual post ID.
	 *
	 * @since 6.0.0
	 *
	 * @param int $id The post ID to normalize.
	 *
	 * @return int The normalized post ID.
	 */
	public function normalize_post_id( $id ) {
		if ( $this->provisional_post->is_provisional_post_id( $id ) ) {
			$normalized_event_id = $this->provisional_post->normalize_provisional_post_id( $id );
			$occurrence          = Occurrence::find( $normalized_event_id, 'occurrence_id' );

			if ( ! $occurrence instanceof Occurrence ) {
				// Maybe there is a temporary redirection for the Occurrence.
				$destination = get_transient( "_tec_events_occurrence_{$id}_redirect" );

				return (int) $destination;
			}

			/** @var Occurrence $occurrence */
			return $occurrence->post_id;
		}

		return $id;
	}

	/**
	 * Adds filters to the template system to prevent the rendering of some templates
	 * targeted by the class.
	 *
	 * @since 6.0.0
	 *
	 * @return void The method will add filters in the Templates class as a side-effect.
	 */
	public function do_not_render_recurring_marker() {
		add_filter( 'tribe_template_done', [ $this, 'do_not_render' ], 10, 2 );
	}

	/**
	 * Adds filter to prevent rendering of the recurrence tooltip in classic editor.
	 *
	 * @since 6.0.0
	 *
	 * @param int $id The post ID of the event.
	 *
	 * @return void
	 */
	public function do_not_render_recurring_info_tooltip( $id ) {
		$this->hidden_recurrence_tooltips[ $id ] = true;

		if ( has_filter( 'tribe_events_recurrence_tooltip', [ $this, 'filter_recurrence_tooltip' ] ) ) {
			return;
		}

		add_filter( 'tribe_events_recurrence_tooltip', [ $this, 'filter_recurrence_tooltip' ], 10, 2 );
	}

	/**
	 * Prevents the rendering of templates as a consequence of the rendering of a template
	 * handled by the class.
	 *
	 * @since 6.0.0
	 *
	 * @param null|bool   $done A value indicating whether the template rendering was handled or
	 *                          not. Defaults to `null` to indicate the template should render as
	 *                          usual.
	 * @param string|null $name The name of the template currently being rendered.
	 *
	 * @return string|null Either a non-null value to prevent the rendering of a template, or
	 *                     `null` to let the template render.
	 */
	public function do_not_render( $done = null, $name = null ) {
		if ( $name !== 'single-event/recurring-description' ) {
			// Let the template render.
			return $done;
		}

		// We're done, let's remove the filtering function.
		remove_filter( 'tribe_template_done', [ $this, 'do_not_render' ] );

		// Returning `true` , or any non-null, value here will prevent the template from rendering.
		return true;
	}

	/**
	 * Filters the Recurring Event tooltip to remove it if the Event is related to a Series.
	 *
	 * This method is really not a good approach: ideally, the tooltip should not even run the
	 * logic to produce the HTML, doing it and silencing is not a good way to do this.
	 * Yet: it's what we have now.
	 *
	 * @todo  update PRO to allow bailing out of the tooltip logic entirely.
	 *
	 * @since 6.0.0
	 *
	 * @param string $tooltip The current tooltip HTML code.
	 * @param int    $post_id The current post ID.
	 *
	 * @return string The filtered tooltip HTML.
	 */
	public function filter_recurrence_tooltip( $tooltip, $post_id ) {
		return empty( $this->hidden_recurrence_tooltips[ $post_id ] ) ? $tooltip : '';
	}

	/**
	 * Include Series HTML for the meta details.
	 *
	 * @since 6.0.0
	 *
	 * @return string The location of the new file
	 */
	public function include_series_meta_details() {
		$post = get_post();
		if ( ! $post instanceof WP_Post ) {
			return;
		}

		// The actual ID of the event is a fake one, make sure to use the real post ID.
		if ( isset( $post->_tec_occurrence ) && $post->_tec_occurrence instanceof Occurrence ) {
			$event_id = $post->_tec_occurrence->post_id;
		} else {
			$event_id = $post->ID;
		}

		$series = tec_event_series( $event_id );
		if ( ! $series instanceof WP_Post ) {
			return;
		}
		?>
		<dt class="tec-events-pro-series-meta-detail--label"><?php esc_html_e( 'Series:', 'tribe-events-calendar-pro' ); ?> </dt>
		<dd class="tec-events-pro-series-meta-detail--link">
			<a
				title="<?php echo esc_attr( get_the_title( $series->ID ) ); ?>"
				href="<?php echo esc_url( get_the_permalink( $series ) ); ?>"
			>
				<?php echo get_the_title( $series->ID ); ?>
			</a>
		</dd>
		<?php
	}

	/**
	 * When a provisional post ID is looking for categories or tags we need to redirect to the original post ID
	 * instead as is the relationship of any taxonomy is done at the real `post_id` level instead.
	 *
	 * @see   get_the_terms()
	 *
	 * @since 6.0.0
	 *
	 * @param WP_Term[]|WP_Error|false $terms    The list of terms found
	 * @param int                      $post_id  The current post ID being affected
	 * @param string                   $taxonomy The name of the taxonomy
	 *
	 * @return WP_Error|WP_Term[] The list of the terms found from the real post ID or the actual value if is not a provisional post ID.
	 */
	public function redirect_get_the_terms( $terms, $post_id, $taxonomy ) {
		// Terms already was setup, nothing to do.
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			return $terms;
		}

		// This is not an occurrence ID nothing to do over here.
		if ( ! tribe( Provisional_Post::class )->is_provisional_post_id( $post_id ) ) {
			return $terms;
		}

		$post = get_post( $post_id );

		if ( ! $post instanceof WP_Post ) {
			return $terms;
		}

		if ( ! isset( $post->_tec_occurrence ) && ! $post->_tec_occurrence instanceof Occurrence ) {
			return $terms;
		}

		return get_the_terms( $post->_tec_occurrence->post_id, $taxonomy );
	}

	/**
	 * When a call to `get_terms` is made, make sure to redirect the terms request from the provisional ID to the
	 * original post ID.
	 *
	 * @since 6.0.0
	 *
	 * @param array      $terms      Array of found terms.
	 * @param array|null $taxonomies An array of taxonomies.
	 * @param array      $args       An array of get_terms() arguments (term query_vars).
	 *
	 * @return  array An array with the terms found.
	 * @see   get_terms()
	 */
	public function redirect_get_terms( $terms, $taxonomies, $args ): array {
		if ( ! ( is_array( $terms ) && is_array( $taxonomies ) && is_array( $args ) ) ) {
			return $terms;
		}

		// This was already populated, move on.
		if ( ! empty( $terms ) ) {
			return $terms;
		}

		// No object ids was provided, nothing to find for us here.
		if ( empty( $args['object_ids'] ) || ! is_array( $args['object_ids'] ) ) {
			return $terms;
		}

		$normal_ids     = [];
		$redirected_ids = [];

		$this->provisional_post->hydrate_caches( $args['object_ids'] );

		foreach ( $args['object_ids'] as $id ) {
			// Use the post as the place to get the ID as it might be cached already.
			$post = get_post( $id );
			if ( $post instanceof WP_Post && isset( $post->_tec_occurrence ) && $post->_tec_occurrence instanceof Occurrence ) {
				$redirected_ids[] = $post->_tec_occurrence->post_id;
			} else {
				$normal_ids[] = $id;
			}
		}

		// This call does not have any ID that was redirect no need to call again to the same function.
		if ( empty( $redirected_ids ) ) {
			return $terms;
		}

		$args['object_ids'] = array_merge( $normal_ids, $redirected_ids );

		// Recursive call after the terms has been populated with the right post ID.
		return get_terms( $args );
	}
}
