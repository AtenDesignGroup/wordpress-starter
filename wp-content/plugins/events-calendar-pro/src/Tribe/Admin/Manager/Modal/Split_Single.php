<?php
/**
 * Split Upcoming modal prompt
 *
 * @since   5.9.0
 *
 * @package Tribe\Events\Pro\Admin\Manager\Modal
 */

namespace Tribe\Events\Pro\Admin\Manager\Modal;

/**
 * Class Modal
 *
 * @package Tribe\Events\Pro\Admin\Manager\Modal
 *
 * @since 5.9.0
 */
class Split_Single extends Split_Abstract {
	/**
	 * Split type (single or upcoming).
	 *
	 * @since 5.9.0
	 *
	 * @var string
	 */
	protected $type = 'single';

	/**
	 * Get the modal's title.
	 *
	 * @since 5.9.0
	 *
	 * @return string
	 */
	protected function get_title() {
		return esc_html__( 'You are about to break this event out of its series.', 'tribe-events-calendar-pro' );
	}
}