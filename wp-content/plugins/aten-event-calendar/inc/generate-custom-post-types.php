<?php
/**
 * This file registers custom post types for the Aten Event Calendar plugin.
 *
 * @package Aten_Event_Calendar
 */

/**
 * Register custom post types.
 */
function aten_events_register_custom_post_types() {

	/**
	 * Post Type: Events.
	 */

	$labels = [
		"name" => esc_html__( "Events", "twentytwentyone" ),
		"singular_name" => esc_html__( "Event", "twentytwentyone" ),
		"menu_name" => esc_html__( "Events", "twentytwentyone" ),
		"all_items" => esc_html__( "All Events", "twentytwentyone" ),
		"add_new" => esc_html__( "Add new", "twentytwentyone" ),
		"add_new_item" => esc_html__( "Add new Event", "twentytwentyone" ),
		"edit_item" => esc_html__( "Edit Event", "twentytwentyone" ),
		"new_item" => esc_html__( "New Event", "twentytwentyone" ),
		"view_item" => esc_html__( "View Event", "twentytwentyone" ),
		"view_items" => esc_html__( "View Events", "twentytwentyone" ),
		"search_items" => esc_html__( "Search Events", "twentytwentyone" ),
		"not_found" => esc_html__( "No Events found", "twentytwentyone" ),
		"not_found_in_trash" => esc_html__( "No Events found in trash", "twentytwentyone" ),
		"parent" => esc_html__( "Parent Event:", "twentytwentyone" ),
		"featured_image" => esc_html__( "Featured image for this Event", "twentytwentyone" ),
		"set_featured_image" => esc_html__( "Set featured image for this Event", "twentytwentyone" ),
		"remove_featured_image" => esc_html__( "Remove featured image for this Event", "twentytwentyone" ),
		"use_featured_image" => esc_html__( "Use as featured image for this Event", "twentytwentyone" ),
		"archives" => esc_html__( "Event archives", "twentytwentyone" ),
		"insert_into_item" => esc_html__( "Insert into Event", "twentytwentyone" ),
		"uploaded_to_this_item" => esc_html__( "Upload to this Event", "twentytwentyone" ),
		"filter_items_list" => esc_html__( "Filter Events list", "twentytwentyone" ),
		"items_list_navigation" => esc_html__( "Events list navigation", "twentytwentyone" ),
		"items_list" => esc_html__( "Events list", "twentytwentyone" ),
		"attributes" => esc_html__( "Events attributes", "twentytwentyone" ),
		"name_admin_bar" => esc_html__( "Event", "twentytwentyone" ),
		"item_published" => esc_html__( "Event published", "twentytwentyone" ),
		"item_published_privately" => esc_html__( "Event published privately.", "twentytwentyone" ),
		"item_reverted_to_draft" => esc_html__( "Event reverted to draft.", "twentytwentyone" ),
		"item_trashed" => esc_html__( "Event trashed.", "twentytwentyone" ),
		"item_scheduled" => esc_html__( "Event scheduled", "twentytwentyone" ),
		"item_updated" => esc_html__( "Event updated.", "twentytwentyone" ),
		"template_name" => esc_html__( "Single Event: Event", "twentytwentyone" ),
		"parent_item_colon" => esc_html__( "Parent Event:", "twentytwentyone" ),
	];

	$args = [
		"label" => esc_html__( "Events", "twentytwentyone" ),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"rest_namespace" => "wp/v2",
		"has_archive" => "events",
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"delete_with_user" => false,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"can_export" => true,
		"rewrite" => [ "slug" => "events", "with_front" => true ],
		"query_var" => true,
		"menu_icon" => "dashicons-calendar-alt",
		"supports" => [ "title", "editor", "thumbnail", "excerpt", "custom-fields" ],
		"taxonomies" => [ "session-type", "session-topic", "session-location", "session-year", "session-time-of-day", "session-presenter", "post_tag" ],
		"show_in_graphql" => false,
	];

	register_post_type( "event", $args );

}

add_action( 'init', 'aten_events_register_custom_post_types' );

/**
 * Remove block editor support for select post types.
 */
function aten_events_remove_block_editor() {
	$post_types = array(
		'event',
	);

	foreach ( $post_types as $post_type ) {
		remove_post_type_support( $post_type, 'editor' );
	}
}
add_action( 'init', 'aten_events_remove_block_editor', 100 );
