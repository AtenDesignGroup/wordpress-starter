<?php
/**
 * Models the Occurrence custom table in the context of the ECP plugin.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Tables
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Tables;

use TEC\Events\Custom_Tables\V1\Schema_Builder\Abstract_Custom_Field;
use TEC\Events\Custom_Tables\V1\Tables\Occurrences as OccurrencesSchema;

/**
 * Class Occurrences
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Tables
 */
class Occurrences extends Abstract_Custom_Field {
	const SCHEMA_VERSION_OPTION = 'tec_ct1_occurrences_field_schema_version';
	const SCHEMA_VERSION = '1.0.1';

	/**
	 * @inheritDoc
	 */
	public function fields() {
		return [
			'has_recurrence',
			'sequence',
			'is_rdate'
		];
	}

	/**
	 * {@inheritdoc}
	 *
	 * This table SQL is differential in respect to the one defined in
	 * the TEC version of the table.
	 *
	 * @see OccurrencesSchema::get_update_sql()
	 */
	protected function get_update_sql() {
		global $wpdb;
		$table_name      = $this->table_schema()::table_name( true );
		$charset_collate = $wpdb->get_charset_collate();

		return "CREATE TABLE `{$table_name}` (
			`has_recurrence` boolean DEFAULT FALSE,
            `sequence` bigint(20) unsigned DEFAULT 0,
            `is_rdate` boolean DEFAULT FALSE
			) {$charset_collate};";
	}

	/**
	 * @inheritDoc
	 */
	public function table_schema() {
		return tribe( OccurrencesSchema::class );
	}
}
