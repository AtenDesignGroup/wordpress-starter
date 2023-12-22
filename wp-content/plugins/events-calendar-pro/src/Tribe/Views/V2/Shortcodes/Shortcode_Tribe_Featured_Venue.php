<?php
/**
 * Implements a shortcode that wraps the existing featured venue widget. Basic usage
 * is as follows (using a venue's post ID):
 *
 *     [tribe_featured_venue id="123"]
 *
 * Besides supplying the venue ID, a slug can be used. It is also possible to limit
 * the number of upcoming events:
 *
 *     [tribe_featured_venue slug="the-club" limit="5"]
 *
 * A title can also be added if desired:
 *
 *     [tribe_featured_venue slug="busy-location" title="Check out these events!"]
 */
namespace Tribe\Events\Pro\Views\V2\Shortcodes;

use Tribe\Shortcode\Shortcode_Abstract;
use Tribe\Events\Pro\Views\V2\Widgets\Widget_Featured_Venue;

class Shortcode_Tribe_Featured_Venue extends Shortcode_Abstract {
	/**
	 * {@inheritDoc}
	 */
	protected $slug = 'tribe_featured_venue';

	/**
	 * {@inheritDoc}
	 */
	protected $default_arguments = [
		'before_widget' => '',
		'before_title'  => '',
		'title'         => '',
		'after_title'   => '',
		'after_widget'  => '',

		'slug'          => null,
		'title'         => null,
		'count'         => 3,
		'venue_ID'      => null,
		'hide_if_empty' => true,
		'jsonld_enable' => true,
	];

	/**
	 * {@inheritDoc}
	 */
	protected $aliased_arguments = [
		'id'       => 'venue_ID',
		'venue'    => 'venue_ID',
		'limit'    => 'count',
	];

	/**
	 * {@inheritDoc}
	 */
	public function get_html() {
		$this->maybe_set_event_by_slug();
		$this->set_limit();

		$arguments = $this->get_arguments();

		// If no venue has been set simply bail with an empty string
		if ( ! isset( $arguments['venue_ID'] ) ) {
			return;
		}

		ob_start();

		// We use $this->arguments for both the args and the instance vars here
		the_widget( Widget_Featured_Venue::class, $arguments, $arguments );

		$this->content = ob_get_clean();

		return $this->content;
	}

	/**
	 * Venue can be specified with one of "id" or "venue". Limit can be set using a
	 * "count" attribute.
	 *
	 * @since 6.0.0
	 */
	protected function set_limit() {
		if ( strlen( $this->get_argument( 'count' ) ) ) {
			return;
		}

		$this->arguments['count'] = (int) tribe_get_option( 'postsPerPage', 10 );
	}

	/**
	 * Facilitates specifying the venue by providing its slug.
	 *
	 * @since 6.0.0
	 */
	protected function maybe_set_event_by_slug() {
		$slug = $this->get_argument( 'slug' );

		if ( empty( $slug ) ) {
			return;
		}

		$venue = tribe_venues()
			->where( 'name', $slug )
			->per_page( 1 )
			->first();

		if ( ! $venue instanceof \WP_Post ) {
			return;
		}

		$this->arguments['venue_ID'] = (int) $venue->ID;
	}
}