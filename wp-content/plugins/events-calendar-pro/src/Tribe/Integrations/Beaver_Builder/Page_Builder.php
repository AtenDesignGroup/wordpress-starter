<?php
/**
 * Compatibility fixes for Beaver Builder Page Builder plugin.
 *
 * @since 5.14.2
 */
class Tribe__Events__Pro__Integrations__Beaver_Builder__Page_Builder {

	/**
	 * Sets up fixes required for improved compatibility with Page Builder.
	 * Move toward allowing loading of admin scripts on front end when BB editor is active.
	 *
	 * @since 5.14.2
	 */
	public function hook() {
		add_action(
			'wp_enqueue_scripts',
			function() {
				if ( FLBuilderModel::is_builder_active() ) {
					add_filter( 'tribe_allow_widget_on_post_page_edit_screen', '__return_true' );
					tribe_asset_enqueue( 'tribe-admin-widget' );

					$plugin = 	Tribe__Events__Pro__Main::instance();

					tribe_asset(
						$plugin,
						'tribe-admin-widget-beaver-builder-compatibility',
						'tec-beaver-builder-compat.css',
						[
							'tribe-admin-widget',
						],
						'wp_print_footer_scripts'
					);
				}
			},
			12
		);
	}
}
