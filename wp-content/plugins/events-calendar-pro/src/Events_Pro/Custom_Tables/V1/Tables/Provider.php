<?php
/**
 * Handles the creation, update and registration of code related with ECP version of the Custom Tables.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Tables
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Tables;

use TEC\Events\Custom_Tables\V1\Schema_Builder\Abstract_Schema_Provider;

/**
 * Class Provider
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Tables
 */
class Provider extends Abstract_Schema_Provider {

	/**
	 * @inheritDoc
	 */
	public static function get_field_schemas() {
		return [
			tribe( Events::class ),
			tribe( Occurrences::class )
		];
	}

	/**
	 * @inheritDoc
	 */
	public static function get_table_schemas() {
		return [
			tribe( Series_Relationships::class ),
		];
	}

}
