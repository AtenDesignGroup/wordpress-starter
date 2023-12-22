<?php

namespace TEC\Events_Pro\Linked_Posts\Contracts;

use WP_Taxonomy;

interface Taxonomy_Interface {
	/**
	 * Add actions related to this controller.
	 *
	 * @since   6.2.0
	 */
	public function add_actions(): void;
	/**
	 * Remove actions related to this controller.
	 *
	 * @since   6.2.0
	 */
	public function remove_actions(): void;

	/**
	 * Add filters related to this controller.
	 *
	 * @since   6.2.0
	 */
	public function add_filters(): void;

	/**
	 * Remove filters related to this controller.
	 *
	 * @since   6.2.0
	 */
	public function remove_filters(): void;

	/**
	 * Sets the taxonomy object.
	 *
	 * @since 6.2.0
	 *
	 * @param WP_Taxonomy $taxonomy_object
	 */
	public function set_taxonomy_object( WP_Taxonomy $taxonomy_object ): void;

	/**
	 * Gets the taxonomy object.
	 *
	 * @since 6.2.0
	 *
	 * @return ?WP_Taxonomy
	 */
	public function get_taxonomy_object(): ?WP_Taxonomy;
	/**
	 * Sets the admin menu registered page.
	 *
	 * @since 6.2.0
	 *
	 * @param string $admin_menu
	 */
	public function set_admin_menu( string $admin_menu ): void;

	/**
	 * Gets the admin menu registered page.
	 *
	 * @since 6.2.0
	 *
	 * @return ?string
	 */
	public function get_admin_menu(): ?string;


	/**
	 * Get the Taxonomy Slug as it's registered in WordPress, normally using a namespace.
	 *
	 * @since 6.2.0
	 *
	 * @return string
	 */
	public function get_wp_slug(): string;

	/**
	 * Get the Taxonomy Slug as it's used in the plugin code, normally without a namespace.
	 *
	 * @since 6.2.0
	 *
	 *
	 * @return string
	 */
	public function get_slug(): string;

	/**
	 * Get the Taxonomy Singular Label without the Linked Post label attached to it.
	 *
	 * @since 6.2.0
	 *
	 * @return string
	 */
	public function get_singular_label_without_linked_post(): ?string;

	/**
	 * Get the Taxonomy Plural Label without the Linked Post label attached to it.
	 *
	 * @since 6.2.0
	 *
	 * @return string
	 */
	public function get_plural_label_without_linked_post(): ?string;

	/**
	 * Get the Taxonomy untranslated slug for rewrite singular.
	 *
	 * @since 6.2.0
	 *
	 * @return ?string
	 */
	public function get_rewrite_slug_singular(): ?string;

	/**
	 * Get the Taxonomy untranslated slug for rewrite plural.
	 *
	 * @since 6.2.0
	 *
	 * @return ?string
	 */
	public function get_rewrite_slug_plural(): ?string;

	/**
	 * Get the Menu Capability required to access the Taxonomy in the WordPress Admin.
	 *
	 * @since 6.2.0
	 *
	 * @return string
	 */
	public function get_menu_capability(): ?string;

	/**
	 * Get the Linked Post Type Slug single as it's used to register the rewrite base.
	 *
	 * @since 6.2.0
	 *
	 * @return string
	 */
	public function get_linked_post_type_rewrite_slug_singular(): ?string;

	/**
	 * Get the Linked Post Type Slug plural as it's used to register the rewrite base.
	 *
	 * @since 6.2.0
	 *
	 * @return string
	 */
	public function get_linked_post_type_rewrite_slug_plural(): ?string;

	/**
	 * Get the Post Type Slug as it's registered in WordPress, normally using a namespace.
	 *
	 * @since 6.2.0
	 *
	 * @return string
	 */
	public function get_linked_post_type(): ?string;

	/**
	 * Get the View Slug as it's used in the plugin code.
	 * This is optional, if your taxonomy doesn't have a view, return null and the Abstract will avoid using it.
	 *
	 * @since 6.2.0
	 *
	 * @return string|null
	 */
	public function get_linked_post_type_view_slug(): ?string;

	/**
	 * Get the Linked Post Type Singular Label.
	 *
	 * @since 6.2.0
	 *
	 * @return string
	 */
	public function get_linked_post_type_label_singular(): ?string;

	/**
	 * Get the Linked Post Type Singular Label in lowercase.
	 *
	 * @since 6.2.0
	 *
	 * @return string
	 */
	public function get_linked_post_type_label_singular_lowercase(): ?string;

	/**
	 * Get the Linked Post Type Plural Label.
	 *
	 * @since 6.2.0
	 *
	 * @return string
	 */
	public function get_linked_post_type_label_plural(): ?string;

	/**
	 * Get the Linked Post Type Plural Label in lowercase.
	 *
	 * @since 6.2.0
	 *
	 * @return string
	 */
	public function get_linked_post_type_label_plural_lowercase(): ?string;

	/**
	 * Get the Linked Post Type Repository.
	 *
	 * @since 6.2.0
	 *
	 * @return \Tribe__Repository__Interface|null
	 */
	public function get_linked_post_type_repository(): ?\Tribe__Repository__Interface;

}