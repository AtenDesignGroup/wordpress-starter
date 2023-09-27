<?php
/**
 * The Pro Event Status service provider.
 *
 * @package Tribe\Events\Pro\Event_Status
 * @since   5.10.0
 */

namespace Tribe\Events\Pro\Event_Status;

use Tribe\Events\Event_Status\Template;
use Tribe\Events\Event_Status\Template_Modifications;
use TEC\Common\Contracts\Service_Provider;

/**
 * Class Event_Status_Provider
 *
 * @since   5.10.0
 *
 * @package Tribe\Events\Pro\Event_Status
 */
class Event_Status_Provider extends Service_Provider {

	const DISABLED = 'TEC_EVENT_STATUS_DISABLED';

	/**
	 * Binds and sets up implementations.
	 */
	public function register() {
		if ( ! self::is_active() ) {
			return false;
		}

		// Register the SP on the container
		$this->container->singleton( 'events.pro.status.provider', $this );

		$this->add_templates();
	}

	/**
	 * Returns whether the event status should register, thus activate, or not.
	 *
	 * @since 5.10.0
	 *
	 * @return bool Whether the event status should register or not.
	 */
	public static function is_active() {
		if ( defined( self::DISABLED ) && constant( self::DISABLED ) ) {
			// The disable constant is defined and it's truthy.
			return false;
		}

		if ( getenv( self::DISABLED ) ) {
			// The disable env var is defined and it's truthy.
			return false;
		}

		/**
		 * Allows filtering whether the event status should be activated or not.
		 *
		 * Note: this filter will only apply if the disable constant or env var
		 * are not set or are set to falsy values.
		 *
		 * @since 5.10.0
		 *
		 * @param bool $activate Defaults to `true`.
		 */
		return (bool) apply_filters( 'tec_event_status_enabled', true );
	}

	/**
	 * Adds the templates for event status.
	 *
	 * @since 5.10.0
	 */
	protected function add_templates() {
		$label_templates = [
			// Photo View.
			'events-pro/v2/photo/event/title:after_container_open',
			// Week View.
			'events-pro/v2/week/grid-body/events-day/event/title:after_container_open',
			'events-pro/v2/week/grid-body/events-day/event/tooltip/title:after_container_open',
			'events-pro/v2/week/grid-body/multiday-events-day/multiday-event/bar/title:after_container_open',
			'events-pro/v2/week/grid-body/multiday-events-day/multiday-event/hidden/link/title:after_container_open',
			'events-pro/v2/week/mobile-events/day/event/title:after_container_open',
			// Map View.
			'events-pro/v2/map/event-cards/event-card/event/title:after_container_open',
			'events-pro/v2/map/event-cards/event-card/tooltip/title:after_container_open',
			// Summary View.
			'events-pro/v2/summary/date-group/event/title:after_container_open',
			// Featured Venue Widget.
			'events-pro/v2/widgets/widget-featured-venue/events-list/event/title:after_container_open',
		];

		/**
		 * Filters the list of template where the event status label is added.
		 *
		 * @since 5.10.0
		 *
		 * @param array<string> $label_templates The array of template names for each view to add the status label.
		 */
		$label_templates = apply_filters( 'tec_pro_event_status_templates', $label_templates );

		foreach ( $label_templates as $template ) {
		    if ( ! is_string( $template ) ) {
	            continue;
	        }

			add_filter(
				'tribe_template_entry_point:' . $template,
				[ $this, 'filter_insert_status_label' ],
				15,
				3
			);
		}
	}

	/**
	 * Inserts Status Label to views.
	 *
	 * @since 5.10.0
	 *
	 * @param string   $hook_name        For which template include this entry point belongs.
	 * @param string   $entry_point_name Which entry point specifically we are triggering.
	 * @param Template $template         Current instance of the Template.
	 */
	public function filter_insert_status_label( $hook_name, $entry_point_name, $template ) {
		return $this->container->make( Template_Modifications::class )->insert_status_label( $hook_name, $entry_point_name, $template );
	}
}
