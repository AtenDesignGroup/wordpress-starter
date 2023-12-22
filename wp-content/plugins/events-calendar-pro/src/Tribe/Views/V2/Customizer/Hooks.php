<?php
/**
 * Handles hooking all the actions and filters used by the module.
 *
 * To remove a filter:
 * remove_filter( 'some_filter', [ tribe( Tribe\Events\Views\Pro\V2\Customizer\Hooks::class ), 'some_filtering_method' ] );
 * remove_filter( 'some_filter', [ tribe( 'views.v2.customizer.filters' ), 'some_filtering_method' ] );
 *
 * To remove an action:
 * remove_action( 'some_action', [ tribe( Tribe\Events\Views\Pro\V2\Customizer\Hooks::class ), 'some_method' ] );
 * remove_action( 'some_action', [ tribe( 'views.v2.customizer.hooks' ), 'some_method' ] );
 *
 * @since 5.8.0
 *
 * @package Tribe\Events\Views\Pro\V2\Customizer
 */

namespace Tribe\Events\Pro\Views\V2\Customizer;

use Tribe\Events\Pro\Views\V2\Customizer\Section\Events_Bar;
use Tribe\Events\Pro\Views\V2\Customizer\Section\Global_Elements;
use TEC\Common\Contracts\Service_Provider;

/**
 * Class Hooks
 *
 * @since 5.8.0
 *
 * @package Tribe\Events\Views\Pro\V2\Customizer
 */
class Hooks extends Service_Provider {


	/**
	 * Binds and sets up implementations.
	 *
	 * @since 5.8.0
	 */
	public function register() {
		$this->add_filters();
	}

	/**
	 * Adds the filters required by each Pro Views v2 component.
	 *
	 * @since 5.8.0
	 */
	public function add_filters() {
		add_filter( 'tribe_customizer_section_events_bar_content_controls', [ $this, 'filter_events_bar_content_controls'], 12, 2 );
		add_filter( 'tribe_customizer_section_events_bar_content_settings', [ $this, 'filter_events_bar_content_settings'], 12, 2 );
		add_filter( 'tribe_customizer_section_events_bar_css_template', [ $this, 'filter_events_bar_css_template'], 12, 2 );
		add_filter( 'tribe_customizer_section_events_bar_default_settings', [ $this, 'filter_events_bar_default_settings'], 12, 2 );
		add_filter( 'tribe_customizer_section_global_elements_content_controls', [ $this, 'filter_global_elements_content_controls'], 10, 2 );
		add_filter( 'tribe_customizer_section_global_elements_content_settings', [ $this, 'filter_global_elements_content_settings'], 10, 2 );
		add_filter( 'tribe_customizer_section_global_elements_css_template', [ $this, 'filter_global_elements_css_template'], 10, 2 );
	}

	/**
	 * Filters the Events Bar defaults to add default view selector settings.
	 *
	 * @since 5.8.0
	 *
	 * @param array<string|mixed>        $arguments The existing array of default values.
	 * @param Tribe__Customizer__Section $section   The section instance we are dealing with.
	 *
	 * @return array<string|mixed> The modified array of default values.
	 */
	public function filter_events_bar_default_settings( $arguments, $section ) {
		return $this->container->make( Events_Bar::class )->filter_events_bar_default_settings( $arguments, $section );
	}

	/**
	 * Filters the Events Bar settings to add view selector settings.
	 *
	 * @since 5.8.0
	 *
	 * @param array<string|mixed>        $arguments The existing array of default values.
	 * @param Tribe__Customizer__Section $section   The section instance we are dealing with.
	 *
	 * @return array<string|mixed> The modified array of settings.
	 */
	public function filter_events_bar_content_settings( $arguments, $section ) {
		return $this->container->make( Events_Bar::class )->filter_events_bar_content_settings( $arguments, $section );
 	}

	 /**
	 * Filters the Events Bar controls to add view selector controls.
	 *
	 * @since 5.8.0
	 *
	 * @param array<string|mixed>        $arguments The existing array of default values.
	 * @param Tribe__Customizer__Section $section   The section instance we are dealing with.
	 *
	 * @return array<string|mixed> The modified array of controls.
	 */
	public function filter_events_bar_content_controls( $arguments, $section ) {
		return $this->container->make( Events_Bar::class )->filter_events_bar_content_controls( $arguments, $section );
	}

	/**
	 * Filters the Events Bar CSS output to add view selector styles.
	 *
	 * @since 5.8.0
	 *
	 * @param string                     $arguments The existing CSS output string.
	 * @param Tribe__Customizer__Section $section   The section instance we are dealing with.
	 *
	 * @return string The modified CSS output.
	 */
	public function filter_events_bar_css_template( $arguments, $section ) {
		return $this->container->make( Events_Bar::class )->filter_events_bar_css_template( $arguments, $section );
	}

	/**
	 * Filters the Events Bar settings to add view selector settings.
	 *
	 * @since 5.9.0
	 *
	 * @param array<string|mixed>        $arguments The existing array of default values.
	 * @param Tribe__Customizer__Section $section   The section instance we are dealing with.
	 *
	 * @return array<string|mixed> The modified array of settings.
	 */
	public function filter_global_elements_content_settings( $arguments, $section ) {
		return $this->container->make( Global_Elements::class )->filter_global_elements_content_settings( $arguments, $section );
 	}

	 /**
	 * Filters the Events Bar controls to add view selector controls.
	 *
	 * @since 5.9.0
	 *
	 * @param array<string|mixed>        $arguments The existing array of default values.
	 * @param Tribe__Customizer__Section $section   The section instance we are dealing with.
	 *
	 * @return array<string|mixed> The modified array of controls.
	 */
	public function filter_global_elements_content_controls( $arguments, $section ) {
		return $this->container->make( Global_Elements::class )->filter_global_elements_content_controls( $arguments, $section );
	}

	/**
	 * Filters the Global Elements CSS output to ECP-specific styles.
	 *
	 * @since 5.9.0
	 *
	 * @param array                      $arguments The existing array of default values.
	 * @param Tribe__Customizer__Section $section   The section instance we are dealing with.
	 *
	 * @return array<string|mixed> The modified array of default values.
	 */
	public function filter_global_elements_css_template( $arguments, $section ) {
		return $this->container->make( Global_Elements::class )->filter_global_elements_css_template( $arguments, $section );
	}
}
