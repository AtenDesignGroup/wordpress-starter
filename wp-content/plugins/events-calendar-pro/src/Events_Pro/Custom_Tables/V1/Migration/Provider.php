<?php
/**
 * Handles the registration of classes, implementations, and filters for Migration activities.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Migration
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Migration;

use TEC\Events\Custom_Tables\V1\Migration\Migration_Exception;
use TEC\Events\Custom_Tables\V1\Migration\Reports\Event_Report;
use TEC\Events\Custom_Tables\V1\Migration\Strategies\Strategy_Interface;
use TEC\Events\Custom_Tables\V1\Migration\String_Dictionary as TEC_String_Dictionary;
use TEC\Events\Custom_Tables\V1\Provider_Contract;
use TEC\Events_Pro\Custom_Tables\V1\Migration\Reports\Event_Report_Categories;
use TEC\Events_Pro\Custom_Tables\V1\Migration\Strategy\Migration_Message_Override;
use TEC\Events_Pro\Custom_Tables\V1\Migration\Strategy\Migration_Strategy_Guide;
use TEC\Events_Pro\Custom_Tables\V1\Migration\Strategy\Multi_Rule_Event_Migration_Strategy;
use TEC\Common\Contracts\Service_Provider;

/**
 * Class Provider
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Migration
 */
class Provider extends Service_Provider implements Provider_Contract {
	/**
	 * Binds and sets up implementation.
	 *
	 * @since 6.0.0
	 */
	public function register() {
		// Make the provider available in the container.
		$this->container->singleton( self::class, $this );

		$this->container->singleton( String_Dictionary::class, String_Dictionary::class );
		$this->container->singleton( Event_Report_Categories::class, Event_Report_Categories::class );

		add_action( 'tec_events_custom_tables_v1_migration_before_cancel', [ $this, 'before_cancel_process_worker' ] );
		add_action( 'tec_events_custom_tables_v1_migration_completed', [ $this, 'flush_permalinks' ] );
		add_action( 'tec_events_custom_tables_v1_migration_completed', [ $this, 'queue_telemetry_report' ] );
		add_action( Telemetry::ACTION_NAME, [ $this, 'send_telemetry_report' ] );

		add_filter( 'tec_events_custom_tables_v1_migration_strings', [ $this, 'filter_strings_map' ] );

		add_filter( 'tec_events_custom_tables_v1_migration_event_report_categories', [
			$this,
			'add_categories'
		], 10, 2 );

		add_filter( 'tec_events_custom_tables_v1_migration_strategy', [ $this, 'strategy_guide' ], 10, 3 );

		add_filter(
			'tec_events_custom_tables_v1_migration_strategy_text_override_' . Multi_Rule_Event_Migration_Strategy::get_slug(),
			[ $this, 'overwrite_multi_rule_message' ], 10, 2
		);

		add_action( 'tec_events_custom_tables_v1_before_migration_applied', [
			$this,
			'before_migration_applied'
		], 10, 4 );

		add_action( 'tec_events_custom_tables_v1_migration_after_cancel', [
			$this,
			'restore_events_recurrence_meta'
		] );
	}

	/**
	 * Before we finalize a cancellation/undo worker, let's do our Pro specific cleanup. We need to remove Series posts
	 * and related data to avoid orphans.
	 *
	 * @since 6.0.0
	 */
	public function before_cancel_process_worker() {
		$this->container->make( Process_Worker_Service::class )->remove_series();
	}

	/**
	 * Does some cleanup before we do migration.
	 *
	 * @since 6.0.0
	 *
	 * @param Event_Report       $event_report
	 * @param Strategy_Interface $strategy
	 * @param numeric            $post_id
	 * @param bool               $dry_run
	 */
	public function before_migration_applied( $event_report, $strategy, $post_id, $dry_run ) {
		$this->container->make( Process_Worker_Service::class )->before_migration_applied( $event_report, $strategy, $post_id, $dry_run );
	}

	/**
	 * Will add a custom event report message, in order to apply multi rule only vars.
	 *
	 * @since 6.0.0
	 *
	 * @param string|null  $message      The message.
	 * @param Event_Report $event_report The event report which the override will apply to.
	 *
	 * @return string
	 */
	public function overwrite_multi_rule_message( $message, $event_report ) {
		return $this->container->make( Migration_Message_Override::class )
		                       ->overwrite_multi_rule_message( $message, $event_report );
	}

	/**
	 * Unhooks from actions and filters the methods hooked in the `register` method.
	 *
	 * @since 6.0.0
	 *
	 * @return void The method does not return any value and will have the side effect
	 *              of unhooking the actions and filters added by the provider in the
	 *              `register` method.
	 */
	public function unregister() {
		remove_filter(
			'tec_events_custom_tables_v1_migration_strategy_text_override_' . Multi_Rule_Event_Migration_Strategy::get_slug(),
			[ $this, 'overwrite_multi_rule_message' ]
		);

		remove_filter(
			'tec_events_custom_tables_v1_migration_event_report_categories',
			[ $this, 'add_categories' ]
		);

		remove_action( 'tec_events_custom_tables_v1_migration_before_cancel', [
			$this,
			'before_cancel_process_worker'
		] );

		remove_action( 'tec_events_custom_tables_v1_before_migration_applied', [ $this, 'before_migration_applied' ] );
		remove_action( 'tec_events_custom_tables_v1_migration_after_cancel', [
			$this,
			'restore_events_recurrence_meta'
		] );
	}

	/**
	 * Will delegate which migration strategies to use and instantiate
	 * and return that instance responsible for applying the migration logic.
	 *
	 * @since 6.0.0
	 *
	 * @param Strategy_Interface|null $current_strategy A reference to the migration
	 *                                                  strategy proposed for the Event
	 *                                                  by previous filtering code.
	 * @param int                     $post_id          The post ID of the Event to migrate.
	 * @param bool                    $dry_run          Whether the migration should run in preview (dry-run) mode
	 *                                                  or not.
	 *
	 * @throws Migration_Exception If there's an issue building the migration strategy.
	 */
	public function strategy_guide( $current_strategy, $post_id, $dry_run ) {
		return $this->container->make( Migration_Strategy_Guide::class )
		                       ->strategy_loader( $current_strategy, $post_id, $dry_run );
	}

	/**
	 * Filters the strings map used by The Events Calendar in the context of the Migration
	 * UI.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,string> $map The map of the strings used by TEC in the context of
	 *                                  the Migration UI.
	 *
	 * @return array<string,string> The filtered map from slugs to the localized version of
	 *                              the strings.
	 */
	public function filter_strings_map( array $map = [] ) {
		return $this->container->make( String_Dictionary::class )->filter_map( $map );
	}

	/**
	 * Add the PRO migration event report categories.
	 *
	 * @since 6.0.0
	 *
	 * @param array<array{ key:string, label:string }> $categories The report categories.
	 * @param TEC_String_Dictionary                    $text       The string translation object.
	 *
	 * @return array<array{ key:string, label:string }>
	 */
	public function add_categories( $categories, $text ) {
		return $this->container->make( Event_Report_Categories::class )->add_categories( $categories, $text );
	}

	/**
	 * Flush permalinks for Events Pro on migration events.
	 *
	 * @since 6.0.0
	 */
	public function flush_permalinks() {
		flush_rewrite_rules();
	}

	/**
	 * After the migration cancellation completed, restore the previous version
	 * of the `_EventRecurrence` meta stored in the `_EventRecurrenceBackup`
	 * meta value.
	 *
	 * @since 6.0.0
	 *
	 * @return void The method does not return any value and will have the side effect
	 *              of restoring the value of the `_EventRecurrence` meta to the value
	 *              of the `_EventRecurrenceBackup` meta if present.
	 */
	public function restore_events_recurrence_meta(  ) {
		$this->container->make( Process_Worker_Service::class )->restore_event_recurrence_meta();
	}

	/**
	 * Queue an action to send the migration report when a preview is completed.
	 *
	 * @since 6.0.0
	 *
	 * @param bool|null $dry_run Whether the migration is in preview mode or not.
	 *
	 * @return bool Whether the telemetry report dispatch was queued or not.
	 */
	public function queue_telemetry_report( ?bool $dry_run ): bool {
		$dry_run = $dry_run ?? false;

		if ( ! Telemetry::is_enabled( $dry_run ) ) {
			return false;
		}

		// The report operation might require some time, let's do that in another process.
		return $this->container->make( Telemetry::class )->queue( $dry_run );
	}

	/**
	 * Hook on the Action Scheduler action to send the report.
	 *
	 * @since 6.0.0
	 *
	 * @param bool $dry_run Whether the migration is in preview mode or not.
	 */
	public function send_telemetry_report( bool $dry_run ): void {
		if ( ! Telemetry::is_enabled( $dry_run ) ) {
			return;
		}

		$this->container->make( Telemetry::class )->send();
	}
}
