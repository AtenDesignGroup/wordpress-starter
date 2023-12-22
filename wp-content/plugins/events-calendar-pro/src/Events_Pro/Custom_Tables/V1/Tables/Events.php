<?php
/**
 * Models the Event custom table in the context of the ECP plugin.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Tables
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Tables;

use TEC\Events\Custom_Tables\V1\Schema_Builder\Abstract_Custom_Field;
use TEC\Events\Custom_Tables\V1\Tables\Events as EventsSchema;

/**
 * Class Events
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Tables
 */
class Events extends Abstract_Custom_Field {
	const SCHEMA_VERSION_OPTION = 'tec_ct1_events_field_schema_version';
	const SCHEMA_VERSION = '1.0.1';

	/**
	 * @inheritDoc
	 */
	public function fields() {
		return [
			'rset'
		];
	}

	/**
	 * {@inheritdoc}
	 *
	 * This table SQL is differential in respect to the one defined in
	 * the TEC version of the table.
	 *
	 * @see EventsSchema::get_update_sql()
	 */
	protected function get_update_sql() {
		global $wpdb;
		$table_name      = $this->table_schema()::table_name( true );
		$charset_collate = $wpdb->get_charset_collate();

		return "CREATE TABLE `{$table_name}` (
			`rset` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL
			) {$charset_collate};";
	}

	/**
	 * @inheritDoc
	 */
	public function table_schema() {
		return tribe( EventsSchema::class );
	}
}
