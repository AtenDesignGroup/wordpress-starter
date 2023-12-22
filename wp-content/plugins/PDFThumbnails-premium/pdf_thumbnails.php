<?php

/**
 * Plugin Name: PDF Thumbnails Premium
 * Plugin URI: https://wp-pdf.com/
 * Description: Generate thumbnails for PDFs uploaded to the Media Library.
 * Version: 1.4.3
 * Author: Lever Technology, LLC
 * Author URI: https://wp-pdf.com/
 * Text Domain: pdfth-thumbnails
 * License: Premium Paid per WordPress site
 *
 * Do not copy, modify, or redistribute without authorization from author Lesterland Ltd (contact@wp-pdf.com)
 *
 * You need to have purchased a license to install this software on each website.
 *
 * You are not authorized to use, modify, or distribute this software beyond the single site license(s) that you
 * have purchased.
 *
 * You must not remove or alter any copyright notices on any and all copies of this software.
 *
 * This software is NOT licensed under one of the public "open source" licenses you may be used to on the web.
 *
 * For full license details, and to understand your rights, please refer to the agreement you made when you purchased it
 * from our website at https://wp-pdf.com/
 *
 * THIS SOFTWARE IS SUPPLIED "AS-IS" AND THE LIABILITY OF THE AUTHOR IS STRICTLY LIMITED TO THE PURCHASE PRICE YOU PAID
 * FOR YOUR LICENSE.
 *
 * Please report violations to contact@wp-pdf.com
 *
 * Copyright Lesterland Ltd, registered company in the UK number 08553880
 *
 */

require_once( plugin_dir_path(__FILE__).'/core/core_pdf_thumbnails.php' );

class pdfth_pdf_thumbnails extends pdfth_core_pdf_thumbnails {

	protected $PLUGIN_VERSION = '1.4.3';

	protected $WPPDF_STORE_URL = 'http://wp-pdf.com/';
	protected $WPPDF_ITEM_NAME = 'PDF Thumbnails Premium';
	protected $WPPDF_ITEM_ID = 9157;

	protected function __construct() {
		$this->add_actions();
		register_activation_hook($this->my_plugin_basename(), array( $this, 'pdfth_activation_hook' ) );
	}

	// Singleton
	private static $instance = null;
	
	public static function get_instance() {
		if (null == self::$instance) {
			self::$instance = new self;
		}
		return self::$instance;
	}

    public function pdfth_activation_hook($network_wide) {
    }

    public function admin_enqueue_scripts() {

	    if (strpos($_SERVER['REQUEST_URI'], '/wp-admin/upload.php') !== false
	            || strpos($_SERVER['REQUEST_URI'], '/wp-admin/post.php') !== false
	            || strpos($_SERVER['REQUEST_URI'], '/wp-admin/post-new.php') !== false) {
		    wp_register_script( 'pdfth_generator_js', $this->my_plugin_url().'js/pdfth-generator.js', array('jquery'));
		    wp_enqueue_script( 'pdfth_generator_js' );
	    }
		
	}

	protected function add_actions() {
		add_action( 'admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'), 5, 0 );

		add_filter( 'query_vars', array($this, 'plugin_add_trigger') );
		add_action( 'template_redirect', array($this, 'plugin_trigger_check') );

		add_filter( 'attachment_fields_to_edit', array($this, 'attachment_fields_to_edit'), 20, 2 );
		add_filter( 'attachment_fields_to_save', array($this, 'attachment_fields_to_save'), 20, 2 );

		add_filter( 'wp_mime_type_icon', array($this, 'pdfth_wp_mime_type_icon'), 10, 3 );
		add_filter( 'wp_get_attachment_image_src', array($this,'pdfth_wp_get_attachment_image_src'), 10, 4 );

		add_action( 'admin_footer-post-new.php', array($this, 'override_filter_object') );
		add_action( 'admin_footer-post.php', array($this, 'override_filter_object') );

		add_filter( 'ajax_query_attachments_args', array( $this,'ajax_query_attachments_args' ), 100, 1 );

		add_filter( 'media_send_to_editor', array($this, 'media_send_to_editor'), 100, 3 );

		if (is_admin()) {
			add_action( 'admin_init', array($this, 'admin_init'), 5, 0 );

			add_action($this->is_multisite_and_network_activated() ? 'network_admin_menu' : 'admin_menu', array($this, 'pdfth_admin_menu'));

			if ($this->is_multisite_and_network_activated()) {
				add_action('network_admin_edit_'.$this->get_options_menuname(), array($this, 'pdfth_save_network_options'));
			}

			add_filter($this->is_multisite_and_network_activated() ? 'network_admin_plugin_action_links' : 'plugin_action_links', array($this, 'pdfth_plugin_action_links'), 10, 2 );

			add_action('wp_ajax_pdfth_get_all_pdfs', array($this, 'pdfth_get_all_pdfs'));
		}

	}

	public function admin_init() {
		register_setting( $this->get_options_pagename(), $this->get_options_name(), Array($this, 'pdfth_options_validate') );

		$edd_updater = $this->edd_plugin_updater();
		$edd_updater->setup_hooks();
	}

	// Ensure we can load a very basic WP framework within our iframe

	function plugin_add_trigger($vars) {
		$vars[] = 'pdfth_is_iframe';
		$vars[] = 'pdfth_is_thumbnail_receive';
		return $vars;
	}

	function plugin_trigger_check() {
		if (get_query_var('pdfth_is_iframe') == '1') {
			require_once( plugin_dir_path(__FILE__).'/iframe.php' );
			exit;
		}
		if (get_query_var('pdfth_is_thumbnail_receive') == '1') {
			require_once( plugin_dir_path(__FILE__).'/thumbnail_receive.php' );
			exit;
		}
	}

	// Media Library integration

	public function attachment_fields_to_edit($form_fields, $post) {
		if ($post->post_mime_type == 'application/pdf') {

			// Output the 'generate thumbnail widget'
			
			$html = '';
			$spanid = 'pdfth_gen_'.$post->ID;
			$pdfurl = apply_filters('pdfth_pdf_direct_download_url', set_url_scheme(wp_get_attachment_url($post->ID)));

			
			$nonce = wp_create_nonce($pdfurl.'|'.$post->ID);

			// Don't js_esc because that messes with urls containing &, unnecessarily
			$jscode = sprintf("pdfth_generate_thumbnail('%s', '%s', '%s', '%s', '%s')",
				$spanid,
				set_url_scheme(get_site_url()),
				$pdfurl,
				$post->ID,
				$nonce
			);

			$html = sprintf("<span id='%s'>Initializing...</span><script> %s; </script>",
				$spanid,
				$jscode
			);
			
			$form_fields['pdfth_generatethumbnail'] = array(
				'input' => 'html',
				'html' => $html,
				'label' => 'PDF Thumbnail');

			// Now replicate the options available for images

			if ( !get_post_meta( $post->ID, '_thumbnail_id', true ) ) return $form_fields;
			$val = get_post_meta( $post->ID, '_pdfth_attach_linkto', true );
			if ( empty( $val ) ) {
				$val = get_option( 'image_default_link_type' );
				if (!$val) $val = 'file';
				update_post_meta( $post->ID, '_pdfth_attach_linkto', $val );
			}
			$form_fields['pdfth_attach_linkto']['label'] = 'Link To';
			$form_fields['pdfth_attach_linkto']['input'] = 'html';
			$form_fields['pdfth_attach_linkto']['html'] =
				'<select name='. "attachments[{$post->ID}][pdfth_attach_linkto]" .'">'.
				'<option ' .selected( $val, 'file', false ). ' value="file">'. __('PDF Media File'). '</option>'.
				'<option ' .selected( $val, 'post', false ). ' value="post">'. __('Attachment Page'). '</option>'.
				'<option ' .selected( $val, 'none', false ). ' value="none">'. __('None'). ' ('. __('Embed directly'). ')</option>'.
				'</select>'. "\n";

			$val = get_post_meta( $post->ID, '_pdfth_attach_size', true );
			if ( empty( $val ) ) {
				$val = get_option( 'image_default_size' );
				if (!$val) $val = 'medium';
				update_post_meta( $post->ID, '_pdfth_attach_size', $val );
			}
			$form_fields['pdfth_attach_size']['label'] = __( 'Media' );
			$form_fields['pdfth_attach_size']['input'] = 'html';
			$form_fields['pdfth_attach_size']['html'] = '<select name='. "attachments[{$post->ID}][pdfth_attach_size]" .'">';
			$sizes = apply_filters( 'image_size_names_choose', array(
				'thumbnail'	=> __('Thumbnail'),
				'medium'	=> __('Medium size'),
				'large'		=> __('Large size'),
				'full'		=> __('Full Size'),
				'url'		=> __('URL'),
				'title'		=> __('Title'),
				'caption'	=> __('Caption'),
			));
			foreach ( $sizes as $slug => $name ) :
				if ( $slug == 'url' || $slug == 'title' || $slug == 'caption' ){
					$form_fields['pdfth_attach_size']['html'] .=
						'<option ' .selected( $val, $slug, false ). ' value="'.esc_attr( $slug ).'">'. esc_html( $name ). '</option>';
				} elseif ( $thumbdata = wp_get_attachment_image_src( $post->ID, $slug ) ){
					$form_fields['pdfth_attach_size']['html'] .=
						'<option ' .selected( $val, $slug, false ). ' value="'.esc_attr( $slug ).'">'. esc_html( $name ). ' &ndash; '.$thumbdata[1].' &times; '.$thumbdata[2].'</option>';
				}
			endforeach;
			$form_fields['pdfth_attach_size']['html'] .= '</select>'. "\n";

			$val = get_post_meta( $post->ID, '_pdfth_attach_align', true );
			if ( empty( $val ) ) {
				$val = get_option( 'image_default_align' );
				if (!$val) $val = 'none';
				update_post_meta( $post->ID, '_pdfth_attach_align', $val );
			}
			$form_fields['pdfth_attach_align']['label'] = __('Alignment');
			$form_fields['pdfth_attach_align']['input'] = 'html';
			$form_fields['pdfth_attach_align']['html'] =
				'<select name='. "attachments[{$post->ID}][pdfth_attach_align]" .'">'.
				'<option ' .selected( $val, 'left', false ). ' value="left">'. __('Left'). '</option>'.
				'<option ' .selected( $val, 'center', false ). ' value="center">'. __('Center'). '</option>'.
				'<option ' .selected( $val, 'right', false ). ' value="right">'. __('Right'). '</option>'.
				'<option ' .selected( $val, 'none', false ). ' value="none">'. __('None'). '</option>'.
				'</select>'." \n"
				.'<style type="text/css">'
				//.'attachment-details[data-id="'.$post->ID.'"]:after { content:"Attachment Display Settings"; font-weight:bold; color:#777; padding:20px 0 0; text-transform:uppercase; clear:both; display:block; } '
				.'.attachment-display-settings ' //.attachment-compat,
				//.'#post-body tr.compat-field-pdfth_attach_linkto, '
				//.'#post-body tr.compat-field-pdfth_attach_size, '
				//.'#post-body tr.compat-field-pdfth_attach_align '
				.' { display:none!important; }'
				.'</style>'."\n";

		}
		return $form_fields;
	}

	public function attachment_fields_to_save( $post, $attachment ){
		if ( isset( $attachment['pdfth_attach_linkto'] ) )
			update_post_meta( $post['ID'], '_pdfth_attach_linkto', $attachment['pdfth_attach_linkto'] );
		if ( isset( $attachment['pdfth_attach_size'] ) )
			update_post_meta( $post['ID'], '_pdfth_attach_size', $attachment['pdfth_attach_size'] );
		if ( isset( $attachment['pdfth_attach_align'] ) )
			update_post_meta( $post['ID'], '_pdfth_attach_align', $attachment['pdfth_attach_align'] );
		return $post;
	}

	// Display thumbnail instead of default document icon
	public function pdfth_wp_mime_type_icon($icon, $mime, $attachment_id) {
		if ( strpos( $_SERVER[ 'REQUEST_URI' ], '/wp-admin/upload.php' ) === false && $mime === 'application/pdf' ){
			$thumbnail = wp_get_attachment_image_src($attachment_id, 'medium');
			if ($thumbnail) {
				$icon = $thumbnail[0];
			}
		}
		return $icon;
	}

	public function pdfth_wp_get_attachment_image_src($image, $attachment_id, $size, $icon) {
		if (get_post_mime_type($attachment_id) === 'application/pdf') {
			$thumbnail_id = get_post_meta($attachment_id, '_thumbnail_id', true);
			if ($thumbnail_id){
				$get_image = wp_get_attachment_image_src($thumbnail_id, $size);
				$image = array($get_image[0], $get_image[1], $get_image[2]);
			}
		}
		return $image;
	}

	public function media_send_to_editor($html, $attach_id, $attachment) {
		if ($attach_id && get_post_mime_type($attach_id) === 'application/pdf'){
			$linkto = get_post_meta($attach_id, '_pdfth_attach_linkto', true);
			$is_secure = false;
			if ($linkto === 'file') {
				$attach_url = wp_get_attachment_url($attach_id);
				$is_secure = apply_filters('pdfth_pdf_is_secure', $attach_url);
			}
			elseif ($linkto === 'post') {
				$attach_url = get_attachment_link($attach_id);
			}
			else {
				$attach_url = '';
			}

			$attach_title = isset( $attachment['post_title'] ) ? $attachment['post_title'] : '';
			$attach_caption = isset( $attachment['post_excerpt'] ) ? $attachment['post_excerpt'] : '';
			$size = get_post_meta( $attach_id, '_pdfth_attach_size', true );
			$thumbnail_id = get_post_meta( $attach_id, '_thumbnail_id', true );
			$thumbnail = wp_get_attachment_image_src( $thumbnail_id, $size );

			if ($size === 'url') {
				$attach_output = $attach_url;
			} elseif ($size === 'title'){
				$attach_output = $attach_title;
			} elseif ($size === 'caption'){
				$attach_output = $attach_caption;
			} elseif($thumbnail) {
				$align = get_post_meta($attach_id, '_pdfth_attach_align', true);
				if ($attach_caption) {
					$align = '';
				} elseif (!$align) {
					$align = 'none';
				}
				$attach_output = '<img src="'. $thumbnail[0] .'" alt="'.get_post_meta( $thumbnail_id, '_wp_attachment_image_alt', true ).'" width="'. $thumbnail[1] .'" height="'. $thumbnail[2] .'" class="'.( $align ? 'align' .$align. ' ' : '' ). 'size-' .esc_attr( $size ). ' wp-image-'.$thumbnail_id.'" />';
				$attach_output = apply_filters( 'pdfth_filter_attachment_output', $attach_output, $thumbnail_id, $thumbnail, $size, $align );
			} else {
				$attach_output = '';
			}
			if ($attach_url && $attach_output) {
			    if ($is_secure === true) {
				    $html = '[pdf-direct-link url="'.$attach_url.'"] '. $attach_output . '[/pdf-direct-link]';
                }
                else {
	                $html = '<a class="link-to-pdf" href="' . $attach_url . '" rel="attachment wp-att-' . esc_attr( $attach_id ) . '" title="' . esc_attr( $attach_title ) . '" target="_blank">' . $attach_output . '</a>';
                }
			}
			if ($thumbnail && $attach_caption) {
				$html = '[caption id="attachment_'.esc_attr( $attach_id ).'" align="align' .$align. '" width="'.$thumbnail[1].'"]'.$html.' '.$attach_caption.'[/caption]';
			}
		}
		return $html;
	}

	// Media Library JS overrides

	public function override_filter_object() { // Override relevant media manager javascript functions
		?>
		<script type="text/javascript">
			l10n = wp.media.view.l10n = typeof _wpMediaViewsL10n === 'undefined' ? {} : _wpMediaViewsL10n;
			wp.media.view.AttachmentFilters.Uploaded.prototype.createFilters = function() {
				var type = this.model.get('type'),
					types = wp.media.view.settings.mimeTypes,
					text;
				if ( types && type ) text = types[ type ];
				filters = {
					all: { text: text || l10n.allMediaItems, props: { uploadedTo: null, orderby: 'date', order: 'DESC' }, priority: 10 },
					uploaded: { text: l10n.uploadedToThisPost, props: { uploadedTo: wp.media.view.settings.post.id, orderby: 'menuOrder', order: 'ASC' }, priority: 20 }
				};
				if ( this.options.controller._state.indexOf( 'featured-image' ) !== -1 ) {
					filters.all = { text: <?php echo '\''.__('Image').' & '.__( 'PDF' ).'\''; ?>, props: { type: 'image_n_pdf', uploadedTo: null, orderby: 'date', order: 'DESC' }, priority: 20 };
					filters.image = { text: <?php echo '\''.__('Image').'\''; ?>, props: { type: 'image', uploadedTo: null, orderby: 'date', order: 'DESC' }, priority: 20 };
					filters.uploaded = { text: l10n.uploadedToThisPost, props: { type: 'image_n_pdf', uploadedTo: wp.media.view.settings.post.id, orderby: 'menuOrder', order: 'ASC' }, priority: 10 };
					filters.unattached = { text: l10n.unattached, props: { 	status: null, uploadedTo: 0, type: null, orderby: 'menuOrder', order: 'ASC' }, priority: 50 };
				}
				this.filters = filters;
			}; // End create filters

			jQuery(function($) {
				wp.media.featuredImage.frame().on( 'ready', function(){
					$( 'select.attachment-filters [value="uploaded"]' ).attr( 'selected', true ).parent().trigger('change'); // Change the default view to "Uploaded to this post".
				});
			});
		</script>
		<?php
	}


	public function ajax_query_attachments_args( $query ) { // Hide thumbnail files in the library.

		$featured = 'thumbnail'; // thumbnail or true
		$hidethumb = false;

		// 's', 'order', 'orderby', 'posts_per_page', 'paged', 'post_mime_type',
		// 'post_parent', 'post__in', 'post__not_in', 'year', 'monthnum'
		if ( isset( $query[ 'post_mime_type' ] ) && $query['post_mime_type'] == 'image_n_pdf' ){
			$post_parent = ( isset( $query['post_parent'] ) && $query['post_parent'] ? '&post_parent='.$query['post_parent'] : '' );
			if ( $featured == 'thumbnail' ){
				$post__in = array();
				$get_posts = get_posts( 'posts_per_page=-1&post_type=attachment&post_mime_type=image'.$post_parent );
				if ( $get_posts ): foreach ( $get_posts as $get ):
					$post__in[] = $get->ID;
				endforeach; endif;
				$get_posts = get_posts( 'posts_per_page=-1&post_type=attachment&post_mime_type=application/pdf'.$post_parent );
				if ( $get_posts ): foreach ( $get_posts as $get ):
					if( $thumbnail_id = get_post_meta( $get->ID, '_thumbnail_id', true ) ) $post__in[] = $thumbnail_id;
				endforeach; endif;
				$query['post_parent'] = false;
				$query['post__in'] = $post__in;
				$query['post_mime_type'] = array('image');
				return $query;
			} elseif ( $featured == 'true' ){
				$query['post_mime_type'] = array('image','application/pdf');
			}
		}
		if ( $hidethumb ){
			if ( isset( $query['post_parent'] ) && $query['post_parent'] ){
				$post__in = array();
				$get_posts = get_posts( 'posts_per_page=-1&post_type=attachment&post_parent='.$query['post_parent'] );
				if ( $get_posts ): foreach ( $get_posts as $get ):
					$post__in[] = $get->ID;
					$thumbnail_id = get_post_meta( $get->ID, '_thumbnail_id', true );
					if( get_post_mime_type( $get->ID ) == 'application/pdf' && $thumbnail_id ) $post__in[] = $thumbnail_id;
				endforeach; endif;
				if ( $post__in ){
					$query['post_parent'] = false;
					$query['post__in'] = $post__in;
				}
			}
			return $query;
		}
		/* $get_posts = get_posts( 'posts_per_page=-1&post_type=attachment&post_mime_type=application/pdf' );
		$post__not_in = array();
		if ( $get_posts ): foreach ( $get_posts as $get ):
			if( $thumbnail_id = get_post_meta( $get->ID, '_thumbnail_id', true ) ) $post__not_in[] = $thumbnail_id;
		endforeach; endif;
		$query['post__not_in'] = $post__not_in; */

		return $query;
	}

	// Fetch all PDFs

	public function pdfth_get_all_pdfs() {

		if (!current_user_can('manage_options')) {
			wp_die('Not authorized');
		}

		$onlynew = isset($_POST['pdfth_onlynew']) && $_POST['pdfth_onlynew'];

		$query_args = array(
			'post_type'      => 'attachment',
			'post_mime_type' => 'application/pdf',
			'post_status'    => 'inherit',
			'posts_per_page' => -1,
		);

		$query_pdfs = new WP_Query( $query_args );
		$site_url = set_url_scheme(get_site_url());

		$pdfs = array();
		foreach ( $query_pdfs->posts as $pdf ) {

			$thumbnail = wp_get_attachment_image_src($pdf->ID, 'medium');

			if (!$thumbnail || !$onlynew) {
				$spanid = 'pdfth_gen_' . $pdf->ID;
				$pdfurl = apply_filters( 'pdfth_pdf_direct_download_url', set_url_scheme( wp_get_attachment_url( $pdf->ID ) ) );

				$nonce = wp_create_nonce( $pdfurl . '|' . $pdf->ID );

				$title = basename($pdfurl);
				if (isset($pdf->post_title) && $pdf->post_title != '') {
					$title = $pdf->post_title;
				}
				elseif (isset($pdf->post_name) && $pdf->post_name != '') {
					$title = $pdf->post_name;
				}

				$pdfs[] = array(
					$spanid,
					$site_url,
					$pdfurl,
					$pdf->ID,
					$nonce,
					$title
				);
			}
		}

		wp_die(json_encode($pdfs));
	}


	// AUX

	protected function my_plugin_basename() {
		$basename = plugin_basename(__FILE__);
		if ('/'.$basename == __FILE__) { // Maybe due to symlink
			$basename = basename(dirname(__FILE__)).'/'.basename(__FILE__);
		}
		return $basename;
	}
	
	protected function my_plugin_url() {
		$basename = plugin_basename(__FILE__);
		if ('/'.$basename == __FILE__) { // Maybe due to symlink
			return plugins_url().'/'.basename(dirname(__FILE__)).'/';
		}
		// Normal case (non symlink)
		return plugin_dir_url( __FILE__ );
	}
	
}

// Global accessor function to singleton
function pdfthPDFThumbnails() {
	return pdfth_pdf_thumbnails::get_instance();
}

// Initialise at least once
pdfthPDFThumbnails();

