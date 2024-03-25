<?php
/**
 * News Release Block Template.
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
	echo '<img src="' . esc_attr( $block['data']['preview_image'] ) . '" style="width:100%; height:auto;">';
else :

	// Support custom "anchor" values.
	$anchor = '';
	if ( ! empty( $block['anchor'] ) ) {
		$anchor = 'id="' . esc_attr( $block['anchor'] ) . '" ';
	}

	// Create class attribute allowing for custom "className" and "align" values.
	$class_name = 'news-release-block';
	if ( ! empty( $block['className'] ) ) {
		$class_name .= ' ' . $block['className'];
	}
	if ( ! empty( $block['align'] ) ) {
		$class_name .= ' align' . $block['align'];
	}

	// Load values and assign defaults.
	$news_releases   = get_field( 'news_releases' );
	$num_releases    = ( $news_releases ) ? count( $news_releases ) : 0;
	$remaining_posts = 3 - $num_releases;
	$included_posts  = array(); // Empty array to track the IDs of already included posts
	?>

	<div <?php echo $anchor; ?> class="<?php echo esc_attr( $class_name ); ?>">
		<div class="news-release-block-component">
			<hr class="gold" />
			<div class="news-release-block-title">
				<h2>News Releases</h2>
			</div>
			<ul class="news-release-list">
			<?php
			if ( $news_releases ) :
				// Output the news releases from the repeater field
				while ( have_rows( 'news_releases' ) ) :
					the_row();
					$post_object = get_sub_field( 'news_release' ); // Assuming the subfield name is 'news_release'
					if ( $post_object ) {
						$included_posts[] = $post_object->ID; // Add post ID to the included posts array
						$title            = get_the_title( $post_object->ID );
						$publication_date = get_field( 'publication_date', $post_object->ID ); // Assuming the custom publication date field name is 'publication_date'

						echo '<li class="news-item">';
						if ( $publication_date ) {
							echo '<p>' . esc_html( $publication_date ) . '</p>';
						}
						echo '<a href="' . esc_url( get_permalink( $post_object->ID ) ) . '"><h3>' . esc_html( $title ) . '</h3></a>';
						echo '</li>';
					}
				endwhile;
			endif;

				// Query the remaining news releases
				$recent_releases = new WP_Query(
					array(
						'post_type'      => 'news',
						'posts_per_page' => $remaining_posts,
						'post__not_in'   => $included_posts, // Exclude the already included post IDs
						'meta_key'       => 'publication_date',
						'orderby'        => 'meta_value_num',
						'order'          => 'DESC',
					)
				);

				// Output the remaining news releases from the recent releases query
			while ( $recent_releases->have_posts() ) {
				$recent_releases->the_post();
				$title            = get_the_title();
				$publication_date = get_field( 'publication_date', get_the_ID() ); // the custom publication date field name is 'publication_date'

				echo '<li class="news-item">';
				if ( $publication_date ) {
					echo '<p>' . esc_html( $publication_date ) . '</p>';
				}
				echo '<a href="' . esc_url( get_permalink() ) . '"><h3>' . esc_html( $title ) . '</h3></a>';
				echo '</li>';
			}

				// Restore original post data
				wp_reset_postdata();
			?>
				<li class="view-all-news">
					<a href="/news" class="btn-large--navy">View All News Releases</a>
				</li>
			</ul>
		</div>
	</div>
<?php endif; ?>
