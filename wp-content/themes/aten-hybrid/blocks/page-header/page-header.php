<?php
/**
 * Standard Page Header Block Template.
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during backend preview render.
 * @param   int $post_id The post ID the block is rendering content against.
 *          This is either the post ID currently being displayed inside a query loop,
 *          or the post ID of the post hosting this block.
 * @param   array $context The context provided to the block by the post or it's parent block.
 */

if( isset( $block['data']['preview_image'] )  ) :    /* rendering in inserter preview  */
    echo '<img src="'. $block['data']['preview_image'] .'" style="width:100%; height:auto;">';
else :
		
	// Support custom "anchor" values.
	$anchor = '';
	if ( ! empty( $block['anchor'] ) ) {
		$anchor = 'id="' . esc_attr( $block['anchor'] ) . '" ';
	}

	// Create class attribute allowing for custom "className" and "align" values.
	$class_name = 'standard-page-header-block';
	if ( ! empty( $block['className'] ) ) {
		$class_name .= ' ' . $block['className'];
	}
	if ( ! empty( $block['align'] ) ) {
		$class_name .= ' align' . $block['align'];
	}

	// Load values and assign defaults.
	$post_id = get_the_ID();
	$header_title = (get_field( 'page_title' )) ? get_field( 'page_title' ) : get_the_title( $post_id );
	$header_description = (get_field( 'description' )) ? get_field( 'description' ) : '';
	$post_type = get_post_type( $post_id );
	$is_resource = false;
	$resource_icon = '';

	if($post_type === 'research'){
		$is_resource = true;
		// Get resource category icon
		$research_category_icon = 'target';
		// Check for a research category
		$research_cat_id = get_field('research_category', $post_id);
		if ($research_cat_id) {
			// Get the research category ID
			$research_cat = get_term($research_cat_id); 
			if ($research_cat) {
				// Check for custom icon on the research category
				if(get_field('icon', 'research-category_' . $research_cat->term_id)) {
					$research_category_icon = get_field('icon', 'research-category_' . $research_cat->term_id);
				} 
			}
		}
		$resource_icon = (get_field( 'resource_icon' )) ? get_field( 'resource_icon' ) : $research_category_icon;

	} elseif($post_type === 'message') {
		$is_resource = true;
		 // Get message category icon
		$message_category_icon = 'message';
		// Check for a message category
		$message_cat_id = get_field('message_category', $post_id);
		if ($message_cat_id) {
			// Get the message category ID
			$message_cat = get_term($message_cat_id); 
			if ($message_cat) {
				// Check for custom icon on the message category
				if(get_field('icon', 'message-category_' . $message_cat->term_id)) {
					$message_category_icon = get_field('icon', 'message-category_' . $message_cat->term_id);
				} 
			}
		}
		$resource_icon = (get_field( 'resource_icon' )) ? get_field( 'resource_icon' ) : $message_category_icon;
	}

	?>

	<div class="page-header-component <?php echo ($is_resource) ? 'resource-page' : ''; ?> animate-fade-in-slide-up">
		<div class="page-header-triangle"><img src="<?php echo get_stylesheet_directory_uri();?>/assets/images/page-header-triangle.svg" alt="" /></div>
		<div class="page-header-wrapper">
			<div class="page-header-content">
				<h1><?php echo $header_title; ?></h1>
				<?php echo ($header_description) ? '<p>' . $header_description . '</p>' : ''; ?>
				<?php if($is_resource) : 
					$button = get_field( 'resource_button' );
					if($button && $button['button_text'] && $button['linked_file']) :
						$file = $button['linked_file']; 
						$has_file_type = $resource_url = $filetype = $file_size = $file_size_suffix = '';
						$resource_url = $file['url'];
						// Check if the resource has a file type
						$has_file_type = !empty(get_post_mime_type($file['ID']));
						if ($has_file_type) {
							// Get the file size in bytes
							$file_size = $file['filesize'];

							// Convert file size to kilobytes (KB)
							$file_size = round($file_size / 1024, 2);
							$file_size_suffix = 'KB';
							
							// Convert to megabytes (MB) as necessary
							if($file_size > 1000) {
								$file_size = round($file_size / 1000, 2);
								$file_size_suffix = 'MB';
							}

							// Get the file type extension
							$filetype = (wp_check_filetype($file['filename']));
						}
						?>
						<a class="custom-button is-style-download-purple" href="<?php esc_url($resource_url); ?>">
							<?php 
								echo $button['button_text']; 
								echo ($filetype) ? ' (' . strtoupper($filetype['ext']) . ') ' : '';
								echo ($file_size) ? ' <span class="file-size">(' . $file_size . ' ' . $file_size_suffix . ')</span>' : '';
							?>
						</a>
					<?php endif; // button exists
				endif; // is resource ?>
			</div>
			<?php if($is_resource) : ?>
				<div class="resource-icon-wrapper">
					<img src="<?php echo (get_stylesheet_directory_uri() . '/assets/icons/white/' . $resource_icon . '.svg'); ?>" alt="" />
				</div>
			<?php endif; ?>
		</div>
	</div>
<?php endif; ?>