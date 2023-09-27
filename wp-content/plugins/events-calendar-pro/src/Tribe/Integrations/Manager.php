<?php

use Tribe\Events\Pro\Integrations\Elementor\Service_Provider as Elementor_Integration;
use Tribe\Events\Pro\Integrations\Fusion\Service_Provider as Fusion_Integration;
use Tribe__Events__Pro__Integrations__Beaver_Builder__Page_Builder as BB_Page_Builder;
use Tribe\Events\Pro\Integrations\Brizy_Builder\Service_Provider as Brizy_Builder;

/**
 * Class Tribe__Events__Pro__Integrations__Manager
 *
 * Loads and manages the third-party plugins integration implementations.
 */
class Tribe__Events__Pro__Integrations__Manager {

	/**
	 * @var Tribe__Events__Pro__Integrations__Manager
	 */
	protected static $instance;

	/**
	 * The class singleton constructor.
	 *
	 * @return Tribe__Events__Pro__Integrations__Manager
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Loads WPML integration classes and event listeners.
	 *
	 * @return bool
	 */
	private function load_wpml_integration() {
		if ( ! ( class_exists( 'SitePress' ) && defined( 'ICL_PLUGIN_PATH' ) ) ) {
			return false;
		}

		Tribe__Events__Pro__Integrations__WPML__WPML::instance()->hook();

		return true;
	}

	/**
	 * Loads WP SEO / WP SEO Premium integration classes and event listeners.
	 *
	 * @since 4.4.14
	 *
	 * @return bool
	 */
	private function load_wpseo_integration() {
		if ( ! defined( 'WPSEO_PREMIUM_FILE' ) ) {
			return false;
		}

		tribe_singleton( 'pro.integrations.wp-seo', 'Tribe__Events__Pro__Integrations__WP_SEO__WP_SEO', array( 'hook' ) );
		tribe( 'pro.integrations.wp-seo' );

		return true;
	}

	/**
	 * Loads Site Origin integration classes and event listeners.
	 *
	 * @since 4.4.29
	 *
	 * @return bool
	 */
	private function load_site_origin_integration() {
		if ( ! class_exists( 'SiteOrigin_Panels' ) ) {
			return false;
		}

		tribe_singleton( 'pro.integrations.site-origin', 'Tribe__Events__Pro__Integrations__Site_Origin__Page_Builder', array( 'hook' ) );
		tribe( 'pro.integrations.site-origin' );


		return true;
	}

	/**
	 * Loads Beaver Builder integration classes and event listeners.
	 *
	 * @since 5.13.1
	 *
	 * @return bool
	 */
	private function load_beaver_builder_integration() {
		if ( ! class_exists( 'FLBuilderLoader' ) ) {
			return false;
		}

		tribe_singleton( 'pro.integrations.beaver-builder', BB_Page_Builder::class, array( 'hook' ) );
		tribe( 'pro.integrations.beaver-builder' );


		return true;
	}


	/**
	 * Conditionally loads the classes needed to integrate with third-party plugins.
	 *
	 * Third-party plugin integration classes and methods will be loaded only if
	 * supported plugins are activated.
	 */
	public function load_integrations() {
		$this->load_wpml_integration();
		$this->load_wpseo_integration();
		$this->load_site_origin_integration();
		$this->load_beaver_builder_integration();
		$this->load_elementor_integration();
		$this->load_fusion_integration();
		$this->load_brizy_builder_integration();
	}

	/**
	 * Loads the Elementor integration if Elementor is currently active.
	 *
	 * @since 5.1.4
	 */
	public function load_elementor_integration() {
		if ( ! defined( 'ELEMENTOR_PATH' ) || empty( ELEMENTOR_PATH ) ) {
			return;
		}

		tribe_register_provider( Elementor_Integration::class );
	}

	/**
	 * Loads the Fusion integration if Fusion Core is currently active.
	 *
	 * @since 5.5.0
	 */
	public function load_fusion_integration() {
		if ( ! defined( 'FUSION_CORE_VERSION' ) || empty( FUSION_CORE_VERSION ) ) {
			return;
		}

		tribe_register_provider( Fusion_Integration::class );
	}

	/**
	 * Loads the Brizy integrations if the Brizy builder plugin is currently active.
	 *
	 * @since 5.14.5
	 */
	public function load_brizy_builder_integration() {
		if ( ! defined( 'BRIZY_FILE' ) || empty( BRIZY_FILE ) ) {
			return;
		}

		tribe_register_provider( Brizy_Builder::class );
	}
}
