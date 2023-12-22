<?php
/**
 * Hooks on the WordPress IDENTIFY, WRITE and READ phases to update
 * an Event and all its Occurrences.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Updates\Update_Controllers
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Updates\Update_Controllers;

use DateTimeZone;
use TEC\Events_Pro\Custom_Tables\V1\Events\Provisional\ID_Generator;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events_Pro\Custom_Tables\V1\Events\Rules\Date_Rule;
use TEC\Events_Pro\Custom_Tables\V1\Models\Provisional_Post;
use TEC\Events_Pro\Custom_Tables\V1\RRule\Occurrence as RRule_Occurrence;
use TEC\Events_Pro\Custom_Tables\V1\Updates\Controller;
use TEC\Events_Pro\Custom_Tables\V1\Updates\Events;
use Tribe__Events__Pro__Editor__Recurrence__Blocks;
use WP_Post;
use TEC\Events_Pro\Custom_Tables\V1\Updates\Transient_Occurrence_Redirector as Occurrence_Redirector;
use Tribe__Events__Pro__Editor__Recurrence__Blocks_Meta as Blocks_Meta;
use Tribe__Date_Utils as Dates;

/**
 * Class All
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Updates\Update_Controllers
 */
class All implements Update_Controller_Interface {
	use Update_Controller_Methods;

	/**
	 * The target post ID.
	 *
	 * @since 6.0.0
	 *
	 * @var int
	 */
	private $target_id;

	/**
	 * The request start date, in `Y-m-d H:i:s` format.
	 *
	 * @since 6.0.0
	 *
	 * @var string
	 */
	private $request_start_date;

	/**
	 * A reference to the current Provision Post handler implementation.
	 *
	 * @since 6.0.0
	 *
	 * @var Provisional_Post
	 */
	private $provisional_post;

	/**
	 * A reference to the Events repository.
	 *
	 * @since 6.0.0
	 *
	 * @var Events
	 */
	private $events;

	/**
	 * A reference to the current implementation of the Occurrence redirector.
	 *
	 * @since 6.0.0
	 *
	 * @var Occurrence_Redirector
	 */
	private $occurrence_redirector;

	/**
	 * All constructor.
	 *
	 * @since 6.0.0
	 *
	 * @param Provisional_Post      $provisional_post      A reference to the current Provision Post handler
	 *                                                     implementation.
	 * @param Events                $events                A reference to the current Events repository handler.
	 * @param Occurrence_Redirector $occurrence_redirector A reference to the current implementation of the Occurrence
	 *                                                     redirector.
	 */
	public function __construct(
		Provisional_Post $provisional_post,
		Events $events,
		Occurrence_Redirector $occurrence_redirector
	) {
		$this->provisional_post = $provisional_post;
		$this->events           = $events;
		$this->occurrence_redirector = $occurrence_redirector;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 6.0.0
	 */
	public function apply_before_identify_step( $post_id ) {
		if ( false === $this->check_step_requirements( $post_id ) ) {
			return false;
		}

		// Never redirect an RDATE Occurrence request if it's the first Occurrence or the real post ID.
		if (
			$this->occurrence->is_rdate
			&& ! Occurrence::is_first( $this->occurrence )
			&& $this->provisional_post->is_provisional_post_id( $post_id )
		) {
			$first = Occurrence::where( 'post_id', '=', $this->occurrence->post_id )
				->order_by( 'start_date', 'ASC' )
				->first();

			if ( $first instanceof Occurrence ) {
				$this->redirect_rdate_update_to_occurrence( $this->occurrence, $first, $first->post_id );
			}
		}

		$this->save_request_id( $post_id );

		$target_id        = $this->occurrence->post_id;

		if ( empty( $target_id ) ) {
			$target_id = $post_id;
		}

		$this->target_id = $target_id;

		if ( null !== $this->occurrence->occurrence_id ) {
			// This branch will be taken for new posts.
			$this->request_start_date = $this->occurrence->start_date;
			$adjusted_dates = $this->events->adjust_request_dates( $this->request, $this->occurrence );
			foreach ( $adjusted_dates as $key => $value ) {
				$_REQUEST[ $key ] = $value;
				$_POST [ $key ]   = $value;
			}
		}

		$this->save_rest_request_recurrence_meta( $target_id, $this->request );
		// Set the context for an occurrence redirect.
		tribe( Controller::class )->set_should_redirect_occurrence( true );

		return $target_id;
	}
}
