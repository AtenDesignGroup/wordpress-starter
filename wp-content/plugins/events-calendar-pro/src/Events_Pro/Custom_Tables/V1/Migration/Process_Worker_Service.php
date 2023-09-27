<?php

namespace TEC\Events_Pro\Custom_Tables\V1\Migration;

use TEC\Events\Custom_Tables\V1\Migration\Reports\Event_Report;
use TEC\Events\Custom_Tables\V1\Migration\Strategies\Strategy_Interface;
use TEC\Events_Pro\Custom_Tables\V1\Series\Post_Type;

/**
 * Service to handle any Process Worker events for Pro data.
 *
 * @since     6.0.0
 *
 * @pacakge   TEC\Events_Pro\Custom_Tables\V1\Migration
 */
class Process_Worker_Service {

	/**
	 * Before we finalize a cancellation/undo worker, let's do our Pro specific cleanup. We need to remove Series posts
	 * and related data to avoid orphans.
	 *
	 * @since 6.0.0
	 */
	public function remove_series(): void {
		global $wpdb;
		$query = "DELETE {$wpdb->posts}, {$wpdb->postmeta}
			FROM {$wpdb->postmeta}
			INNER JOIN {$wpdb->posts} ON {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID
			WHERE {$wpdb->posts}.post_type = %s";
		$wpdb->query( $wpdb->prepare( $query, Post_Type::POSTTYPE ) );
		// Remove any leftover Series posts in cache.
		wp_cache_flush();
	}

	/**
	 * Does some cleanup before we do migration for this event.
	 *
	 * @since 6.0.0
	 *
	 * @param Event_Report       $event_report The event report.
	 * @param Strategy_Interface $strategy     The strategy to use for this event.
	 * @param numeric            $post_id      The post ID of the event.
	 * @param bool               $dry_run      Whether this is a dry run or not.
	 */
	public function before_migration_applied( Event_Report $event_report, Strategy_Interface $strategy, $post_id, bool $dry_run ): void {
		if ( ! $dry_run ) {
			// Delete any pending recurrence creation for real migrations. These are not needed anymore.
			delete_post_meta( $post_id, '_EventNextPendingRecurrence' );
		}

		$this->update_event_recurrence_meta( $post_id );
	}

	/**
	 * Restore the `_EventRecurrence` meta to the original value if the strategy applied to
	 * an Event modified it.
	 *
	 * @since 6.0.0
	 *
	 * @return void The method does not return any value and will have the side effect of restoring
	 *              the `_EventRecurrence` meta to its pre-migration value.
	 */
	public function restore_event_recurrence_meta() {
		global $wpdb;
		$wpdb->query(
			"UPDATE $wpdb->postmeta pm
					  JOIN $wpdb->postmeta pm_backup
						   ON pm.post_id = pm_backup.post_id
						   AND pm_backup.meta_key = '_EventRecurrenceBackup'
					  SET pm.meta_value = pm_backup.meta_value
					  WHERE pm.meta_key = '_EventRecurrence'"
		);
		$wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key = '_EventRecurrenceBackup'" );
	}

	/**
	 * Updates the format used by the `_EventRecurrence` meta value to make sure it will conform to
	 * the latest version.
	 *
	 * @since 6.0.1
	 *
	 * @param int $post_id The post ID of the event.
	 *
	 * @return void The `_EventRecurrence` meta value will be updated to the latest format, if required.
	 */
	private function update_event_recurrence_meta( int $post_id ): void {
		/**
		 * Filters whether the `_EventRecurrence` meta value should be updated to the latest format before migration.
		 *
		 * @since 6.0.1
		 *
		 * @param bool $do_patch Whether the `_EventRecurrence` meta value should be updated to the latest format
		 *                       before migration.
		 * @param int  $post_id  The post ID of the event being migrated
		 */
		$do_patch = apply_filters( 'tec_events_custom_tables_v1_patch_recurrence_meta_before_migration', true, $post_id );

		if ( ! $do_patch ) {
			return;
		}

		$recurrence_meta = get_post_meta( $post_id, '_EventRecurrence', true );

		if ( empty( $recurrence_meta['rules'] ) ) {
			return;
		}

		$patcher = new Patchers\Event_Recurrence_Meta_Patcher( $recurrence_meta, $post_id );
		update_post_meta( $post_id, '_EventRecurrence', $patcher->patch() );
	}
}