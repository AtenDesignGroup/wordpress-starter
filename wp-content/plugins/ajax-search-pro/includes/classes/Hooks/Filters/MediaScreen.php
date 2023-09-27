<?php

namespace WPDRMS\ASP\Hooks\Filters;

if (!defined('ABSPATH')) die('-1');

class MediaScreen extends AbstractFilter {
	public function handle( $form_fields = array(), $post = null ) {
		$field_value = get_post_meta( $post->ID, '_asp_attachment_text', true );

		if ( $field_value !== '' ) {
			$form_fields['asp_attachment_text'] = array(
				'value' => $field_value,
				'label' => __( 'Content (not editable)' ),
				'helps' => __( 'Parsed content by Ajax Search Pro Media Parser service' ),
				'input'  => 'textarea'
			);
		}

		return $form_fields;
	}
}