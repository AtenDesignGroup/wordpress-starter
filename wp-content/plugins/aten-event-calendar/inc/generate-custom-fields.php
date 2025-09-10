<?php
/**
 * This file registers custom fields for the Aten Event Calendar plugin.
 *
 * @package Aten_Event_Calendar
 */

/**
 * Register custom fields for custom post types using ACF.
 */
function aten_events_register_custom_fields() {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	/**
	 * Event Custom Fields
	 */
	acf_add_local_field_group( 
		array(
			'key' => 'group_67dc491ae8742',
			'title' => 'Event Fields',
			'fields' => array(
				array(
					'key' => 'field_68af43de5d4e6',
					'label' => 'Event Scheduling Details',
					'name' => 'event_scheduling_details',
					'aria-label' => '',
					'type' => 'group',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'relevanssi_exclude' => 0,
					'layout' => 'table',
					'acfe_seamless_style' => 0,
					'acfe_group_modal' => 0,
					'sub_fields' => array(
						array(
							'key' => 'field_68af433b07bfa',
							'label' => 'Event Start',
							'name' => 'event_start',
							'aria-label' => '',
							'type' => 'date_time_picker',
							'instructions' => '',
							'required' => 1,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'relevanssi_exclude' => 0,
							'display_format' => 'm/d/Y g:i A',
							'return_format' => 'Y-m-d H:i:s',
							'first_day' => 0,
							'default_to_current_date' => 0,
							'allow_in_bindings' => 0,
						),
						array(
							'key' => 'field_68af4a78615da',
							'label' => 'Event End',
							'name' => 'event_end',
							'aria-label' => '',
							'type' => 'date_time_picker',
							'instructions' => '',
							'required' => 1,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'relevanssi_exclude' => 0,
							'display_format' => 'm/d/Y g:i A',
							'return_format' => 'Y-m-d H:i:s',
							'first_day' => 0,
							'default_to_current_date' => 0,
							'allow_in_bindings' => 0,
						),
					),
					'acfe_group_modal_close' => 0,
					'acfe_group_modal_button' => '',
					'acfe_group_modal_size' => 'large',
				),
			),
			'location' => array(
				array(
					array(
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'event',
					),
				),
			),
			'menu_order' => 0,
			'position' => 'acf_after_title',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
			'active' => true,
			'description' => '',
			'show_in_rest' => 0,
			'acfe_display_title' => '',
			'acfe_autosync' => array(
				0 => 'json',
			),
			'acfe_form' => 0,
			'acfe_meta' => '',
			'acfe_note' => '',
		) 
	);

	/** 
	 * Event Custom Taxonomy UI Fields 
	 */
	acf_add_local_field_group( 
		array(
			'key' => 'group_67e2e1bdddba0',
			'title' => 'Event Taxonomies',
			'fields' => array(
				array(
					'key' => 'field_67e2e1be25488',
					'label' => 'Event Type',
					'name' => 'event_type',
					'aria-label' => '',
					'type' => 'taxonomy',
					'instructions' => '',
					'required' => 1,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'relevanssi_exclude' => 0,
					'taxonomy' => 'event-type',
					'add_term' => 0,
					'save_terms' => 1,
					'load_terms' => 1,
					'return_format' => 'id',
					'field_type' => 'select',
					'allow_null' => 1,
					'acfe_bidirectional' => array(
						'acfe_bidirectional_enabled' => '0',
					),
					'allow_in_bindings' => 0,
					'bidirectional' => 0,
					'multiple' => 0,
					'bidirectional_target' => array(
					),
				),
				array(
					'key' => 'field_67e2e21525489',
					'label' => 'Event Topics',
					'name' => 'event_topics',
					'aria-label' => '',
					'type' => 'taxonomy',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'relevanssi_exclude' => 0,
					'taxonomy' => 'event-topic',
					'add_term' => 1,
					'save_terms' => 1,
					'load_terms' => 1,
					'return_format' => 'id',
					'field_type' => 'multi_select',
					'allow_null' => 1,
					'acfe_bidirectional' => array(
						'acfe_bidirectional_enabled' => '0',
					),
					'allow_in_bindings' => 0,
					'bidirectional' => 0,
					'multiple' => 0,
					'bidirectional_target' => array(
					),
				),
				array(
					'key' => 'field_67e2e23b2548a',
					'label' => 'Location',
					'name' => 'location',
					'aria-label' => '',
					'type' => 'taxonomy',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'taxonomy' => 'event-location',
					'add_term' => 0,
					'save_terms' => 1,
					'load_terms' => 1,
					'return_format' => 'id',
					'field_type' => 'select',
					'allow_null' => 1,
					'allow_in_bindings' => 0,
					'bidirectional' => 0,
					'multiple' => 0,
					'bidirectional_target' => array(
					),
				),
			),
			'location' => array(
				array(
					array(
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'event',
					),
				),
			),
			'menu_order' => 0,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
			'active' => true,
			'description' => '',
			'show_in_rest' => 0,
			'acfe_display_title' => '',
			'acfe_autosync' => array(
				0 => 'json',
			),
			'acfe_form' => 0,
			'acfe_meta' => '',
			'acfe_note' => '',
		) 
	);

	/**
	 * Related Event Fields
	 */
	acf_add_local_field_group( 
		array(
			'key' => 'group_6839f46ade7fe',
			'title' => 'Related Events',
			'fields' => array(
				array(
					'key' => 'field_6839f46ae1981',
					'label' => 'Event Posts',
					'name' => 'related_events',
					'aria-label' => '',
					'type' => 'relationship',
					'instructions' => 'Select up to 3 related events or events. If no related posts are selected, the related events section will not display for this post.',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'relevanssi_exclude' => 0,
					'post_type' => array(
						0 => 'event',
					),
					'post_status' => array(
						0 => 'publish',
					),
					'taxonomy' => '',
					'filters' => array(
						0 => 'search',
						1 => 'taxonomy',
					),
					'return_format' => 'object',
					'acfe_bidirectional' => array(
						'acfe_bidirectional_enabled' => '0',
					),
					'min' => '',
					'max' => 3,
					'allow_in_bindings' => 1,
					'elements' => '',
					'bidirectional' => 0,
					'bidirectional_target' => array(
					),
				),
			),
			'location' => array(
				array(
					array(
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'event',
					),
				),
			),
			'menu_order' => 0,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'left',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
			'active' => true,
			'description' => '',
			'show_in_rest' => 0,
			'acfe_display_title' => '',
			'acfe_autosync' => array(
				0 => 'json',
			),
			'acfe_form' => 0,
			'acfe_meta' => '',
			'acfe_note' => '',
		) 
	);
}

add_action( 'acf/include_fields', 'aten_events_register_custom_fields' );
