<?php
/**
 * Recent Posts Block Template.
 *
 * @package aten-fse
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during backend preview render.
 * @param   int $post_id The post ID the block is rendering content against.
 *          This is either the post ID currently being displayed inside a query loop,
 *          or the post ID of the post hosting this block.
 * @param   array $context The context provided to the block by the post or it's parent block.
 */

if ( isset( $block['data']['preview_image'] ) ) :    /* rendering in inserter preview  */
	echo '<img src="' . esc_url( get_site_url() . $block['data']['preview_image'] ) . '" style="width:100%; height:auto;">';
else :

	// Support custom "anchor" values.
	$anchor = '';
	if ( ! empty( $block['anchor'] ) ) {
		$anchor = 'id="' . esc_attr( $block['anchor'] ) . '" ';
	}

	// Create class attribute allowing for custom "className" and "align" values.
	$class_name = 'recent-posts-block';
	if ( ! empty( $block['className'] ) ) {
		$class_name .= ' ' . $block['className'];
	}
	if ( ! empty( $block['align'] ) ) {
		$class_name .= ' align' . $block['align'];
	}

	// Load values and assign defaults.
	$block_title       = ( get_field( 'block_heading' ) ) ? get_field( 'block_heading' ) : 'Recent Posts';
	$queried_post_type = ( get_field( 'post_type_to_display' ) ) ? get_field( 'post_type_to_display' ) : 'post';
	$displayed_posts   = get_field( 'displayed_posts' );
	$post_count_limit  = ( get_field( 'number_of_displayed_posts' ) ) ? get_field( 'number_of_displayed_posts' ) : 3;
	$num_releases      = ( $displayed_posts ) ? count( $displayed_posts ) : 0;
	$remaining_posts   = $post_count_limit - $num_releases;
	$included_posts    = array(); // Empty array to track the IDs of already included posts.
	$display_view_all_button = ( get_field( 'display_view_all_button' ) ) ? get_field( 'display_view_all_button' ) : 'false';
	?>


	<?php
	if ( $displayed_posts ) :
		?>
	<div <?php echo esc_attr( $anchor ); ?> class="<?php echo esc_attr( $class_name ); ?>">
		<div class="recent-posts-block-component">
			<hr />
			<div class="recent-posts-block-title">
				<h2><?php echo esc_html( $block_title ); ?></h2>
			</div>
			<ul class="recent-posts-list">
				<?php
				// Output the recent posts from the repeater field.
				while ( have_rows( 'displayed_posts' ) ) :
					the_row();
					$post_object = get_sub_field( 'displayed_post' ); // Assuming the subfield name is 'displayed_post'.
					if ( $post_object ) {
						$included_posts[]    = $post_object->ID; // Add post ID to the included posts array.
						$included_post_title = get_the_title( $post_object->ID );

						echo '<li class="displayed-post-item">';
						echo '<a href="' . esc_url( get_permalink( $post_object->ID ) ) . '"><h3>' . esc_html( $included_post_title ) . '</h3></a>';
						echo '</li>';
					}
				endwhile;

				// Query the remaining recent posts.
				$recent_posts = new WP_Query(
					array(
						'post_type'      => $queried_post_type,
						'posts_per_page' => $remaining_posts,
						'post__not_in'   => $included_posts, // Exclude the already included post IDs.
					)
				);

				// Output the remaining posts from the recent posts query.
				while ( $recent_posts->have_posts() ) {
					$recent_posts->the_post();
					$included_post_title = get_the_title();

					echo '<li class="displayed-post-item">';
					echo '<a href="' . esc_url( get_permalink() ) . '"><h3>' . esc_html( $included_post_title ) . '</h3></a>';
					echo '</li>';
				}

				// Restore original post data.
				wp_reset_postdata();
				?>

				<?php
				if ( $display_view_all_button ) :
					$button_text   = ( get_field( 'view_all_button_button_text' ) ) ? get_field( 'view_all_button_button_text' ) : 'View More';
					$button_link   = get_field( 'view_all_button_button_link' );
					$button_target = $button_link['target'] ?? '_self';
					?>
					<li class="view-all-posts">
						<a href="<?php echo esc_url( $button_link['url'] ); ?>" target="<?php echo esc_attr( $button_target ); ?>">
							<?php echo esc_html( $button_text ); ?>
						</a>
					</li>
				<?php endif; ?>
			</ul>
		</div>
	</div>
<?php endif;
endif; ?>
