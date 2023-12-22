<?php

namespace TEC\Events_Pro\Linked_Posts\Contracts;

use TEC\Common\Contracts\Provider\Controller;
use Tribe\Events\Pro\Views\V2\Shortcodes\Tribe_Events;
use Tribe\Events\Views\V2\Template;
use Tribe\Events\Views\V2\View;
use Tribe\Shortcode\Shortcode_Abstract;
use Tribe\Utils\Taxonomy;
use Tribe__Events__Rewrite as TEC_Rewrite;
use Tribe__Events__Main as TEC;
use Tribe__Log as Log;
use Tribe__Context as Context;
use Tribe__Utils__Array as Arr;
use Tribe__Repository__Interface as Repository_Interface;

use WP_Taxonomy;
use WP_Error;
use WP_Term;

/**
 * Class Taxonomy_Abstract
 *
 * @since   6.2.0
 *
 * @package TEC\Events_Pro\Linked_Posts\Contracts
 */
abstract class Taxonomy_Abstract extends Controller implements Taxonomy_Interface {
	/**
	 * Determines if this controller will register.
	 * This is present due to how UOPZ works, it will fail we have the boolean living on the method.
	 *
	 * @since 6.2.0
	 */
	protected bool $is_active = true;

	/**
	 * Stores the registered taxonomy object.
	 *
	 * @since 6.2.0
	 *
	 * @var ?WP_Taxonomy
	 */
	protected ?WP_Taxonomy $taxonomy_object = null;

	/**
	 * Stores the admin menu registered page.
	 *
	 * @since 6.2.0
	 *
	 * @var ?string
	 */
	protected ?string $admin_menu = null;

	/**
	 * @inheritDoc
	 */
	public function is_active(): bool {
		return $this->is_active;
	}

	/**
	 * @inheritDoc
	 */
	public function do_register(): void {
		$this->add_filters();
		$this->add_actions();
	}

	/**
	 * @inheritDoc
	 */
	public function unregister(): void {
		$this->remove_filters();
		$this->remove_actions();
	}

	/**
	 * @inheritDoc
	 */
	public function add_actions(): void {
		add_action( 'init', [ $this, 'register_to_wp' ], 15 );

		if ( ! did_action( 'init' ) && ! doing_action( 'init' ) ) {
			// Hook itself to the init action, so we can be sure that the post type is registered.
			add_action( 'init', [ $this, 'add_actions' ], 20 );

			return;
		}

		$configuration = $this->get_configuration();

		// Only deal with admin_menu in case we have an UI to show.
		if ( $configuration['show_ui'] ) {
			add_action( 'admin_menu', [ $this, 'add_submenu_to_wp' ], 15 );
			add_action( 'admin_menu', [ $this, 'modify_admin_menu' ], 50000 );
		}

		// Only deal with Rewrites if we are public.
		if ( $configuration['public'] ) {
			add_action( 'tribe_events_pre_rewrite', [ $this, 'add_rewrites' ], 3 );
		}

		$linked_post_type = $this->get_linked_post_type();
		// Handle the value for each of the new columns.
		add_action( "manage_{$linked_post_type}_posts_custom_column", [ $this, 'render_taxonomy_column_on_linked_post_type_admin_table' ], 10, 2 );

		$repository = $this->get_linked_post_type_repository();
		if ( $repository ) {
			$repository_slug = $repository->get_filter_name();
			add_action( "tribe_repository_{$repository_slug}_init", [ $this, 'include_schemas' ] );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function remove_actions(): void {
		remove_action( 'init', [ $this, 'register_to_wp' ], 15 );
		remove_action( 'init', [ $this, 'add_actions' ], 20 );

		$configuration = $this->get_configuration();

		// Only deal with admin_menu in case we have an UI to show.
		if ( $configuration['show_ui'] ) {
			remove_action( 'admin_menu', [ $this, 'add_submenu_to_wp' ], 15 );
			remove_action( 'admin_menu', [ $this, 'modify_admin_menu' ], 50000 );
		}

		// Only deal with Rewrites if we are public.
		if ( $configuration['public'] ) {
			remove_action( 'tribe_events_pre_rewrite', [ $this, 'add_rewrites' ], 3 );
		}

		$linked_post_type = $this->get_linked_post_type();
		// Handle the value for each of the new columns.
		remove_action( "manage_{$linked_post_type}_posts_custom_column", [ $this, 'render_taxonomy_column_on_linked_post_type_admin_table' ], 10 );

		$repository = $this->get_linked_post_type_repository();
		if ( $repository ) {
			$repository_slug = $repository->get_filter_name();
			remove_action( "tribe_repository_{$repository_slug}_init", [ $this, 'include_schemas' ] );
		}
	}


	/**
	 * @inheritDoc
	 */
	public function add_filters(): void {
		add_filter( 'tribe_context_locations', [ $this, 'filter_context_locations' ] );

		$linked_post_type = $this->get_linked_post_type();
		// Register the Linked Post Type Columns.
		add_filter( "manage_{$linked_post_type}_posts_columns", [ $this, 'filter_include_taxonomy_to_linked_post_type_admin_table' ] );

		$view_slug = $this->get_linked_post_type_view_slug();
		if ( is_string( $view_slug ) ) {
			add_filter( "tribe_template_html:events-pro/v2/{$view_slug}/meta", [ $this, 'filter_remove_meta_on_category_page' ], 15, 4 );
			add_filter( "tribe_events_views_v2_view_{$view_slug}_breadcrumbs", [ $this, 'filter_view_breadcrumbs' ], 15, 2 );
			add_filter( "tec_events_views_v2_view_{$view_slug}_header_title", [ $this, 'filter_view_header_title' ], 15, 2 );
			add_filter( "tec_events_views_v2_view_{$view_slug}_content_title", [ $this, 'filter_view_content_title' ], 15, 2 );
			add_filter( 'tec_events_title_taxonomies', [ $this, 'filter_include_to_title_taxonomies' ], 15 );
			add_filter( 'tribe_shortcode_tribe_events_default_arguments', [ $this, 'filter_shortcode_default_arguments' ], 15, 2 );
			add_filter( 'tec_shortcode_tribe_events_aliased_arguments', [ $this, 'filter_shortcode_aliased_arguments' ], 15, 2 );
			add_filter( 'tribe_shortcode_tribe_events_validate_arguments_map', [ $this, 'filter_shortcode_validate_arguments_map' ], 15, 2 );
			add_filter( 'tribe_events_pro_shortcode_toggle_view_hooks', [ $this, 'toggle_shortcode_hooks' ], 15, 2 );

		}

		$configuration = $this->get_configuration();

		// Only deal with Rewrites if we are public.
		if ( $configuration['public'] ) {
			add_filter( 'tribe_events_rewrite_base_slugs', [ $this, 'filter_include_rewrite_bases' ] );
			add_filter( 'tribe_events_rewrite_matchers_to_query_vars_map', [ $this, 'filter_include_rewrite_query_vars_map' ] );
			add_filter( 'tec_common_rewrite_dynamic_matchers', [ $this, 'filter_include_taxonomy_to_dynamic_matchers' ], 15, 3 );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function remove_filters(): void {
		remove_filter( 'tribe_context_locations', [ $this, 'filter_context_locations' ] );

		$linked_post_type = $this->get_linked_post_type();
		// Register the Linked Post Type Columns.
		remove_filter( "manage_{$linked_post_type}_posts_columns", [ $this, 'filter_include_taxonomy_to_linked_post_type_admin_table' ] );

		$view_slug = $this->get_linked_post_type_view_slug();
		if ( is_string( $view_slug ) ) {
			remove_filter( "tribe_template_html:events-pro/v2/{$view_slug}/meta", [ $this, 'filter_remove_meta_on_category_page' ], 15 );
			remove_filter( "tribe_events_views_v2_view_{$view_slug}_breadcrumbs", [ $this, 'filter_view_breadcrumbs' ], 15 );
			remove_filter( "tec_events_views_v2_view_{$view_slug}_header_title", [ $this, 'filter_view_header_title' ], 15 );
			remove_filter( "tec_events_views_v2_view_{$view_slug}_content_title", [ $this, 'filter_view_content_title' ], 15 );
			remove_filter( 'tec_common_rewrite_dynamic_matchers', [ $this, 'filter_include_taxonomy_to_dynamic_matchers' ], 15 );
			remove_filter( "tribe_shortcode_tribe_events_default_arguments", [ $this, 'filter_shortcode_default_arguments' ], 15 );
			remove_filter( "tec_shortcode_tribe_events_aliased_arguments", [ $this, 'filter_shortcode_aliased_arguments' ], 15 );
			remove_filter( "tribe_shortcode_tribe_events_validate_arguments_map", [ $this, 'filter_shortcode_validate_arguments_map' ], 15 );
			remove_filter( 'tribe_events_pro_shortcode_toggle_view_hooks', [ $this, 'toggle_shortcode_hooks' ], 15 );
		}

		$configuration = $this->get_configuration();

		// Only deal with Rewrites if we are public.
		if ( $configuration['public'] ) {
			remove_filter( 'tribe_events_rewrite_base_slugs', [ $this, 'filter_include_rewrite_bases' ] );
			remove_filter( 'tribe_events_rewrite_matchers_to_query_vars_map', [ $this, 'filter_include_rewrite_query_vars_map' ] );
			remove_filter( "tec_events_title_taxonomies", [ $this, 'filter_include_to_title_taxonomies' ], 15 );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function set_taxonomy_object( WP_Taxonomy $taxonomy_object ): void {
		$this->taxonomy_object = $taxonomy_object;
	}

	/**
	 * @inheritDoc
	 */
	public function get_taxonomy_object(): ?WP_Taxonomy {
		return $this->taxonomy_object;
	}

	/**
	 * @inheritDoc
	 */
	public function set_admin_menu( string $admin_menu ): void {
		$this->admin_menu = $admin_menu;
	}

	/**
	 * @inheritDoc
	 */
	public function get_admin_menu(): ?string {
		return $this->admin_menu;
	}

	/**
	 * @inheritDoc
	 */
	public function is_registered_in_wp(): bool {
		return $this->get_taxonomy_object() instanceof WP_Taxonomy;
	}

	/**
	 * @inheritDoc
	 */
	public function get_menu_capability(): string {
		return 'publish_tribe_events';
	}

	/**
	 * @inheritDoc
	 */
	abstract public function get_wp_slug(): string;

	/**
	 * @inheritDoc
	 */
	abstract public function get_slug(): string;

	/**
	 * @inheritDoc
	 */
	public function get_singular_label_without_linked_post(): ?string {
		$slug  = $this->get_slug();
		$value = $this->define_singular_label_without_linked_post();

		/**
		 * Filter the singular label for the taxonomy without the linked post for all linked post type taxonomies.
		 *
		 * @since 6.2.0
		 *
		 * @param string            $value The singular label for the taxonomy without the linked post.
		 * @param Taxonomy_Abstract $this  The taxonomy object.
		 */
		$value = apply_filters( 'tec_events_pro_linked_post_taxonomy_singular_label_without_linked_post', $value, $this );

		// If the value is not a string or null, reset to null, failed validation.
		if ( $value !== null && ! is_string( $value ) ) {
			$value = null;
		}

		/**
		 * Filter the singular label for the taxonomy without the linked post for a specific linked post type taxonomies.
		 *
		 * @since 6.2.0
		 *
		 * @param string            $value The singular label for the taxonomy without the linked post.
		 * @param Taxonomy_Abstract $this  The taxonomy object.
		 */
		$value = apply_filters( "tec_events_pro_linked_post_taxonomy_{$slug}_singular_label_without_linked_post", $value, $this );

		// If the value is not a string or null, return null, failed validation.
		if ( $value !== null && ! is_string( $value ) ) {
			return null;
		}

		return $value;
	}

	/**
	 * @inheritDoc
	 */
	public function get_plural_label_without_linked_post(): ?string {
		$slug  = $this->get_slug();
		$value = $this->define_plural_label_without_linked_post();

		/**
		 * Filter the singular label for the taxonomy without the linked post for all linked post type taxonomies.
		 *
		 * @since 6.2.0
		 *
		 * @param string            $value The singular label for the taxonomy without the linked post.
		 * @param Taxonomy_Abstract $this  The taxonomy object.
		 */
		$value = apply_filters( 'tec_events_pro_linked_post_taxonomy_plural_label_without_linked_post', $value, $this );

		// If the value is not a string or null, reset to null, failed validation.
		if ( $value !== null && ! is_string( $value ) ) {
			$value = null;
		}

		/**
		 * Filter the singular label for the taxonomy without the linked post for a specific linked post type taxonomies.
		 *
		 * @since 6.2.0
		 *
		 * @param string            $value The singular label for the taxonomy without the linked post.
		 * @param Taxonomy_Abstract $this  The taxonomy object.
		 */
		$value = apply_filters( "tec_events_pro_linked_post_taxonomy_{$slug}_plural_label_without_linked_post", $value, $this );

		// If the value is not a string or null, return null, failed validation.
		if ( $value !== null && ! is_string( $value ) ) {
			return null;
		}

		return $value;
	}

	/**
	 * @inheritDoc
	 */
	public function get_rewrite_slug_singular(): ?string {
		$slug  = $this->get_slug();
		$value = $this->define_rewrite_slug_singular();

		/**
		 * Filter the singular label for the taxonomy without the linked post for all linked post type taxonomies.
		 *
		 * @since 6.2.0
		 *
		 * @param string            $value The singular label for the taxonomy without the linked post.
		 * @param Taxonomy_Abstract $this  The taxonomy object.
		 */
		$value = apply_filters( 'tec_events_pro_linked_post_taxonomy_rewrite_slug_singular', $value, $this );

		// If the value is not a string or null, reset to null, failed validation.
		if ( $value !== null && ! is_string( $value ) ) {
			$value = null;
		}

		/**
		 * Filter the singular label for the taxonomy without the linked post for a specific linked post type taxonomies.
		 *
		 * @since 6.2.0
		 *
		 * @param string            $value The singular label for the taxonomy without the linked post.
		 * @param Taxonomy_Abstract $this  The taxonomy object.
		 */
		$value = apply_filters( "tec_events_pro_linked_post_taxonomy_{$slug}_rewrite_slug_singular", $value, $this );

		// If the value is not a string or null, return null, failed validation.
		if ( $value !== null && ! is_string( $value ) ) {
			return null;
		}

		return $value;
	}

	/**
	 * @inheritDoc
	 */
	public function get_rewrite_slug_plural(): ?string {
		$slug  = $this->get_slug();
		$value = $this->define_rewrite_slug_plural();

		/**
		 * Filter the singular label for the taxonomy without the linked post for all linked post type taxonomies.
		 *
		 * @since 6.2.0
		 *
		 * @param string            $value The singular label for the taxonomy without the linked post.
		 * @param Taxonomy_Abstract $this  The taxonomy object.
		 */
		$value = apply_filters( 'tec_events_pro_linked_post_taxonomy_rewrite_slug_plural', $value, $this );

		// If the value is not a string or null, reset to null, failed validation.
		if ( $value !== null && ! is_string( $value ) ) {
			$value = null;
		}

		/**
		 * Filter the singular label for the taxonomy without the linked post for a specific linked post type taxonomies.
		 *
		 * @since 6.2.0
		 *
		 * @param string            $value The singular label for the taxonomy without the linked post.
		 * @param Taxonomy_Abstract $this  The taxonomy object.
		 */
		$value = apply_filters( "tec_events_pro_linked_post_taxonomy_{$slug}_rewrite_slug_plural", $value, $this );

		// If the value is not a string or null, return null, failed validation.
		if ( $value !== null && ! is_string( $value ) ) {
			return null;
		}

		return $value;
	}

	/**
	 * @inheritDoc
	 */
	public function get_linked_post_type(): ?string {
		$slug  = $this->get_slug();
		$value = $this->define_linked_post_type();

		/**
		 * Filter the singular label for the taxonomy without the linked post for all linked post type taxonomies.
		 *
		 * @since 6.2.0
		 *
		 * @param string            $value The singular label for the taxonomy without the linked post.
		 * @param Taxonomy_Abstract $this  The taxonomy object.
		 */
		$value = apply_filters( 'tec_events_pro_linked_post_taxonomy_linked_post_type', $value, $this );

		// If the value is not a string or null, reset to null, failed validation.
		if ( $value !== null && ! is_string( $value ) ) {
			$value = null;
		}

		/**
		 * Filter the singular label for the taxonomy without the linked post for a specific linked post type taxonomies.
		 *
		 * @since 6.2.0
		 *
		 * @param string            $value The singular label for the taxonomy without the linked post.
		 * @param Taxonomy_Abstract $this  The taxonomy object.
		 */
		$value = apply_filters( "tec_events_pro_linked_post_taxonomy_{$slug}_linked_post_type", $value, $this );

		// If the value is not a string or null, return null, failed validation.
		if ( $value !== null && ! is_string( $value ) ) {
			return null;
		}

		return $value;
	}

	/**
	 * @inheritDoc
	 */
	public function get_linked_post_type_rewrite_slug_singular(): ?string {
		$slug  = $this->get_slug();
		$value = $this->define_linked_post_type_rewrite_slug_singular();

		/**
		 * Filter the singular label for the taxonomy without the linked post for all linked post type taxonomies.
		 *
		 * @since 6.2.0
		 *
		 * @param string            $value The singular label for the taxonomy without the linked post.
		 * @param Taxonomy_Abstract $this  The taxonomy object.
		 */
		$value = apply_filters( 'tec_events_pro_linked_post_taxonomy_linked_post_type_rewrite_slug_singular', $value, $this );

		// If the value is not a string or null, reset to null, failed validation.
		if ( $value !== null && ! is_string( $value ) ) {
			$value = null;
		}

		/**
		 * Filter the singular label for the taxonomy without the linked post for a specific linked post type taxonomies.
		 *
		 * @since 6.2.0
		 *
		 * @param string            $value The singular label for the taxonomy without the linked post.
		 * @param Taxonomy_Abstract $this  The taxonomy object.
		 */
		$value = apply_filters( "tec_events_pro_linked_post_taxonomy_{$slug}_linked_post_type_rewrite_slug_singular", $value, $this );

		// If the value is not a string or null, return null, failed validation.
		if ( $value !== null && ! is_string( $value ) ) {
			return null;
		}

		return $value;
	}

	/**
	 * @inheritDoc
	 */
	public function get_linked_post_type_rewrite_slug_plural(): ?string {
		$slug  = $this->get_slug();
		$value = $this->define_linked_post_type_rewrite_slug_plural();

		/**
		 * Filter the singular label for the taxonomy without the linked post for all linked post type taxonomies.
		 *
		 * @since 6.2.0
		 *
		 * @param string            $value The singular label for the taxonomy without the linked post.
		 * @param Taxonomy_Abstract $this  The taxonomy object.
		 */
		$value = apply_filters( 'tec_events_pro_linked_post_taxonomy_linked_post_type_rewrite_slug_plural', $value, $this );

		// If the value is not a string or null, reset to null, failed validation.
		if ( $value !== null && ! is_string( $value ) ) {
			$value = null;
		}

		/**
		 * Filter the singular label for the taxonomy without the linked post for a specific linked post type taxonomies.
		 *
		 * @since 6.2.0
		 *
		 * @param string            $value The singular label for the taxonomy without the linked post.
		 * @param Taxonomy_Abstract $this  The taxonomy object.
		 */
		$value = apply_filters( "tec_events_pro_linked_post_taxonomy_{$slug}_linked_post_type_rewrite_slug_plural", $value, $this );

		// If the value is not a string or null, return null, failed validation.
		if ( $value !== null && ! is_string( $value ) ) {
			return null;
		}

		return $value;
	}

	/**
	 * @inheritDoc
	 */
	public function get_linked_post_type_view_slug(): ?string {
		$slug  = $this->get_slug();
		$value = $this->define_linked_post_type_view_slug();

		/**
		 * Filter the linked post type view slug for all linked post type taxonomies.
		 *
		 * @since 6.2.0
		 *
		 * @param string            $value The linked post type view slug.
		 * @param Taxonomy_Abstract $this  The taxonomy object.
		 */
		$value = apply_filters( 'tec_events_pro_linked_post_taxonomy_linked_post_type_view_slug', $value, $this );

		// If the value is not a string or null, reset to null, failed validation.
		if ( $value !== null && ! is_string( $value ) ) {
			$value = null;
		}

		/**
		 * Filter the linked post type view slug for a specific linked post type taxonomies.
		 *
		 * @since 6.2.0
		 *
		 * @param string            $value The linked post type view slug.
		 * @param Taxonomy_Abstract $this  The taxonomy object.
		 */
		$value = apply_filters( "tec_events_pro_linked_post_taxonomy_{$slug}_linked_post_type_view_slug", $value, $this );

		// If the value is not a string or null, return null, failed validation.
		if ( $value !== null && ! is_string( $value ) ) {
			return null;
		}

		return $value;
	}

	/**
	 * @inheritDoc
	 */
	public function get_linked_post_type_label_singular(): ?string {
		$slug  = $this->get_slug();
		$value = $this->define_linked_post_type_label_singular();

		/**
		 * Filter the singular linked post type label for all linked post type taxonomies.
		 *
		 * @since 6.2.0
		 *
		 * @param string            $value The singular linked post type label.
		 * @param Taxonomy_Abstract $this  The taxonomy object.
		 */
		$value = apply_filters( 'tec_events_pro_linked_post_taxonomy_linked_post_type_label_singular', $value, $this );

		// If the value is not a string or null, reset to null, failed validation.
		if ( $value !== null && ! is_string( $value ) ) {
			$value = null;
		}

		/**
		 * Filter the singular linked post type label for a specific linked post type taxonomies.
		 *
		 * @since 6.2.0
		 *
		 * @param string            $value The singular linked post type label.
		 * @param Taxonomy_Abstract $this  The taxonomy object.
		 */
		$value = apply_filters( "tec_events_pro_linked_post_taxonomy_{$slug}_linked_post_type_label_singular", $value, $this );

		// If the value is not a string or null, return null, failed validation.
		if ( $value !== null && ! is_string( $value ) ) {
			return null;
		}

		return $value;
	}

	/**
	 * @inheritDoc
	 */
	public function get_linked_post_type_label_singular_lowercase(): ?string {
		$slug  = $this->get_slug();
		$value = $this->define_linked_post_type_label_singular_lowercase();

		/**
		 * Filter the singular lowercase linked post type label for all linked post type taxonomies.
		 *
		 * @since 6.2.0
		 *
		 * @param string            $value The singular lowercase linked post type label.
		 * @param Taxonomy_Abstract $this  The taxonomy object.
		 */
		$value = apply_filters( 'tec_events_pro_linked_post_taxonomy_linked_post_type_label_singular_lowercase', $value, $this );

		// If the value is not a string or null, reset to null, failed validation.
		if ( $value !== null && ! is_string( $value ) ) {
			$value = null;
		}

		/**
		 * Filter the singular lowercase linked post type label for a specific linked post type taxonomies.
		 *
		 * @since 6.2.0
		 *
		 * @param string            $value The singular lowercase linked post type label.
		 * @param Taxonomy_Abstract $this  The taxonomy object.
		 */
		$value = apply_filters( "tec_events_pro_linked_post_taxonomy_{$slug}_linked_post_type_label_singular_lowercase", $value, $this );

		// If the value is not a string or null, return null, failed validation.
		if ( $value !== null && ! is_string( $value ) ) {
			return null;
		}

		return $value;
	}

	/**
	 * @inheritDoc
	 */
	public function get_linked_post_type_label_plural(): ?string {
		$slug  = $this->get_slug();
		$value = $this->define_linked_post_type_label_plural();

		/**
		 * Filter the plural linked post type label for all linked post type taxonomies.
		 *
		 * @since 6.2.0
		 *
		 * @param string            $value The plural linked post type label.
		 * @param Taxonomy_Abstract $this  The taxonomy object.
		 */
		$value = apply_filters( 'tec_events_pro_linked_post_taxonomy_linked_post_type_label_plural', $value, $this );

		// If the value is not a string or null, reset to null, failed validation.
		if ( $value !== null && ! is_string( $value ) ) {
			$value = null;
		}

		/**
		 * Filter the plural linked post type label for a specific linked post type taxonomies.
		 *
		 * @since 6.2.0
		 *
		 * @param string            $value The plural linked post type label.
		 * @param Taxonomy_Abstract $this  The taxonomy object.
		 */
		$value = apply_filters( "tec_events_pro_linked_post_taxonomy_{$slug}_linked_post_type_label_plural", $value, $this );

		// If the value is not a string or null, return null, failed validation.
		if ( $value !== null && ! is_string( $value ) ) {
			return null;
		}

		return $value;
	}

	/**
	 * @inheritDoc
	 */
	public function get_linked_post_type_label_plural_lowercase(): ?string {
		$slug  = $this->get_slug();
		$value = $this->define_linked_post_type_label_plural_lowercase();

		/**
		 * Filter the plural lowercase linked post type label for all linked post type taxonomies.
		 *
		 * @since 6.2.0
		 *
		 * @param string            $value The plural lowercase linked post type label.
		 * @param Taxonomy_Abstract $this  The taxonomy object.
		 */
		$value = apply_filters( 'tec_events_pro_linked_post_taxonomy_linked_post_type_label_plural_lowercase', $value, $this );

		// If the value is not a string or null, reset to null, failed validation.
		if ( $value !== null && ! is_string( $value ) ) {
			$value = null;
		}

		/**
		 * Filter the plural lowercase linked post type label for a specific linked post type taxonomies.
		 *
		 * @since 6.2.0
		 *
		 * @param string            $value The plural lowercase linked post type label.
		 * @param Taxonomy_Abstract $this  The taxonomy object.
		 */
		$value = apply_filters( "tec_events_pro_linked_post_taxonomy_{$slug}_linked_post_type_label_plural_lowercase", $value, $this );

		// If the value is not a string or null, return null, failed validation.
		if ( $value !== null && ! is_string( $value ) ) {
			return null;
		}

		return $value;
	}

	/**
	 * @inheritDoc
	 */
	public function get_linked_post_type_repository(): ?Repository_Interface {
		$slug  = $this->get_slug();
		$value = $this->define_linked_post_type_repository();

		/**
		 * Filter the linked post repository for all linked post type taxonomies.
		 *
		 * @since 6.2.0
		 *
		 * @param Repository_Interface $value The post type repository.
		 * @param Taxonomy_Abstract    $this  The taxonomy object.
		 */
		$value = apply_filters( 'tec_events_pro_linked_post_taxonomy_linked_post_type_repository', $value, $this );

		if ( ! $value instanceof Repository_Interface ) {
			return null;
		}

		/**
		 * Filter the linked post repository for a specific linked post type taxonomies.
		 *
		 * @since 6.2.0
		 *
		 * @param Repository_Interface $value The post type repository.
		 * @param Taxonomy_Abstract    $this  The taxonomy object.
		 */
		$value = apply_filters( "tec_events_pro_linked_post_taxonomy_{$slug}_linked_post_type_repository", $value, $this );

		if ( ! $value instanceof Repository_Interface ) {
			return null;
		}

		return $value;
	}

	/**
	 * Protected method to define the singular taxonomy label without the linked post type.
	 *
	 * This method is used internally by the `get_singular_label_without_linked_post` method, which is the one that
	 * is used for getting the actual value.
	 *
	 * @since 6.2.0
	 *
	 * @return string|null
	 */
	abstract protected function define_singular_label_without_linked_post(): ?string;

	/**
	 * Protected method to define the plural taxonomy label without the linked post type.
	 *
	 * This method is used internally by the `get_plural_label_without_linked_post` method, which is the one that
	 * is used for getting the actual value.
	 *
	 * @since 6.2.0
	 *
	 * @return string|null
	 */
	abstract protected function define_plural_label_without_linked_post(): ?string;

	/**
	 * Protected method to define the singular taxonomy rewrite slug.
	 *
	 * This method is used internally by the `get_rewrite_slug_singular` method, which is the one that
	 * is used for getting the actual value.
	 *
	 * @since 6.2.0
	 *
	 * @return string|null
	 */
	abstract protected function define_rewrite_slug_singular(): ?string;

	/**
	 * Protected method to define the plural taxonomy rewrite slug.
	 *
	 * This method is used internally by the `get_rewrite_slug_plural` method, which is the one that
	 * is used for getting the actual value.
	 *
	 * @since 6.2.0
	 *
	 * @return string|null
	 */
	abstract protected function define_rewrite_slug_plural(): ?string;

	/**
	 * Protected method to define the Linked Post Type.
	 *
	 * This method is used internally by the `get_linked_post_type` method, which is the one that
	 * is used for getting the actual value.
	 *
	 * @since 6.2.0
	 *
	 * @return string|null
	 */
	abstract protected function define_linked_post_type(): ?string;

	/**
	 * Protected method to define the Linked Post Type rewrite slug singular.
	 *
	 * This method is used internally by the `get_linked_post_type_rewrite_slug_singular` method, which is the one that
	 * is used for getting the actual value.
	 *
	 * @since 6.2.0
	 *
	 * @return string|null
	 */
	abstract protected function define_linked_post_type_rewrite_slug_singular(): ?string;

	/**
	 * Protected method to define the Linked Post Type rewrite slug plural.
	 *
	 * This method is used internally by the `get_linked_post_type_rewrite_slug_plural` method, which is the one that
	 * is used for getting the actual value.
	 *
	 * @since 6.2.0
	 *
	 * @return string|null
	 */
	abstract protected function define_linked_post_type_rewrite_slug_plural(): ?string;

	/**
	 * Protected method to define the Linked Post Type view slug.
	 *
	 * This method is used internally by the `get_linked_post_type_view_slug` method, which is the one that
	 * is used for getting the actual value.
	 *
	 * @since 6.2.0
	 *
	 * @return string|null
	 */
	abstract protected function define_linked_post_type_view_slug(): ?string;

	/**
	 * Protected method to define the Linked Post Type Label singular.
	 *
	 * This method is used internally by the `get_linked_post_type_label_singular` method, which is the one that
	 * is used for getting the actual value.
	 *
	 * @since 6.2.0
	 *
	 * @return string|null
	 */
	abstract protected function define_linked_post_type_label_singular(): ?string;

	/**
	 * Protected method to define the Linked Post Type Label singular lowercase.
	 *
	 * This method is used internally by the `get_linked_post_type_label_singular_lowercase` method, which is the one that
	 * is used for getting the actual value.
	 *
	 * @since 6.2.0
	 *
	 * @return string|null
	 */
	abstract protected function define_linked_post_type_label_singular_lowercase(): ?string;

	/**
	 * Protected method to define the Linked Post Type Label plural.
	 *
	 * This method is used internally by the `get_linked_post_type_label_plural` method, which is the one that
	 * is used for getting the actual value.
	 *
	 * @since 6.2.0
	 *
	 * @return string|null
	 */
	abstract protected function define_linked_post_type_label_plural(): ?string;

	/**
	 * Protected method to define the Linked Post Type Label plural lowercase.
	 *
	 * This method is used internally by the `get_linked_post_type_label_plural_lowercase` method, which is the one that
	 * is used for getting the actual value.
	 *
	 * @since 6.2.0
	 *
	 * @return string|null
	 */
	abstract protected function define_linked_post_type_label_plural_lowercase(): ?string;

	/**
	 * Protected method to define the repository for the linked post type.
	 *
	 * This method is used internally by the `get_linked_post_type_repository` method, which is the one that is used for
	 * getting the actual repository.
	 *
	 * @since 6.2.0
	 *
	 * @return Repository_Interface|null
	 */
	abstract protected function define_linked_post_type_repository(): ?\Tribe__Repository__Interface;

	/**
	 * Gets the rewrite rule for the taxonomy.
	 * This is currently not being used here, but it's here for future reference.
	 *
	 * @since 6.2.0
	 *
	 * @return string
	 */
	protected function get_rewrite_rule(): string {
		$rewrite = TEC_Rewrite::instance();
		$tec     = TEC::instance();

		$rewrite_slug = $rewrite->prepare_slug( $this->get_linked_post_type_label_plural_lowercase(), $this->get_linked_post_type(), false );

		return sprintf( '%1$s/%2$s/%3$s', $tec->getRewriteSlug(), $rewrite_slug, $this->get_rewrite_translated_slug_singular() );
	}

	/**
	 * Based on the singular slug of the taxonomy attempts to get the translated version of it.
	 *
	 * @since 6.2.0
	 *
	 * @return string
	 */
	protected function get_rewrite_translated_slug_singular(): string {
		// These translations are intentionally dynamic, so that we can use existing translations.
		$attempt_to_translate = __( $this->get_rewrite_slug_singular(), 'tribe-events-calendar-pro' );

		// If the translation is the same as the slug, it means it wasn't translated, so we try with TEC domain.
		if ( $attempt_to_translate === $this->get_rewrite_slug_singular() ) {
			$attempt_to_translate = __( $this->get_rewrite_slug_singular(), 'the-events-calendar' );
		}

		return $attempt_to_translate;
	}

	/**
	 * Based on the plural slug of the taxonomy attempts to get the translated version of it.
	 *
	 * @since 6.2.0
	 *
	 * @return string
	 */
	protected function get_rewrite_translated_slug_plural(): string {
		// These translations are intentionally dynamic, so that we can use existing translations.
		$attempt_to_translate = __( $this->get_rewrite_slug_plural(), 'tribe-events-calendar-pro' );

		// If the translation is the same as the slug, it means it wasn't translated, so we try with TEC domain.
		if ( $attempt_to_translate === $this->get_rewrite_slug_plural() ) {
			$attempt_to_translate = __( $this->get_rewrite_slug_plural(), 'the-events-calendar' );
		}

		return $attempt_to_translate;
	}

	/**
	 * Define the values for the configuration array.
	 *
	 * @since 6.2.0
	 *
	 * @return array
	 */
	abstract protected function define_configuration(): array;

	/**
	 * Gets the taxonomy configuration for WP registration.
	 *
	 * @since 6.2.0
	 *
	 * @return array
	 */
	protected function get_configuration(): array {
		$configuration = [
			'hierarchical'          => true,
			'update_count_callback' => '',
			'rewrite'               => [
				'slug'         => $this->get_rewrite_rule(),
				'with_front'   => false,
				'hierarchical' => true,
			],
			'public'                => true,
			'show_ui'               => true,
			'labels'                => $this->get_labels(),
			'capabilities'          => [
				'manage_terms' => 'publish_tribe_events',
				'edit_terms'   => 'publish_tribe_events',
				'delete_terms' => 'publish_tribe_events',
				'assign_terms' => 'edit_tribe_events',
			],
		];

		// Pull the configuration from the child class.
		$configuration = array_merge( $configuration, $this->define_configuration() );

		$slug = $this->get_slug();

		/**
		 * Filter the linked post taxonomy arguments used in register_taxonomy.
		 *
		 * @since 6.2.0
		 *
		 * @param array             $configuration The taxonomy arguments.
		 * @param Taxonomy_Abstract $taxonomy      The taxonomy object.
		 */
		return apply_filters( "tec_events_pro_linked_post_taxonomy_{$slug}_configuration", $configuration, $this );
	}

	/**
	 * Define the values for the configuration array.
	 *
	 * @since 6.2.0
	 *
	 * @return array
	 */
	abstract protected function define_labels(): array;

	/**
	 * Gets the labels for the taxonomy registration, this method is only used for registration with WP,
	 * please if you need to fetch labels, use `$this->get_taxonomy_object()->labels`.
	 *
	 * We are specifically using `private` methods here to avoid direct usage.
	 *
	 * @since 6.2.0
	 *
	 * @return array
	 */
	protected function get_labels(): array {
		$post_type_singular = $this->get_linked_post_type_label_singular();
		$taxonomy_singular  = $this->get_singular_label_without_linked_post();
		$taxonomy_plural    = $this->get_plural_label_without_linked_post();

		$labels = [
			'name'                  => sprintf(
				esc_html__( '%1$s %2$s', 'tribe-events-calendar-pro' ),
				$post_type_singular,
				$taxonomy_plural
			),
			'singular_name'         => sprintf(
				esc_html__( '%1$s %2$s', 'tribe-events-calendar-pro' ),
				$post_type_singular,
				$taxonomy_singular
			),
			'search_items'          => sprintf(
				esc_html__( 'Search %1$s %2$s', 'tribe-events-calendar-pro' ),
				$post_type_singular,
				$taxonomy_plural
			),
			'all_items'             => sprintf(
				esc_html__( 'All %1$s %2$s', 'tribe-events-calendar-pro' ),
				$post_type_singular,
				$taxonomy_plural
			),
			'parent_item'           => sprintf(
				esc_html__( 'Parent %1$s %2$s', 'tribe-events-calendar-pro' ),
				$post_type_singular,
				$taxonomy_singular
			),
			'parent_item_colon'     => sprintf(
				esc_html__( 'Parent %1$s %2$s:', 'tribe-events-calendar-pro' ),
				$post_type_singular,
				$taxonomy_singular
			),
			'edit_item'             => sprintf(
				esc_html__( 'Edit %1$s %2$s', 'tribe-events-calendar-pro' ),
				$post_type_singular,
				$taxonomy_singular
			),
			'update_item'           => sprintf(
				esc_html__( 'Update %1$s %2$s', 'tribe-events-calendar-pro' ),
				$post_type_singular,
				$taxonomy_singular
			),
			'add_new_item'          => sprintf(
				esc_html__( 'Add New %1$s %2$s', 'tribe-events-calendar-pro' ),
				$post_type_singular,
				$taxonomy_singular
			),
			'new_item_name'         => sprintf(
				esc_html__( 'New %1$s %2$s Name', 'tribe-events-calendar-pro' ),
				$post_type_singular,
				$taxonomy_singular
			),
			'item_link'             => sprintf(
			// Translators: %s: Linked Post Type singular label.
				esc_html__( '%1$s %2$s Link', 'tribe-events-calendar-pro' ),
				$post_type_singular,
				$taxonomy_singular
			),
			'item_link_description' => sprintf(
			// Translators: %s: Linked Post Type singular label.
				esc_html__( 'A link to a particular %1$s %2$s.', 'tribe-events-calendar-pro' ),
				$post_type_singular,
				$taxonomy_singular
			),
		];

		$slug = $this->get_slug();

		/**
		 * Filter the linked post type category taxonomy labels.
		 *
		 * @since 6.2.0
		 *
		 * @param array             $labels   The taxonomy labels.
		 * @param Taxonomy_Abstract $taxonomy The taxonomy object.
		 */
		return apply_filters( "tec_events_pro_linked_post_taxonomy_{$slug}_labels", $labels, $this );
	}

	/**
	 * Registers the Linked Post taxonomy in WordPress.
	 *
	 * @since 6.2.0
	 *
	 * @return WP_Taxonomy|WP_Error The registered taxonomy object on success, WP_Error object on failure.
	 */
	public function register_to_wp(): void {
		$taxonomy_object = register_taxonomy( $this->get_wp_slug(), $this->get_linked_post_type(), $this->get_configuration() );

		if ( $taxonomy_object instanceof WP_Error ) {
			do_action( 'tribe_log', Log::DEBUG, 'Error while trying to register Organizer Category into WordPress', [
				'from'  => $this,
				'error' => $taxonomy_object,
			] );

			return;
		}

		$this->set_taxonomy_object( $taxonomy_object );
	}

	/**
	 * Filters the context locations to add the ones used by the Linked Post taxonomy.
	 *
	 * @since 6.2.0
	 *
	 * @param array $locations The array of context locations.
	 *
	 * @return array The modified context locations.
	 */
	public function filter_context_locations( array $locations = [] ): array {
		$locations[ $this->get_wp_slug() ] = [
			'read' => [
				Context::QUERY_PROP  => [ $this->get_wp_slug() ],
				Context::QUERY_VAR   => [ $this->get_wp_slug() ],
				Context::REQUEST_VAR => [ $this->get_wp_slug() ],
			],
		];

		return $locations;
	}

	/**
	 * Includes the admin menu for the Organizer Category taxonomy.
	 *
	 * @since 6.2.0
	 */
	public function add_submenu_to_wp(): void {
		$labels = $this->get_taxonomy_object()->labels;
		$parent = add_query_arg( [ 'post_type' => TEC::POSTTYPE ], \Tribe__Settings::$parent_page );
		$page   = add_query_arg( [ 'taxonomy' => $this->get_wp_slug(), 'post_type' => TEC::POSTTYPE ], 'edit-tags.php' );

		$category_menu = add_submenu_page(
			$parent,
			$labels->name,
			sprintf( '↳ %1$s', $this->get_plural_label_without_linked_post() ),
			$this->get_menu_capability(),
			$page
		);

		$this->set_admin_menu( $category_menu );
	}

	/**
	 * Modifies the admin menu to add the Organizer Category taxonomy below the Organizer post type and delete the original.
	 *
	 * Note this method does some really hacky stuff to make it work, but it's the only way to do it, please employ
	 * good defensive programming practices when modifying this method.
	 *
	 * @since 6.2.0
	 */
	public function modify_admin_menu(): void {
		global $submenu, $pagenow;

		$parent         = add_query_arg( [ 'post_type' => TEC::POSTTYPE ], \Tribe__Settings::$parent_page );
		$category_page  = add_query_arg( [ 'taxonomy' => $this->get_wp_slug(), 'post_type' => TEC::POSTTYPE ], 'edit-tags.php' );
		$post_type_page = add_query_arg( [ 'post_type' => $this->get_linked_post_type() ], 'edit.php' );

		if ( ! isset( $submenu[ $parent ] ) ) {
			return;
		}

		$submenu_category_array = array_filter( $submenu[ $parent ], static function ( $item ) use ( $category_page ) {
			return $item[2] === $category_page;
		} );
		$submenu_category       = reset( $submenu_category_array );
		$submenu_category_index = array_key_first( $submenu_category_array );

		$submenu_array = array_filter( $submenu[ $parent ], static function ( $item ) use ( $post_type_page ) {
			return $item[2] === $post_type_page;
		} );
		$submenu_index = array_key_first( $submenu_array );

		if ( isset( $submenu[ $parent ][ $submenu_category_index ] ) ) {
			unset( $submenu[ $parent ][ $submenu_category_index ] );
		}
		$submenu[ $parent ] = \Tribe__Main::array_insert_after_key( $submenu_index, $submenu[ $parent ], [ $submenu_index . '.1' => $submenu_category ] );

		// Determine if we are in the correct page to set the submenu file, which will internally set the current menu.
		$request_taxonomy  = tribe_get_request_var( 'taxonomy' );
		$request_post_type = tribe_get_request_var( 'post_type' );
		if (
			$request_post_type === TEC::POSTTYPE
			&& $pagenow === 'edit-tags.php'
			&& $request_taxonomy === $this->get_wp_slug()
		) {

			$modify_submenu_file = static function ( $submenu_file ) use ( $category_page ) {
				return $category_page;
			};
			add_filter( 'submenu_file', $modify_submenu_file, 15 );
		}
	}

	/**
	 * Remove the Meta area in the Category page.
	 *
	 * @since 6.2.0
	 *
	 * @param string   $html     The HTML to be filtered.
	 * @param string   $file     Full path to the template file.
	 * @param string   $name     Name of the template.
	 * @param Template $template The template object.
	 *
	 * @return string
	 */
	public function filter_remove_meta_on_category_page( $html, $file, $name, $template ): string {
		$view = $template->get( 'view' );

		if ( ! $view instanceof View ) {
			return $html;
		}

		$category = $view->get_context()->get( $this->get_wp_slug() );
		if ( empty( $category ) ) {
			return $html;
		}

		return '';
	}

	/**
	 * Filters the repository args to fetch the ids of the Linked Post that belong to the Linked Post taxonomy requested.
	 *
	 * @since 6.2.0
	 *
	 * @param array   $args    The repository args.
	 * @param View    $view    The view object.
	 * @param Context $context The context object.
	 *
	 * @return array
	 */
	public function setup_repository_args( $args, View $view, Context $context ): array {
		if ( ! is_string( $this->get_linked_post_type_view_slug() ) ) {
			return $args;
		}

		if ( ! $this->get_linked_post_type_repository() instanceof \Tribe__Events__Repositories__Linked_Posts ) {
			return $args;
		}

		$term_slug = $context->get( $this->get_wp_slug() );
		$ids       = $this->get_linked_post_type_repository()->by( $this->get_wp_slug(), $term_slug )->get_ids();

		$args[ $this->get_linked_post_type_view_slug() ] = $ids;

		if ( method_exists( $view, 'set_post_id' ) ) {
			$view->set_post_id( $ids );
		}

		return $args;
	}

	/**
	 * Includes the Linked Post taxonomy in the schema for the Linked Post repository.
	 *
	 * @since 6.2.0
	 *
	 * @param \Tribe__Events__Repositories__Linked_Posts $repository The repository object.
	 */
	public function include_schemas( \Tribe__Events__Repositories__Linked_Posts $repository ): void {
		$category = $this->get_wp_slug();
		$slug     = $this->get_slug();
		$repository->add_simple_tax_schema_entry( $slug, $category );
		$repository->add_simple_tax_schema_entry( $slug . '_not_in', $category, 'term_not_in' );
		$repository->add_simple_tax_schema_entry( 'category', $category );
		$repository->add_simple_tax_schema_entry( 'category_not_in', $category, 'term_not_in' );
		$repository->add_simple_tax_schema_entry( $category, $category );
		$repository->add_simple_tax_schema_entry( $category . '_not_in', $category, 'term_not_in' );
	}

	/**
	 * Filters the view breadcrumbs to add the Linked Post taxonomy.
	 *
	 * @since 6.2.0
	 *
	 * @param array $breadcrumbs
	 * @param View  $view
	 *
	 * @return array
	 */
	public function filter_view_breadcrumbs( array $breadcrumbs, View $view ): array {
		$category = $view->get_context()->get( $this->get_wp_slug() );
		if ( empty( $category ) ) {
			return $breadcrumbs;
		}
		$category = get_term_by( 'slug', $category, $this->get_wp_slug() );

		if ( ! $category instanceof \WP_Term ) {
			return $breadcrumbs;
		}

		// Reset the breadcrumbs.
		$breadcrumbs = [];

		$breadcrumbs[] = [
			'link'  => tribe_get_events_link(),
			'label' => tribe_get_event_label_plural(),
		];

		$breadcrumbs[] = [
			'link'  => '',
			'label' => sprintf( _x( 'By %1$s', 'Breadcrumb for the Archive of the Linked Post type by a given taxonomy', 'tribe-events-calendar-pro' ), $this->get_linked_post_type_label_plural() ),
		];

		$breadcrumbs[] = [
			'link'  => '',
			'label' => $category->name,
		];

		return $breadcrumbs;
	}

	/**
	 * Filters the view header title to add the Linked Post taxonomy.
	 *
	 * @since 6.2.0
	 *
	 * @param string $header_title
	 * @param View   $view
	 *
	 * @return string
	 */
	public function filter_view_header_title( string $header_title, View $view ): string {
		$category = $view->get_context()->get( $this->get_wp_slug() );
		if ( empty( $category ) ) {
			return $header_title;
		}
		$category = get_term_by( 'slug', $category, $this->get_wp_slug() );

		if ( ! $category instanceof \WP_Term ) {
			return $header_title;
		}

		return $category->name;
	}

	/**
	 * Filter the content title for the taxonomy view.
	 *
	 * @since 6.2.0
	 *
	 * @param string $content_title
	 * @param View   $view
	 *
	 * @return string
	 */
	public function filter_view_content_title( string $content_title, View $view ): string {
		$category = $view->get_context()->get( $this->get_wp_slug() );
		if ( empty( $category ) ) {
			return $content_title;
		}
		$category = get_term_by( 'slug', $category, $this->get_wp_slug() );

		if ( ! $category instanceof \WP_Term ) {
			return $content_title;
		}

		return sprintf(
			_x( '%1$s from this %3$s of %2$s', 'Content title for the Taxonomy View', 'tribe-events-calendar-pro' ),
			tribe_get_event_label_plural(),
			$this->get_linked_post_type_label_plural_lowercase(),
			strtolower( $this->get_singular_label_without_linked_post() )
		);
	}

	/**
	 * Filters the list of taxonomies that can affect the title of the page.
	 *
	 * @since 6.2.0
	 *
	 * @param array $taxonomies The taxonomies currently included in the title.
	 *
	 * @return array
	 */
	public function filter_include_to_title_taxonomies( array $taxonomies ): array {
		$taxonomies[] = $this->get_wp_slug();

		return $taxonomies;
	}

	/**
	 * Include the taxonomy as a default argument for the shortcode, using the non-prefixed slug.
	 *
	 * @since 6.2.0
	 *
	 * @param array              $default_arguments
	 * @param Shortcode_Abstract $shortcode
	 *
	 * @return array
	 */
	public function filter_shortcode_default_arguments( array $default_arguments, Shortcode_Abstract $shortcode ): array {
		$slug                       = $this->get_slug();
		$default_arguments[ $slug ] = null;

		/**
		 * @todo Uncomment when the `linked_post_type_not_in`  argument is added to the Events Repository
		 * $default_arguments["exclude-{$wp_slug}"] = null;
		 */

		return $default_arguments;
	}

	/**
	 * Include the taxonomy to the shortcode aliases.
	 *
	 * @since 6.2.0
	 *
	 * @param array              $aliased_arguments
	 * @param Shortcode_Abstract $shortcode
	 *
	 * @return array
	 */
	public function filter_shortcode_aliased_arguments( array $aliased_arguments, Shortcode_Abstract $shortcode ): array {
		$slug                          = $this->get_slug();
		$wp_slug                       = $this->get_wp_slug();
		$aliased_arguments[ $wp_slug ] = $slug;

		/**
		 * @todo Uncomment when the `linked_post_type_not_in`  argument is added to the Events Repository
		 * $aliased_arguments["exclude-{$wp_slug}"] = "exclude-{$slug}";
		 */

		return $aliased_arguments;
	}

	/**
	 * Given a list of arguments, normalize the taxonomy to term IDs.
	 *
	 * @since 6.2.0
	 *
	 * @param array              $arguments_map
	 * @param Shortcode_Abstract $shortcode
	 *
	 * @return array
	 */
	public function filter_shortcode_validate_arguments_map( array $arguments_map, Shortcode_Abstract $shortcode ): array {
		$slug                   = $this->get_slug();
		$taxonomy               = $this->get_wp_slug();
		$arguments_map[ $slug ] = static function ( $terms ) use ( $taxonomy ) {
			return Taxonomy::normalize_to_term_ids( $terms, $taxonomy );
		};

		/**
		 * @todo Uncomment when the `linked_post_type_not_in`  argument is added to the Events Repository
		 * $arguments_map["exclude-{$slug}"] = static function ( $terms ) use ( $taxonomy ) {
		 * return Taxonomy::normalize_to_term_ids( $terms, $taxonomy );
		 * };
		 */

		return $arguments_map;
	}

	/**
	 * Toggles the shortcode hooks for Linked Post Taxonomy.
	 *
	 * @since 6.2.0
	 *
	 * @param bool               $toggle
	 * @param Shortcode_Abstract $shortcode
	 */
	public function toggle_shortcode_hooks( bool $toggle, Shortcode_Abstract $shortcode ): void {
		if ( $toggle ) {
			add_filter( 'tribe_events_views_v2_view_repository_args', [ $this, 'filter_shortcode_repository_args' ], 15, 3 );
		} else {
			remove_filter( 'tribe_events_views_v2_view_repository_args', [ $this, 'filter_shortcode_repository_args' ], 15 );
		}
	}

	/**
	 * Filter the repository arguments to include the organizers taxonomy
	 *
	 * @since 6.2.0
	 *
	 * @param array   $repository_args
	 * @param Context $context
	 * @param View    $view
	 *
	 * @return array
	 */
	public function filter_shortcode_repository_args( array $repository_args, Context $context, View $view ): array {
		if ( ! $context instanceof Context ) {
			return $repository_args;
		}

		$shortcode_id = $context->get( 'shortcode', false );

		if ( false === $shortcode_id ) {
			return $repository_args;
		}

		$shortcode_args = tribe( Tribe_Events::class )->get_database_arguments( $shortcode_id );
		$slug           = $this->get_slug();

		if (
			! empty( $shortcode_args[ $slug ] )
		) {
			$ids = $this->get_linked_post_type_repository()->by( $this->get_wp_slug(), $shortcode_args[ $slug ] )->get_ids();
			if ( ! empty( $repository_args[ $this->get_linked_post_type_view_slug() ] ) ) {
				$repository_args[ $this->get_linked_post_type_view_slug() ] = array_unique( [ ...( (array) $repository_args[ $this->get_linked_post_type_view_slug() ] ), ...$ids ] );
			} else {
				$repository_args[ $this->get_linked_post_type_view_slug() ] = $ids;
			}
		}

		return $repository_args;
	}

	/**
	 * Add the relevant rewrite rules for the Linked Post taxonomy.
	 *
	 * @since 6.2.0
	 *
	 * @param \Tribe__Rewrite $rewrite
	 */
	public function add_rewrites( $rewrite ): void {
		if ( ! $rewrite instanceof TEC_Rewrite ) {
			return;
		}

		$plural_post_type_name = $this->get_linked_post_type_rewrite_slug_plural();
		$view_slug             = $this->get_linked_post_type_view_slug();

		// When a view Slug was not set, we don't need to add any rewrite rules.
		if ( ! is_string( $view_slug ) ) {
			return;
		}

		$taxonomy_slug = $this->get_slug();

		$rewrite->add(
			[
				"{{ archive }}",
				"{{ {$plural_post_type_name} }}",
				"{{ {$taxonomy_slug} }}",
				'(?:[^/]+/)*([^/]+)',
			],
			[
				'eventDisplay'       => $view_slug,
				'post_type'          => $this->get_linked_post_type(),
				$this->get_wp_slug() => '%1',
			]
		);

		$rewrite->add(
			[
				"{{ archive }}",
				"{{ {$plural_post_type_name} }}",
				"{{ {$taxonomy_slug} }}",
				'(?:[^/]+/)*([^/]+)',
				'{{ page }}',
				'(\d+)',
			],
			[
				'eventDisplay'       => $view_slug,
				'post_type'          => $this->get_linked_post_type(),
				$this->get_wp_slug() => '%1',
				'paged'              => '%2',
			]
		);
	}

	/**
	 * Filters the rewrite bases to add the Linked Post taxonomy.
	 *
	 * @since 6.2.0
	 *
	 * @param array $bases
	 *
	 * @return array
	 */
	public function filter_include_rewrite_bases( array $bases ): array {
		$bases[ $this->get_slug() ] = [ $this->get_rewrite_slug_singular(), $this->get_rewrite_translated_slug_singular() ];

		return $bases;
	}

	/**
	 * Filters the rewrite query vars map to add the Linked Post taxonomy.
	 *
	 * @since 6.2.0
	 *
	 * @param array $query_vars_map
	 *
	 * @return array
	 */
	public function filter_include_rewrite_query_vars_map( array $query_vars_map ): array {
		$query_vars_map[ $this->get_slug() ] = $this->get_wp_slug();

		return $query_vars_map;
	}

	/**
	 * Filters the Linked Post type admin table columns to include the Linked Post taxonomy.
	 *
	 * @since 6.2.0
	 *
	 * @param array $defaults
	 *
	 * @return array
	 */
	public function filter_include_taxonomy_to_linked_post_type_admin_table( array $defaults ): array {
		$defaults[ $this->get_wp_slug() ] = $this->get_taxonomy_object()->labels->name;

		return $defaults;
	}

	/**
	 * Filters the dynamic matchers from the rewrite to add the Linked Post taxonomy, allowing navigation to work as expected.
	 *
	 * @todo  We dont currently have support for non hierarchical taxonomies, so we need to add it, look at the Rewrite class
	 *       in common for the `get_dynamic_matchers` method, and how it handles non-hierarchical taxonomies. Example: `tag`.
	 *
	 * @since 6.2.0
	 *
	 * @param array           $dynamic_matchers
	 * @param array           $query_vars
	 * @param \Tribe__Rewrite $rewrite
	 *
	 * @return array
	 */
	public function filter_include_taxonomy_to_dynamic_matchers( array $dynamic_matchers, array $query_vars, \Tribe__Rewrite $rewrite ): array {
		// Handle The Events Calendar category.
		if ( ! isset( $query_vars[ $this->get_wp_slug() ] ) ) {
			return $dynamic_matchers;
		}

		$bases = (array) $rewrite->get_bases();

		$taxonomy_regex = $bases[ $this->get_slug() ];
		preg_match( '/^\(\?:(?<slugs>[^\\)]+)\)/', $taxonomy_regex, $matches );
		if ( ! isset( $matches['slugs'] ) ) {
			return $dynamic_matchers;
		}

		$slugs = explode( '|', $matches['slugs'] );
		// The localized version is the last.
		$localized_slug = end( $slugs );

		/*
		 * Categories can be hierarchical and the path will be something like
		 * `/events/category/grand-parent/parent/child/list/page/2/`.
		 * If we can match the category to an existing one then let's make sure to build the hierarchical slug.
		 * We cast to comma-separated list to ensure multi-category queries will not resolve to a URL.
		 */
		$category_slug = Arr::to_list( $query_vars[ $this->get_wp_slug() ] );
		$category_term = get_term_by( 'slug', $category_slug, $this->get_wp_slug() );
		if ( $category_term instanceof WP_Term ) {
			$category_slug = get_term_parents_list(
				$category_term->term_id,
				$this->get_wp_slug(),
				[ 'format' => 'slug', 'separator' => '/', 'link' => false, 'inclusive' => true ]
			);
			// Remove leading/trailing slashes to get something like `grand-parent/parent/child`.
			$category_slug = trim( $category_slug, '/' );

			// Create a capturing and non-capturing version of the taxonomy match.
			$dynamic_matchers["(?:{$taxonomy_regex})/(?:[^/]+/)*([^/]+)"] = "{$localized_slug}/{$category_slug}";
			$dynamic_matchers["{$taxonomy_regex}/(?:[^/]+/)*([^/]+)"]     = "{$localized_slug}/{$category_slug}";
		}

		return $dynamic_matchers;
	}

	/**
	 * Column content for the Linked Post taxonomy on the Linked Post type admin table.
	 *
	 * @since 6.2.0
	 *
	 * @param string     $column_name
	 * @param int|string $post_id
	 *
	 */
	public function render_taxonomy_column_on_linked_post_type_admin_table( string $column_name, $post_id ): void {
		$no_terms_html = sprintf(
			'<span aria-hidden="true">&#8212;</span><span class="screen-reader-text">%s</span>',
			sprintf( __( 'No %1$s.', 'tribe-events-calendar-pro' ), $this->get_taxonomy_object()->labels->name )
		);
		// Bail when not the correct column.
		if ( $column_name !== $this->get_wp_slug() ) {
			echo $no_terms_html;

			return;
		}
		$terms = wp_get_object_terms( $post_id, $this->get_wp_slug() );
		if ( empty( $terms ) ) {
			echo $no_terms_html;

			return;
		}

		if ( is_wp_error( $terms ) ) {
			echo $no_terms_html;

			return;
		}
		$total_terms = count( $terms );

		foreach ( $terms as $i => $term ) {
			$url = add_query_arg( [ $this->get_wp_slug() => $term->slug, 'post_type' => $this->get_linked_post_type() ], admin_url( 'edit.php' ) );
			echo '<a href="' . esc_url( $url ) . '">' . esc_html( $term->name ) . '</a>';
			if ( $total_terms !== $i + 1 ) {
				echo ', ';
			}
		}
	}
}