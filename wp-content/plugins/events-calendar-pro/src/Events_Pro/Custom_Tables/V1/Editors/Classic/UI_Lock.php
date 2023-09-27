<?php
/**
 * ${CARET}
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Editors\Classic;
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Editors\Classic;

use TEC\Events\Custom_Tables\V1\Models\Occurrence as Occurrence_Model;
use TEC\Events_Pro\Custom_Tables\V1\Models\Occurrence;
use Tribe__Template as Template;
use WP_Post;

/**
 * Class UI_Lock.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Editors\Classic;
 */
class UI_Lock {
	/**
	 * The provisional or post ID.
	 *
	 * @since 6.0.0
	 *
	 * @var numeric
	 */
	private $event_id;

	/**
	 * A reference to the ECP Template handler.
	 *
	 * @since 6.0.0
	 *
	 * @var Template
	 */
	private $template;

	/**
	 * A reference to the ECP Occurrence extender.
	 *
	 * @since 6.0.0
	 *
	 * @var Occurrence
	 */
	private $occurrence;

	/**
	 * UI_Lock constructor.
	 *
	 * since 6.0.0
	 *
	 * @param numeric    $event_id   A provisional or post ID.
	 * @param Template   $template   A reference to the ECP Template handler.
	 * @param Occurrence $occurrence A reference to the ECP Occurrence extender.
	 */
	public function __construct( $event_id, Template $template, Occurrence $occurrence ) {
		$this->event_id = $event_id;
		$this->template = $template;
		$this->occurrence = $occurrence;
	}

	/**
	 * Prints the notice HTML to the page.
	 *
	 * @since 6.0.0
	 *
	 * @return void The notice HTML is printed to the page.
	 */
	public function print_notice(): void {
		echo $this->get_notice();
	}

	/**
	 * Retrieves the dynamic parts of the notice message.
	 *
	 * @since 6.0.0
	 *
	 * @return array
	 */
	public function get_notice_parts(): array {
		$post_id = $this->occurrence->normalize_occurrence_post_id( $this->event_id );
		$title   = get_post( $post_id )->post_title;

		// So we don't get the wrong ID filtered in the edit link.
		$force_our_id = static function ( $id ) use ( $post_id ) {
			return $post_id;
		};
		add_filter( 'tec_events_pro_custom_tables_v1_redirect_id', $force_our_id );
		$parts = [
			'url'  => get_edit_post_link( $post_id, 'url' ),
			'name' => $title
		];
		remove_filter( 'tec_events_pro_custom_tables_v1_redirect_id', $force_our_id );

		return $parts;
	}


	/**
	 * Returns the notice HTML for the current request.
	 *
	 * @since 6.0.0
	 *
	 * @return string The notice HTML.
	 */
	public function get_notice(): string {
		if ( ! $this->lock_rules_ui() ) {
			return '';
		}

		$notice_parts = $this->get_notice_parts();
		$link         = sprintf( '<a href="%1$s">%2$s</a>', $notice_parts['url'], $notice_parts['name'] );

		return $this->template->template(
			'custom-tables-v1/rdate-occurrence-ui-locked',
			[
				// translators: The placeholder is a link to the editable event.
				'link' => $link,
			], false );
	}

	/**
	 * Determines if the recurrence rules UI should be locked.
	 *
	 * @since 6.0.0
	 *
	 * @return bool Whether the recurrence rules UI should be locked or not.
	 */
	public function lock_rules_ui(): bool {
		$post = get_post( $this->event_id );

		if ( empty( $post->_tec_occurrence->is_rdate ) ) {
			return false;
		}

		return ! $this->is_first( $post->_tec_occurrence );
	}

	/**
	 * Determines if the recurrence exclusions UI should be locked.
	 *
	 * @since 6.0.0
	 *
	 * @return bool Whether the recurrence exclusions UI should be locked or not.
	 */
	public function lock_exclusions_ui(): bool {
		return $this->lock_rules_ui();
	}

	/**
	 * Whether an Occurrence is the first in the context of a Recurring Event or not.
	 *
	 * @since 6.0.0
	 *
	 * @param Occurrence_Model $occurrence The Occurrence to check.
	 *
	 * @return bool Whether the Occurrence is the first in the context of a Recurring Event or not.
	 */
	private function is_first( Occurrence_Model $occurrence ): bool {
		$cache = tribe_cache();
		$cache_key = 'ui_lock_is_first_occurrence_' . $occurrence->occurrence_id;

		if ( isset( $cache[ $cache_key ] ) ) {
			return $cache[ $cache_key ];
		}

		$post_id = $occurrence->post_id;
		$first = Occurrence_Model::where( 'post_id', '=', $post_id )
			->order_by( 'start_date', 'ASC' )
			->first();
		$first_occurrence_id = $first instanceof Occurrence_Model ? $first->occurrence_id : null;

		$is_first = $first_occurrence_id === $occurrence->occurrence_id;
		$cache[ $cache_key ] = $is_first;

		return $is_first;
	}
}