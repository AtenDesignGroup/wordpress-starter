<?php
/**
 * Models the link to break-out an Occurrence from the Recurring Event and edit it as a Single Event.
 *
 * @since   6.0.1
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Updates\Post_Links;
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Updates\Post_Links;

use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events_Pro\Custom_Tables\V1\Updates\Updates;

/**
 * Class Single_Edit_Post_Link.
 *
 * @since   6.0.1
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Updates\Post_Links;
 */
class Single_Edit_Post_Link {
	/**
	 * A reference to the Occurrence model instance to build the link for.
	 *
	 * @since 6.0.1
	 *
	 * @var Occurrence
	 */
	private $occurrence;

	/**
	 * Single_Edit_Post_Link constructor.
	 *
	 * since 6.0.1
	 *
	 * @param Occurrence $occurrence The Occurrence model instance to build the link for.
	 */
	public function __construct( Occurrence $occurrence ) {
		$this->occurrence = $occurrence;
	}

	/**
	 * Returns the link to break-out an Occurrence from the Recurring Event and edit it as a Single Event.
	 *
	 * @since 6.0.1
	 *
	 * @return string The link to break-out an Occurrence from the Recurring Event and edit it as a Single Event.
	 */
	public function __toString(): string {
		return $this->get_link();
	}

	/**
	 * Returns the link to break-out an Occurrence from the Recurring Event and edit it as a Single Event.
	 *
	 * @since 6.0.1
	 *
	 * @return string The link to break-out an Occurrence from the Recurring Event and edit it as a Single Event.
	 */
	public function get_link(): string {
		return add_query_arg(
			[
				'post'               => $this->occurrence->provisional_id,
				'action'             => 'edit',
				Updates::REQUEST_KEY => Updates::SINGLE,
				'nonce'              => wp_create_nonce( 'tec_edit_link' )
			],
			admin_url( 'post.php' )
		);
	}
}