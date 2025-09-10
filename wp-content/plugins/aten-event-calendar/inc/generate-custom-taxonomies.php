<?php
/**
 * This file registers custom taxonomies for the Aten Event Calendar plugin.
 *
 * @package Aten_Event_Calendar
 */

/**
 * Register custom taxonomies.
 */
function aten_events_register_custom_taxonomies() {

	/**
	 * Taxonomy: Event Types.
	 */
	$labels = [
		"name" => esc_html__( "Event Types", "twentytwentyone" ),
		"singular_name" => esc_html__( "Event Type", "twentytwentyone" ),
		"menu_name" => esc_html__( "Event Types", "twentytwentyone" ),
		"all_items" => esc_html__( "All Event Types", "twentytwentyone" ),
		"edit_item" => esc_html__( "Edit Event Type", "twentytwentyone" ),
		"view_item" => esc_html__( "View Event Type", "twentytwentyone" ),
		"update_item" => esc_html__( "Update Event Type name", "twentytwentyone" ),
		"add_new_item" => esc_html__( "Add new Event Type", "twentytwentyone" ),
		"new_item_name" => esc_html__( "New Event Type name", "twentytwentyone" ),
		"parent_item" => esc_html__( "Parent Event Type", "twentytwentyone" ),
		"parent_item_colon" => esc_html__( "Parent Event Type:", "twentytwentyone" ),
		"search_items" => esc_html__( "Search Event Types", "twentytwentyone" ),
		"popular_items" => esc_html__( "Popular Event Types", "twentytwentyone" ),
		"separate_items_with_commas" => esc_html__( "Separate Event Types with commas", "twentytwentyone" ),
		"add_or_remove_items" => esc_html__( "Add or remove Event Types", "twentytwentyone" ),
		"choose_from_most_used" => esc_html__( "Choose from the most used Event Types", "twentytwentyone" ),
		"not_found" => esc_html__( "No Event Types found", "twentytwentyone" ),
		"no_terms" => esc_html__( "No Event Types", "twentytwentyone" ),
		"items_list_navigation" => esc_html__( "Event Types list navigation", "twentytwentyone" ),
		"items_list" => esc_html__( "Event Types list", "twentytwentyone" ),
		"back_to_items" => esc_html__( "Back to Event Types", "twentytwentyone" ),
		"name_field_description" => esc_html__( "The name is how it appears on your site.", "twentytwentyone" ),
		"parent_field_description" => esc_html__( "Assign a parent term to create a hierarchy. The term Jazz, for example, would be the parent of Bebop and Big Band.", "twentytwentyone" ),
		"slug_field_description" => esc_html__( "The slug is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.", "twentytwentyone" ),
		"desc_field_description" => esc_html__( "The description is not prominent by default; however, some themes may show it.", "twentytwentyone" ),
	];
	
	$args = [
		"label" => esc_html__( "Event Types", "twentytwentyone" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'event-type', 'with_front' => true, ],
		"show_admin_column" => false,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "event-type",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => false,
		"sort" => false,
		"show_in_graphql" => false,
		"meta_box_cb" => false,
	];
	register_taxonomy( "event-type", [ "event" ], $args );

	/**
	 * Taxonomy: Event Topics.
	 */
	$labels = [
		"name" => esc_html__( "Event Topics", "twentytwentyone" ),
		"singular_name" => esc_html__( "Event Topic", "twentytwentyone" ),
		"menu_name" => esc_html__( "Event Topics", "twentytwentyone" ),
		"all_items" => esc_html__( "All Event Topics", "twentytwentyone" ),
		"edit_item" => esc_html__( "Edit Event Topic", "twentytwentyone" ),
		"view_item" => esc_html__( "View Event Topic", "twentytwentyone" ),
		"update_item" => esc_html__( "Update Event Topic name", "twentytwentyone" ),
		"add_new_item" => esc_html__( "Add new Event Topic", "twentytwentyone" ),
		"new_item_name" => esc_html__( "New Event Topic name", "twentytwentyone" ),
		"parent_item" => esc_html__( "Parent Event Topic", "twentytwentyone" ),
		"parent_item_colon" => esc_html__( "Parent Event Topic:", "twentytwentyone" ),
		"search_items" => esc_html__( "Search Event Topics", "twentytwentyone" ),
		"popular_items" => esc_html__( "Popular Event Topics", "twentytwentyone" ),
		"separate_items_with_commas" => esc_html__( "Separate Event Topics with commas", "twentytwentyone" ),
		"add_or_remove_items" => esc_html__( "Add or remove Event Topics", "twentytwentyone" ),
		"choose_from_most_used" => esc_html__( "Choose from the most used Event Topics", "twentytwentyone" ),
		"not_found" => esc_html__( "No Event Topics found", "twentytwentyone" ),
		"no_terms" => esc_html__( "No Event Topics", "twentytwentyone" ),
		"items_list_navigation" => esc_html__( "Event Topics list navigation", "twentytwentyone" ),
		"items_list" => esc_html__( "Event Topics list", "twentytwentyone" ),
		"back_to_items" => esc_html__( "Back to Event Topics", "twentytwentyone" ),
		"name_field_description" => esc_html__( "The name is how it appears on your site.", "twentytwentyone" ),
		"parent_field_description" => esc_html__( "Assign a parent term to create a hierarchy. The term Jazz, for example, would be the parent of Bebop and Big Band.", "twentytwentyone" ),
		"slug_field_description" => esc_html__( "The slug is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.", "twentytwentyone" ),
		"desc_field_description" => esc_html__( "The description is not prominent by default; however, some themes may show it.", "twentytwentyone" ),
	];

	$args = [
		"label" => esc_html__( "Event Topics", "twentytwentyone" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => false,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'event-topic', 'with_front' => true, ],
		"show_admin_column" => false,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "event-topic",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => false,
		"sort" => false,
		"show_in_graphql" => false,
		"meta_box_cb" => false,
	];
	register_taxonomy( "event-topic", [ "event" ], $args );

	/**
	 * Taxonomy: Locations.
	 */
	$labels = [
		"name" => esc_html__( "Locations", "twentytwentyone" ),
		"singular_name" => esc_html__( "Location", "twentytwentyone" ),
	];
	
	$args = [
		"label" => esc_html__( "Locations", "twentytwentyone" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'event-location', 'with_front' => true, ],
		"show_admin_column" => false,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "event-location",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => false,
		"sort" => false,
		"show_in_graphql" => false,
		"meta_box_cb" => false,
	];
	register_taxonomy( "event-location", [ "event" ], $args );
}
add_action( 'init', 'aten_events_register_custom_taxonomies' );