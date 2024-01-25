<?php

/**
 * Example Block template for CPT posts
 * Sets default blocks for CPT 
 */
function aten_fse_custom_post_block_template() {
	$post_type_object = get_post_type_object( 'custom-cpt' );
	$post_type_object->template = array(
		// Single column stacked on top
		array(
			'core/column',
			array(),
			array(
				// Add your custom Hero Subtitle Component block here
				array( 'acf/subtitle', array() ),
			),
		),
		// Two-column layout
		array(
			'core/columns',
			array(),
			array(
				// First column
				array(
					'core/column',
					array(),
					array(
						array( 'core/paragraph', array( 'content' => 'Sed purus auctor amet interdum adipiscing iaculis arcu. Duis auctor in risus aliquam quis velit turpis urna. Nisl aliquam vitae fames eget porta risus cras imperdiet metus. Id arcu sollicitudin tortor maecenas.' ) ),
						array( 'core/paragraph', array( 'content' => 'Sed purus auctor amet interdum adipiscing iaculis arcu. Duis auctor in risus aliquam quis velit turpis urna. Nisl aliquam vitae fames eget porta risus cras imperdiet metus. Id arcu sollicitudin tortor maecenas.' ) ),
					),
				),
				// Second column
				array(
					'core/column',
					array(),
					array(
						// Example shortcode block
						array( 'core/shortcode', array( 'shortcode' => 'your_shortcode_1' ) ),
					),
				),
			),
		),
	);
}
add_action( 'init', 'aten_fse_custom_post_block_template' );
