<?php
/**
 * Manages the templates for the Series post type.
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Templates
 */

namespace TEC\Events_Pro\Custom_Tables\V1\Templates;

use Tribe__Template as Base_Template;

/**
 * Class Templates
 *
 * @since   6.0.0
 *
 * @package TEC\Events_Pro\Custom_Tables\V1\Templates
 */
class Templates extends Base_Template {

	/**
	 * Templates constructor.
	 *
	 * Builds and sets up the Template to work with the plugin templates.
	 *
	 * @since 6.0.0
	 */
	public function __construct() {
		$this->set_template_origin( \Tribe__Events__Pro__Main::instance() );
		$this->set_template_folder( '/src/views/custom-tables-v1' );
		$this->set_template_folder_lookup( true );
		$this->set_template_context_extract( true );

		// Set some defaults we might use over and over.
		$series_relationship_label = _x( 'Event Series:', 'The text shown before indicating the Series the Event is in relation with.', 'tribe-events-calendar-pro' );
		$this->set( 'series_relationship_label', $series_relationship_label );

		// @todo remove this when our Customizer will expose presentational classes.
		$this->set( 'fg_accent_color_class', 'tribe-events-ical' );
	}

	/**
	 * Returns the Series post type singular template path.
	 *
	 * @since 6.0.0
	 *
	 * @return false|string Either the Series post type singular template
	 *                      path, or `false` if it could not be found.
	 */
	public function get_series_singular_path() {
		return $this->get_template_file( 'single-series' );
	}
}
