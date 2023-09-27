<?php
/**
 * Implements a shortcode that wraps the existing event coundtown widget. Basic usage
 * is as follows (using an event's post ID):
 *
 *     [tribe_event_countdown id="123"]
 *
 * If preferred, the event slug can be used:
 *
 *     [tribe_event_countdown slug="some-event"]
 *
 * Display of seconds is optional but can be enabled by adding a show_seconds="1"
 * attribute. To specify the text that should display once the event time rolls round
 * a complete attribute is available.
 *
 *     [tribe_event_countdown slug="party-time" show_seconds="1" complete="The party is on!"]
 */

namespace Tribe\Events\Pro\Views\V2\Shortcodes;

use Tribe\Shortcode\Shortcode_Abstract;
use Tribe\Events\Pro\Views\V2\Widgets\Widget_Countdown;

class Shortcode_Tribe_Event_Countdown extends Shortcode_Abstract {
	/**
	 * {@inheritDoc}
	 */
	protected $slug = 'tribe_event_countdown';

	/**
	 * Default arguments expected by the countdown widget.
	 *
	 * @var array
	 */
	protected $default_arguments = array(
		// General widget properties
		'before_widget' => '',
		'before_title'  => '',
		'title'         => '',
		'after_title'   => '',
		'after_widget'  => '',

		// Widget specific properties
		'event'        => '',
		'slug'         => '',
		'show_seconds' => '',
		'complete'     => '',
	);

	/**
	 * {@inheritDoc}
	 */
	protected $aliased_arguments = [
		'id'       => 'event',
		'event_ID' => 'event',
	];

	/**
	 * {@inheritDoc}
	 */
	public function get_html() {
		$this->maybe_set_event_by_slug();
		$this->set_date();

		$arguments = $this->get_arguments();

		// If we don't have an event date we cannot display the timer
		if ( ! isset( $arguments['event_date'] ) ) {
			return;
		}

		ob_start();

		the_widget( Widget_Countdown::class, $arguments, $arguments );

		$this->content = ob_get_clean();

		return $this->content;
	}

	/**
	 * Facilitates specifying the event by providing its slug.
	 *
	 * @since 6.0.0
	 */
	protected function maybe_set_event_by_slug() {
		if (
			$this->get_argument( 'event' )
			|| empty( $this->get_argument( 'slug' ) )
		) {
			return;
		}

		$event = tribe_events()
			->where( 'name', $this->get_argument( 'slug' ) )
			->per_page( 1 )
			->first();

		if ( ! $event instanceof \WP_Post ) {
			return;
		}

		$this->arguments['event'] = (int) $event->ID;
	}

	/**
	 * The countdown widget requires the date of the event to be passed in
	 * as an argument.
	 */
	protected function set_date() {
		$event = $this->get_argument( 'event' );
		if ( ! $event ) {
			return;
		}

		$this->arguments['event_date'] = tribe_get_start_date( $event, false, \Tribe__Date_Utils::DBDATEFORMAT );
	}
}