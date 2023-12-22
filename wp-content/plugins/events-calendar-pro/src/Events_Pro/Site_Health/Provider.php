<?php
// tec_debug_info_sections
/**
 * Service Provider for interfacing with tec-common Site Health.
 *
 * @since 6.1.0
 *
 * @package TEC\Events_Pro\Site_Health
 */

 namespace TEC\Events_Pro\Site_Health;

 use TEC\Common\Contracts\Service_Provider;

 /**
  * Class Site_Health
  *
  * @since 6.1.0

  * @package TEC\Events_Pro\Site_Health
  */
 class Provider extends Service_Provider {

	/**
	 * Internal placeholder to pass around the section slug.
	 *
	 * @since 6.1.0
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * Register our service provider.
	 *
	 * @since 6.1.0
	 *
	 * @return void
	 */
	public function register() {
		$this->slug = Info_Section::get_slug();
		$this->add_actions();
		$this->add_filters();
	}

	/**
	 * Add the action hooks.
	 *
	 * @since 6.1.0
	 */
	public function add_actions() {
		// no op.
	}

	/**
	 * Add the filter hooks.
	 *
	 * @since 6.1.0
	 */
	public function add_filters() {
		add_filter( 'tec_debug_info_sections', [ $this, 'filter_include_sections' ] );
	}

	/**
	 * This builds the Info_Section object and adds it to the Site Health screen.
	 *
	 * @since 6.1.0
	 *
	 * @param array $sections The array of sections to be displayed.
	 */
	public function filter_include_sections( $sections ) {
		$sections[ Info_Section::get_slug() ] = $this->container->make( Info_Section::class );

		return $sections;
	}

 }
