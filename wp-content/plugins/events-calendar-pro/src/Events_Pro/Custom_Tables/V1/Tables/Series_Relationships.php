<?php
/**
 * Models the Series to Events relationship custom table.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Tables
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Tables;

use TEC\Events\Custom_Tables\V1\Schema_Builder\Abstract_Custom_Table;

/**
 * Class Series
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Tables
 */
class Series_Relationships extends Abstract_Custom_Table {
	/**
	 * @inheritDoc
	 */
	const SCHEMA_VERSION_OPTION = 'tec_ct1_series_relationship_table_schema_version';

	/**
	 * @inheritDoc
	 */
	const SCHEMA_VERSION = '1.0.0';

	/**
	 * @inheritDoc
	 */
	public static function base_table_name() {
		return 'tec_series_relationships';
	}

	/**
	 * {@inheritDoc}
	 */
	public static function group_name() {
		return 'ecp';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_update_sql() {
		global $wpdb;
		$table_name      = self::table_name( true );
		$charset_collate = $wpdb->get_charset_collate();

		return "CREATE TABLE `{$table_name}` (
			`relationship_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`series_post_id` bigint(20) unsigned NOT NULL,
			`event_id` bigint(20) unsigned NOT NULL,
			`event_post_id` bigint(20) unsigned NOT NULL,
			PRIMARY KEY `relationship_id` (`relationship_id`),
			KEY `series_post_id` (`series_post_id`),
			KEY `event_post_id` (`event_post_id`)
			) {$charset_collate};";
	}

	/**
	 * {@inheritdoc}
	 */
	public static function uid_column() {
		return 'relationship_id';
	}
}
