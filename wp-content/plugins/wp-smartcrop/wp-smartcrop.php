<?php if(!defined('ABSPATH')) { die(); } // Include in all php files, to prevent direct execution
/**
 * Plugin Name: WP SmartCrop
 * Plugin URI: https://www.wpsmartcrop.com/
 * Description: Style your images exactly how you want them to appear, for any screen size, and never get a cut-off face.
 * Version: 2.0.6
 * Author: Bytes.co
 * Author URI: https://bytes.co
 * License: GPLv2 or later
 * Text Domain: wpsmartcrop
 **/

if( !class_exists('WP_Smart_Crop') ) {
	class WP_Smart_Crop {
		public  $version = '2.0.6';
		private $plugin_dir_path;
		private $plugin_dir_url;
		private $current_image = null;
		private $focus_cache;
		private $upload_focus = null;
		private $upload_processors = false;
		private $options;
		private $debug_mode = false;

		public static function Instance() {
			static $instance = null;
			if ($instance === null) {
				$instance = new self();
			}
			return $instance;
		}

		private function __construct() {
			// Initialize Variables
			$this->plugin_dir_path = plugin_dir_path( __FILE__ );
			$this->plugin_dir_url  = plugin_dir_url( __FILE__ );
			$this->focus_cache     = array();
			$this->options         = get_option( 'wp-smartcrop-settings' );

			// Plugin list links
			add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'plugin_action_links' ) );

			// Register Settings
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			add_action( 'admin_init', array( $this, 'admin_init' ) );

			// Upload Processing
			add_filter( 'wp_handle_upload', array( $this, 'wp_handle_upload' ) );
			add_action( 'add_attachment'  , array( $this, 'add_attachment'   ) );
			add_action( 'edit_attachment' , array( $this, 'edit_attachment'  ) );

			// Editor Functions
			add_action( 'wp_enqueue_media'         , array( $this, 'wp_enqueue_media' ) );
			add_filter( 'attachment_fields_to_edit', array( $this, 'attachment_fields_to_edit' ), 10, 2 );

			// Thumbnail Crop Functions (for legacy theme support and hard-crop applications)
			add_filter( 'get_attached_file', array( $this, 'capture_attachment_id' ), 10, 2 );
			add_filter( 'update_attached_file', array( $this, 'capture_attachment_id' ), 10, 2 );
			add_filter( 'image_resize_dimensions', array( $this, 'image_resize_dimensions' ), 10, 6 );
			add_filter( 'wp_generate_attachment_metadata', array( $this, 'wp_generate_attachment_metadata' ), 10, 2 );

			// Display Functions
			add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
			add_filter( 'wp_get_attachment_image_attributes', array( $this, 'wp_get_attachment_image_attributes' ), PHP_INT_MAX, 3 );
			add_filter( 'the_content', array( $this, 'the_content' ), PHP_INT_MAX );
		}

		function plugin_action_links( $links ) {
			$links[] = '<a href="'. esc_url( get_admin_url(null, 'options-general.php?page=wp-smartcrop') ) .'">Settings</a>';
			//$links[] = '<a href="https://www.wpsmartcrop.com/addons" target="_blank">Get Addons</a>';
			return $links;
		}

		function admin_init() {
			register_setting( 'wp-smartcrop', 'wp-smartcrop-settings', array( $this, 'settings_sanitization_callback' ) );
			add_settings_section(
				'wp-smartcrop-settings',
				__( 'General Settings', 'wpsmartcrop' ),
				array( $this, 'add_settings_section' ),
				'wp-smartcrop'
			);

			add_settings_field(
				'wp_smartcrop_select_focus_mode',
				__( 'Focus Mode', 'wpsmartcrop' ),
				array( $this, 'wp_smartcrop_select_focus_mode' ),
				'wp-smartcrop',
				'wp-smartcrop-settings'
			);
			add_settings_field(
				'wp_smartcrop_disable_thumbnail_generation',
				__( 'Thumbnail Generation', 'wpsmartcrop' ),
				array( $this, 'wp_smartcrop_disable_thumbnail_generation' ),
				'wp-smartcrop',
				'wp-smartcrop-settings'
			);

			$upload_processors = apply_filters( 'wp_smartcrop_announce_upload_processors', array() );
			// THIS IS WHERE WE EVENTUALLY SORT THE STACK ON THE BACK END
			$this->upload_processors = $upload_processors;

			do_action( 'wp_smartcrop_admin_init' );
		}

		function settings_sanitization_callback( $settings ) {
			$sanitized = array();
			if( isset( $settings['focus-mode'] ) && $settings['focus-mode'] ) {
				$sanitized['focus-mode'] = $settings['focus-mode'];
			} else {
				$sanitized['focus-mode'] = 'power-lines';
			}
			if( isset( $settings['disable-thumbnails'] ) && $settings['disable-thumbnails'] ) {
				$sanitized['disable-thumbnails'] = 1;
			} else {
				$sanitized['disable-thumbnails'] = 0;
			}
			return apply_filters( 'wp_smartcrop_sanitize_settings', $sanitized, $settings );
		}

		function add_settings_section() { }

		function wp_smartcrop_select_focus_mode() {
			$focus_mode = 'power-lines';
			if( isset( $this->options['focus-mode'] ) ) {
				$focus_mode = $this->options['focus-mode'];
			}
			?>
			<label>
				<span><?php _e( 'Select Focus Mode' ); ?></span>
				<select name='wp-smartcrop-settings[focus-mode]'>
					<option value="power-lines" <?php selected($focus_mode, 'power-lines'); ?>>Default (Power Lines)</option>
					<option value="relative-position" <?php selected($focus_mode, 'relative-position'); ?>>Relative Position</option>
				</select>
			</label>
			<p><em>
				<?php
				_e('Power Lines cropping will attempt to place the focal point at a 33.33%, 50%, or 66.67% position vertically and horizontally, to produce powerful compositions.');
				echo "<br>";
				_e('Relative Position cropping will attempt to maintain the position of the focal point, relative to the cropped dimensions.');
				?></em></p>
			<?php
		}

		function wp_smartcrop_disable_thumbnail_generation() {
			$disable_thumbs = 0;
			if( isset( $this->options['disable-thumbnails'] ) ) {
				$disable_thumbs = $this->options['disable-thumbnails'];
			}
			?>
			<input type='checkbox' name='wp-smartcrop-settings[disable-thumbnails]' <?php checked( $disable_thumbs, 1 ); ?> value='1'>
			<label for='wp-smartcrop-settings[disable-thumbnails]'><?php _e( 'Disable thumbnail generation (not recommended)' ); ?></label>
			<p><em>
				<?php
				_e('Disabling thumbnail generation allows you to manage legacy thumbnail cropping with other plugins, such as Manual Image Crop.');
				echo " ";
				_e('It also will prevent conflicts with plugins like Jetpack\'s Photon CDN, which sadly break thumbnail regeneration.');
				?></em></p>
			<?php
		}

		function admin_menu() {
			add_options_page(
				'WP SmartCrop',
				'WP SmartCrop',
				'manage_options',
				'wp-smartcrop',
				array( $this, 'submenu_page' )
			);

			do_action( 'wp_smartcrop_admin_menu' );
		}

		function submenu_page() {
			?>
			<form action='options.php' method='post'>
				<div class='wrap'>
					<h1>WP SmartCrop</h1>
					<?php
					settings_fields( 'wp-smartcrop' );
					do_settings_sections( 'wp-smartcrop' );
					submit_button();
					?>
				</div>
			</form>
			<?php
		}

		function wp_handle_upload( $file ) {
			if( $this->upload_processors ) {
				// make sure this is an image
				if( substr( $file['type'], 0, 6 ) == 'image/' ) {
					$editor = wp_get_image_editor( $file['file'] );
					$resized = $editor->resize( 400, 400, true );
					if( !is_wp_error( $resized ) ) {
						$thumb_path = $editor->generate_filename( 'wpsmartcrop-processor' );
						$saved = $editor->save( $thumb_path );
						if( !is_wp_error( $saved ) ) {
							$upload_dir = wp_upload_dir();
							$thumb_url = $this->replace_prefix( $upload_dir['basedir'] , $upload_dir['baseurl'] , $thumb_path );
							// hack in case of windows path silliness
							$thumb_url = str_replace( '\\', '/', $thumb_url );
							$thumb = array(
								'path' => $thumb_path,
								'url'  => $thumb_url
							);
							foreach( $this->upload_processors as $upload_processor ) {
								$focus = call_user_func( $upload_processor, $thumb );
								if( $focus ) {
									break;
								}
							}
							unlink( $thumb_path );

							if( $focus ) {
								$this->upload_focus = $focus;
							}

						}
					}
				}
			}
			return $file;
		}

		function add_attachment( $attachment_id ) {
			if( $this->upload_focus ) {
				$type = get_post_mime_type( $attachment_id );
				if( substr( $type, 0, 6 ) == 'image/' ) {
					update_post_meta( $attachment_id, '_wpsmartcrop_enabled', 1 );
					update_post_meta( $attachment_id, '_wpsmartcrop_image_focus', $this->upload_focus );
				}
				$this->upload_focus = null;
			}
		}

		function wp_enqueue_media() {
			wp_enqueue_script( 'wp-smartcrop-media-library', $this->plugin_dir_url . 'js/media-library.js', array( 'jquery' ), $this->version, true );
			wp_enqueue_style( 'wp-smartcrop-media-library', $this->plugin_dir_url . 'css/media-library.css', array(), $this->version );
		}

		function attachment_fields_to_edit( $form_fields, $post ) {
			if( substr($post->post_mime_type, 0, 5) == 'image' ) {
				// get image width
				$image_info = wp_get_attachment_metadata( $post->ID );
				$width = false;
				if( $image_info && !empty( $image_info['width'] ) ) {
					$width = $image_info['width'];
				} else {
					// no width means not an image
					return $form_fields;
				}
				// get current settings
				$enabled = intval( get_post_meta( $post->ID, '_wpsmartcrop_enabled', true ) );
				$focus  = get_post_meta( $post->ID, '_wpsmartcrop_image_focus', true );
				if( !$focus || !is_array( $focus ) || !isset( $focus['left'] ) || !isset( $focus['top'] ) ) {
					$focus = array(
						'left' => '50',
						'top'  => '50'
					);
					$default_focus = apply_filters( 'wpsmartcrop_default_focus', array(50, 50), $post->ID );
					if( count( $default_focus ) > 1 ) {
						$focus = array(
							'left' => $default_focus[0],
							'top'  => $default_focus[1]
						);
					}
				}
				$enabled_class = '';
				if( $enabled == 1 ) {
					$enabled_class = ' wpsmartcrop_interface_enabled';
				}
				// build image overlay
				ob_start();
				?>
				<div class="wpsmartcrop_preview_wrap" style="max-width: <?php echo 3 * $width; ?>px;">
					<?php echo wp_get_attachment_image( $post->ID, 'full' ); ?>
					<div class="wpsmartcrop_gnomon">
						<div class="wpsmartcrop_gnomon_h" style="top:  <?php echo $focus['top'];  ?>%;"></div>
						<div class="wpsmartcrop_gnomon_v" style="left: <?php echo $focus['left']; ?>%;"></div>
						<div class="wpsmartcrop_gnomon_c" style="top:  <?php echo $focus['top'];  ?>%; left: <?php echo $focus['left']; ?>%;"></div>
					</div>
				</div>
				<?php
				$image_overlay = ob_get_clean();

				// build html for form interface
				ob_start();
				?>
				<input type="checkbox" class="wpsmartcrop_enabled" id="wpsmartcrop_enabled" name="attachments[<?php echo $post->ID; ?>][_wpsmartcrop_enabled]" value="1"<?php checked( $enabled, 1 );?> />
				<label for="wpsmartcrop_enabled">Enable Smart Cropping</label><br/>
				<div class="wpsmartcrop_interface<?php echo $enabled_class; ?>">
					<?php echo $image_overlay; ?>
					<input type="hidden" class="wpsmartcrop_image_focus_left" name="attachments[<?php echo $post->ID; ?>][_wpsmartcrop_image_focus][left]" value="<?php echo $focus['left']; ?>" />
					<input type="hidden" class="wpsmartcrop_image_focus_top"  name="attachments[<?php echo $post->ID; ?>][_wpsmartcrop_image_focus][top]"  value="<?php echo $focus['top' ]; ?>" />
					<button type="button" class="button wpsmartcrop_edit">Edit Focal Point</button>
					<script type="template/html" class="wpsmartcrop_editor_template">
						<div class="wpsmartcrop_editor">
							<div class="wpsmartcrop_editor_backdrop wpsmartcrop_cancel"></div>
							<div class="wpsmartcrop_editor_inner">
								<?php echo $image_overlay; ?>
								<div class="wpsmartcrop_editor_fields">
									<h3>Focal Point</h3>
									<div class="wpsmartcrop_editor_inputs">
										<label>
											Left:
											<span class="wpsmartcrop_percent_wrap">
												<input type="number" min="0" max="100" step="0.01" class="wpsmartcrop_temp_focus_left" value="" />
												<span>%</span>
											</span>
										</label>
										<label>
											Top:
											<span class="wpsmartcrop_percent_wrap">
												<input type="number" min="0" max="100" step="0.01" class="wpsmartcrop_temp_focus_top" value="" />
												<span>%</span>
											</span>
										</label>
										<div class="wpsmartcrop_buttons">
											<button type="button" class="button wpsmartcrop_cancel">Cancel</button>
											<button type="button" class="button wpsmartcrop_apply">Apply</button>
										</div>
									</div>
								</div>
							</div>
						</div>
					</script>
				</div>
				<?php
				$focal_point_html = ob_get_clean();
				$form_fields = array(
					'wpsmartcrop_image_focal_point' => array(
						'input' => 'html',
						'label' => __( 'Smart Crop' ),
						'html'  => $focal_point_html
					)
				) + $form_fields;
			}
			return $form_fields;
		}

		function edit_attachment( $attachment_id ) {
			if( ! isset( $_REQUEST['attachments'] ) || ! isset( $_REQUEST['attachments'][$attachment_id] ) ) {
				return;
			}
			$attachment = $_REQUEST['attachments'][$attachment_id];

			$smartcrop_enabled = boolval( isset( $attachment['_wpsmartcrop_enabled'] ) && $attachment['_wpsmartcrop_enabled'] == 1 );
			$smartcrop_image_focus = array(
				'top'  => null,
				'left' => null
			);

			if ( isset( $attachment['_wpsmartcrop_image_focus'] ) ) {
				if ( isset( $attachment['_wpsmartcrop_image_focus']['top'] ) ) {
					$smartcrop_image_focus['top']	= number_format( $attachment['_wpsmartcrop_image_focus']['top'], 2 );
				}
				if ( isset( $attachment['_wpsmartcrop_image_focus']['left' ] )) {
					$smartcrop_image_focus['left']	= number_format ($attachment['_wpsmartcrop_image_focus']['left'], 2 );
				}
			}
			if ( $smartcrop_image_focus['top'] === null && $smartcrop_image_focus['left'] === null ) {
				$smartcrop_image_focus = false;
			}

			unset($attachment);

			$old_enabled = get_post_meta( $attachment_id, '_wpsmartcrop_enabled', true );
			$old_focus   = get_post_meta( $attachment_id, '_wpsmartcrop_image_focus', true );

			$new_enabled = $smartcrop_enabled;
			$new_focus   = $smartcrop_image_focus;

			if( ( $new_enabled != $old_enabled ) || ( serialize( $new_focus ) != serialize( $old_focus ) ) ) {
				update_post_meta( $attachment_id, '_wpsmartcrop_enabled', $new_enabled );
				update_post_meta( $attachment_id, '_wpsmartcrop_image_focus', $new_focus );
				if( !( isset( $this->options['disable-thumbnails'] ) && $this->options['disable-thumbnails'] ) ) {
					$this->regenerate_thumbnails( $attachment_id );
				}
			}
		}

		function capture_attachment_id( $file, $attachment_id ) {
			$this->current_image = $attachment_id;
			return $file;
		}

		function image_resize_dimensions( $old_val, $orig_w, $orig_h, $dest_w, $dest_h, $crop ) {
			// if we aren't cropping or we have no id to work with, just return
			if( $crop && $this->current_image && !( isset( $this->options['disable-thumbnails'] ) && $this->options['disable-thumbnails'] ) ) {
				// if we have no height or cropping is unnecessary, just return
				if( ( $orig_h * $dest_h ) && ( $orig_w / $orig_h ) != ( $dest_w / $dest_h ) ) {
					$id = $this->current_image;
					$focus = get_post_meta( $id, '_wpsmartcrop_image_focus', true );
					// if we aren't a smartcrop image, just return
					if( $focus ) {
						// now we can make some calculations
						return $this->get_smartcrop_dimensions( $orig_w, $orig_h, $dest_w, $dest_h, $focus );
					}
				}
			}
			return $old_val;
		}

		function wp_generate_attachment_metadata( $metadata, $attachment_id ) {
			$this->current_image = null;
			return $metadata;
		}

		function wp_enqueue_scripts() {
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'jquery.wp-smartcrop', $this->plugin_dir_url . 'js/jquery.wp-smartcrop.min.js', array( 'jquery' ), $this->version, true );
			wp_localize_script( 'jquery.wp-smartcrop', 'wpsmartcrop_options', array(
				'focus_mode' => ( is_array($this->options) && !empty($this->options['focus-mode']) ) ? $this->options['focus-mode'] : 'power-lines'
			) );
			wp_enqueue_style( 'wp-smart-crop-renderer', $this->plugin_dir_url . 'css/image-renderer.css', array(), $this->version );
		}

		function wp_get_attachment_image_attributes( $atts, $attachment, $size ) {
			$focus_attr = $this->get_smartcrop_focus_attr( $attachment->ID, $size );
			if( $focus_attr ) {
				if( !isset( $atts['class'] ) || !$atts['class'] ) {
					$atts['class'] = "";
				} else {
					$atts['class'] .= " ";
				}
				$atts['class'] .= "wpsmartcrop-image";
				$atts['data-smartcrop-focus'] = $focus_attr;
			}
			return $atts;
		}

		function the_content( $content ) {
			$tags = $this->extract_tags( $content, 'img', true, true );
			$unique_tags = array();
			$ids = array();
			foreach( $tags as $tag ) {
				list( $id, $size ) = $this->get_id_and_size_from_tag( $tag );
				if( $id && $size ) {
					$ids[] = $id;
					$tag['id'] = $id;
					$tag['size'] = $size;
					$unique_tags[$tag['full_tag']] = $tag;
				}
			}
			array_unique( $ids );
			if( count( $ids ) > 1 ) {
				update_meta_cache( 'post', $ids );
			}
			foreach( $unique_tags as $old_tag => $parsed_tag ) {
				$new_tag = $this->make_new_content_img_tag( $parsed_tag );
				if( $new_tag ) {
					$content = str_replace( $old_tag, $new_tag, $content );
				}
			}
			return $content;
		}

		private function replace_prefix( $find, $replace, $string ) {
			if (substr($string, 0, strlen($find)) == $find) {
				$string = $replace . substr($string, strlen($find));
			}
			return $string;
		}

		private function regenerate_thumbnails( $attachment_id ) {
			$this->current_image = $attachment_id;
			$path = get_attached_file( $attachment_id );
			$this->delete_existing_cropped_thumbs( $attachment_id );
			// hack for file_exists on windows servers
			$path = str_replace( array( '/', '\\' ), DIRECTORY_SEPARATOR, $path );
			if ( !$path || !file_exists( $path ) ) {
				return;
			}
			$metadata = wp_generate_attachment_metadata( $attachment_id, $path );
			if( !$metadata || empty( $metadata ) || is_wp_error( $metadata ) ) {
				return;
			}
			wp_update_attachment_metadata( $attachment_id, $metadata );
		}

		private function delete_existing_cropped_thumbs( $attachment_id ) {
			$metadata = wp_get_attachment_metadata( $attachment_id );
			if( !is_array( $metadata ) ) {
				return false;
			}
			$file = get_attached_file( $attachment_id );
			// hack for file_exists on windows servers
			$file = str_replace( array( '/', '\\' ), DIRECTORY_SEPARATOR, $file );
			$basename = basename( $file );
			$folder = $this->remove_postfix( $file, $basename );
			foreach( $metadata['sizes'] as $size_name => $size_details ) {
				if( $this->is_image_size_cropped( $size_name ) ) {
					$thumb_path = $folder . $size_details['file'];
					if( file_exists( $thumb_path ) ) {
						unlink( $thumb_path );
					}
				}
			}
		}

		private function remove_postfix($string, $postfix) {
			if( substr( $string,-1 * strlen( $postfix ) ) === $postfix ) {
				$string = substr( $string, 0, strlen( $string ) - strlen( $postfix ) );
			}
			return $string;
		}

		private function get_smartcrop_dimensions( $orig_w, $orig_h, $dest_w, $dest_h, $focus ) {
			if( ( $orig_w / $orig_h ) > ( $dest_w / $dest_h ) ) {
				$src_h = $orig_h;
				$src_w = round( $dest_w * $orig_h / $dest_h );
				$src_x = $this->get_smartcrop_offset( $src_w, $orig_w, $focus['left'] );
				$src_y = 0;
			} else {
				$src_h = round( $dest_h * $orig_w / $dest_w );
				$src_w = $orig_w;
				$src_x = 0;
				$src_y = $this->get_smartcrop_offset( $src_h, $orig_h, $focus['top'] );
			}
			$ret_val = array(
				'dest_x' => 0,
				'dest_y' => 0,
				'src_x'  => $src_x,
				'src_y'  => $src_y,
				'dest_w' => $dest_w,
				'dest_h' => $dest_h,
				'src_w'  => $src_w,
				'src_h'  => $src_h
			);
			return array_values( $ret_val );
		}

		private function get_smartcrop_offset( $dim, $orig_dim, $focus_pos ) {
			$power_lines = array( .3333, .5, .6667 );
			$focus_pos = $focus_pos / 100;
			$focus_target = $this->get_closest( $focus_pos, $power_lines );
			$offset = round( $focus_pos * $orig_dim - $focus_target * $dim );
			$max = $orig_dim - $dim;
			if( $offset > $max ) {
				$offset = $max;
			}
			if( $offset < 0 ) {
				$offset = 0;
			}
			return $offset;
		}

		private function get_closest( $search, $arr ) {
			$closest = null;
			foreach( $arr as $item ) {
				if( $closest === null || abs( $search - $closest ) > abs( $search - $item ) ) {
					$closest = $item;
					if( $closest == $search ) {
						break;
					}
				}
			}
			return $closest;
		}

		private function get_smartcrop_focus_attr( $id, $size ) {
			// check cache
			if( isset( $this->focus_cache[ $id ] ) && isset( $this->focus_cache[ $id ][ $size ] ) ) {
				return $this->focus_cache[ $id ][ $size ];
			}
			if( !$this->is_image_size_cropped( $size ) ) {
				if( get_post_meta( $id, '_wpsmartcrop_enabled', true ) == 1 ) {
					$focus = get_post_meta( $id, '_wpsmartcrop_image_focus', true );
					if( $focus && is_array( $focus ) && isset( $focus['left'] ) && isset( $focus['top'] ) ) {
						$ret_val = json_encode( array(
							round( intval( $focus['left'] ), 2 ),
							round( intval( $focus['top' ] ), 2 ),
						) );
						// load into cache
						if( !isset( $this->focus_cache[ $id ] ) ) {
							$this->focus_cache[ $id ] = array();
						}
						$this->focus_cache[ $id ][ $size ] = $ret_val;
						return $ret_val;
					} else {
						$default_focus = apply_filters( 'wpsmartcrop_default_focus', array(50, 50), $id );
						$ret_val = json_encode( $default_focus );
					}
				}
			}
			return false;
		}
		private function is_image_size_cropped( $size ) {
			$image_sizes = $this->get_image_sizes();
			// array sizes are assumed to be cropped... use names, as suggested by WordPress
			if( is_array( $size ) ) {
				return true;
			}
			if(!$size || $size == 'full' ) {
				return false;
			}
			if( isset( $image_sizes[ $size ] ) && isset( $image_sizes[ $size ]['crop'] ) ) {
				return (bool) intval( $image_sizes[ $size ]['crop'] );
			}
			// if we can't find the size, lets assume it is cropped... it's a guess
			return true;
		}
		private function get_image_sizes() {
			global $_wp_additional_image_sizes;
			$custom_sizes = $_wp_additional_image_sizes;
			if( !is_array( $custom_sizes ) ) {
				$custom_sizes = array();
			}
			$sizes = array();
			foreach( get_intermediate_image_sizes() as $_size ) {
				if( !in_array( $_size, $custom_sizes ) ) {
					$temp = array(
						'width'  => get_option( $_size . "_size_w" ),
						'height' => get_option( $_size . "_size_h" ),
						'crop'   => get_option( $_size . "_crop"   ),
					);
					if( $temp['width'] || $temp['height'] ) {
						$sizes[ $_size ] = $temp;
					}
				} elseif( isset( $custom_sizes[ $_size ] ) ) {
					$sizes[ $_size ] = shortcode_atts( array(
						'width' => 0,
						'height'=> 0,
						'crop'  => false
					), $custom_sizes[ $_size ] );
				}
			}
			return $sizes;
		}

		private function extract_tags( $html, $tag, $selfclosing = null, $return_the_entire_tag = false, $charset = 'ISO-8859-1' ){
			if ( is_array($tag) ){
				$tag = implode('|', $tag);
			}
			//known self-closing tabs
			$selfclosing_tags = array( 'area', 'base', 'basefont', 'br', 'hr', 'input', 'img', 'link', 'meta', 'col', 'param' );
			if ( is_null($selfclosing) ){
				$selfclosing = in_array( $tag, $selfclosing_tags );
			}
			//The regexp is different for normal and self-closing tags because I can't figure out
			//how to make a sufficiently robust unified one.
			if ( $selfclosing ){
				$tag_pattern =
					'@<(?P<tag>'.$tag.')           # <tag
					(?P<attributes>\s[^>]+)?       # attributes, if any
					\s*/?>                   # /> or just >, being lenient here
					@xsi';
			} else {
				$tag_pattern =
					'@<(?P<tag>'.$tag.')           # <tag
					(?P<attributes>\s[^>]+)?       # attributes, if any
					\s*>                 # >
					(?P<contents>.*?)         # tag contents
					</(?P=tag)>               # the closing </tag>
					@xsi';
			}
			$attribute_pattern =
				'@
				(?P<name>\w+)                         # attribute name
				\s*=\s*
				(
					(?P<quote>[\"\'])(?P<value_quoted>.*?)(?P=quote)    # a quoted value
					|                           # or
					(?P<value_unquoted>[^\s"\']+?)(?:\s+|$)           # an unquoted value (terminated by whitespace or EOF)
				)
				@xsi';
			//Find all tags
			if ( !preg_match_all($tag_pattern, $html, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE ) ){
				//Return an empty array if we didn't find anything
				return array();
			}
			$tags = array();
			foreach ($matches as $match){
				//Parse tag attributes, if any
				$attributes = array();
				if ( !empty($match['attributes'][0]) ){
					if ( preg_match_all( $attribute_pattern, $match['attributes'][0], $attribute_data, PREG_SET_ORDER ) ){
						//Turn the attribute data into a name->value array
						foreach($attribute_data as $attr){
							if( !empty($attr['value_quoted']) ){
								$value = $attr['value_quoted'];
							} else if( !empty($attr['value_unquoted']) ){
								$value = $attr['value_unquoted'];
							} else {
								$value = '';
							}
							$attributes[$attr['name']] = $value;
						}
					}
				}
				$tag = array(
					'tag_name'   => $match['tag'][0],
					'offset'     => $match[0][1],
					'contents'   => !empty($match['contents'])?$match['contents'][0]:'', //empty for self-closing tags
					'attributes' => $attributes,
				);
				if ( $return_the_entire_tag ){
					$tag['full_tag'] = $match[0][0];
				}
				$tags[] = $tag;
			}

			return $tags;
		}
		private function get_id_and_size_from_tag( $tag ) {
			if( isset( $tag['attributes'] ) && isset( $tag['attributes']['class'] ) && $tag['attributes']['class'] ) {
				$classes     = explode( ' ', $tag['attributes']['class'] );
				$id_prefix   = 'wp-image-';
				$size_prefix = 'size-';
				$ret_val = array( false, false );
				foreach( $classes as $class ) {
					if( !$ret_val[0] && strpos( $class, $id_prefix ) === 0 ) {
						$ret_val[0] = intval( substr( $class, strlen( $id_prefix ) ) );
					} elseif( !$ret_val[1] && strpos( $class, $size_prefix ) === 0 ) {
						$ret_val[1] = substr( $class, strlen( $size_prefix ) );
					} elseif( $ret_val[0] && $ret_val[1] ) {
						break;
					}
				}
				return $ret_val;
			}
			return false;
		}
		private function make_new_content_img_tag( $tag ) {
			$id   = $tag['id'];
			$size = $tag['size'];
			$atts = $tag['attributes'];
			$focus_attr = $this->get_smartcrop_focus_attr( $id, $size );
			if( $focus_attr ) {
				if( !isset( $atts['class'] ) || !$atts['class'] ) {
					$atts['class'] = "";
				} else {
					$atts['class'] .= " ";
				}
				$atts['class'] .= "wpsmartcrop-image";
				$atts['data-smartcrop-focus'] = $focus_attr;
			}
			$new_tag = '<img';
			foreach( $atts as $name => $val ) {
				$new_tag .= ' ' . $name . '="' . $val . '"';
			}
			$new_tag .= ' />';
			return $new_tag;
		}

		private function debug($message) {
			if( $this->debug_mode ) {
				$debug = date('Y-m-d H:i:s') . " | ";

				$backtrace = debug_backtrace();
				$call = $backtrace[0];

				// trim to plugin directory
				$root = trim( $this->plugin_dir_path, '/' ) . DIRECTORY_SEPARATOR;
				if( substr( $call['file'], 0, strlen( $root ) ) == $root ) {
					$debug .= substr( $call['file'], strlen( $root ) );
				} else {
					$debug .= $call['file'];
				}
				$debug .= ' | ';
				$debug .= 'Ln: ' . $call['line'] . ' | ';
				$debug .= print_r($message, true);
				$debug .= "\n";
				$handle = fopen( $this->plugin_dir_path . "debug_log.txt", 'a' );
				fwrite( $handle, $debug );
				fclose( $handle );
			}
		}
	}
	WP_Smart_Crop::Instance();
}
