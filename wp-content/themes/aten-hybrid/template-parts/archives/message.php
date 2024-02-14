<?php
/**
 * Template partial for message archive blocks
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package WordPress
 * @subpackage ccc
 */

$message_custom_fields = get_field('message_lab_archive', 'option');
$title = ($message_custom_fields && $message_custom_fields['message_lab_title']) ? $message_custom_fields['message_lab_title'] : 'Message Lab';
$description = ($message_custom_fields && $message_custom_fields['message_lab_archive_subtitle']) ? $message_custom_fields['message_lab_archive_subtitle'] : '';
$has_featured_message = is_array($message_custom_fields['featured_message']);

?>

    <header class="page-header alignwide">
		<h1 class="page-title"><?php echo $title; ?></h1>
		<?php if ( $description ) : ?>
            <div class="archive-description"><p><?php echo $description; ?></p></div>
		<?php endif; ?>
	</header><!-- .page-header -->

	<?php if($has_featured_message) : ?>
		<div class="archive-featured-resource-wrapper">
			<ul class="featured-resource-list">
				<?php foreach($message_custom_fields['featured_message'] as $featured_post_array) : 
					$args = array(
						'p'         => $featured_post_array['message_post'],
						'post_type' => 'any'
					);
					$featured_post = new WP_Query($args);
					if($featured_post->have_posts()) : while ( $featured_post->have_posts() ) : $featured_post->the_post();
						get_template_part( 'template-parts/content/content-single-message-card' );
					endwhile; endif;
				endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>

	<div class="filter-container alignfull">
		<div id="filter-wrapper" role="region" class="filter-wrapper animate-fade-in-slide-up">
			<div class="filter-form-wrapper">
                <?php echo do_shortcode( '[searchandfilter slug="message-archive-filters"]' ); ?>
			</div>

			<div class="result-count-wrapper">
				<p>
					<?php
						printf(
							esc_html(
								/* translators: %d: The number of search results. */
								_n(
									'Showing %d result',
									'Showing %d results',
									(int) $wp_query->found_posts,
									'twentytwentyone'
								)
							),
							(int) $wp_query->found_posts
						);
					?>
				</p>
			</div>
		</div>
	</div>

	<div class="archive-container">
		<div class="archive-wrap">
			<ul class="message-archive-list archive-list">

				<?php while ( have_posts() ) : ?>

					<?php the_post(); ?>
					<?php get_template_part( 'template-parts/content/content-single-message-card' ); ?>
				
				<?php endwhile; ?>

			</ul>
			<?php 
			$pagination_args = array(
				'prev_text' => '<div class="pagination-icon"><p class="a11y-visible" style="color: #fff;">Previous Page</p></div>',
				'next_text' => '<div class="pagination-icon"><p class="a11y-visible" style="color: #fff;">Next Page</p></div>',			
				'screen_reader_text' => 'Message Archive Navigation',
				'end_size' => 10,
				'mid_size' => 10,
				'aria_label' => 'Messages'
			);
			the_posts_pagination($pagination_args); ?>
		</div>
		<div class="content-end-triangle"><img src="<?php echo get_stylesheet_directory_uri();?>/assets/images/content-end-triangle.svg" alt="" /></div>
	</div>
