<?php
/**
 * Template partial for research archive blocks
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package WordPress
 * @subpackage ccc
 */

$research_custom_fields = get_field('research_archive', 'option');
$title = ($research_custom_fields && $research_custom_fields['research_archive_title']) ? $research_custom_fields['research_archive_title'] : 'Evidence Center';
$description = ($research_custom_fields && $research_custom_fields['research_archive_subtitle']) ? $research_custom_fields['research_archive_subtitle'] : '';
$has_featured_research = is_array($research_custom_fields['featured_research']);

?>

	<header class="page-header alignwide">
		<h1 class="page-title"><?php echo $title; ?></h1>
		<?php if ( $description ) : ?>
            <div class="archive-description"><p><?php echo $description; ?></p></div>
		<?php endif; ?>
	</header><!-- .page-header -->
	<?php if($has_featured_research) : ?>
		<div class="archive-featured-resource-wrapper">
			<ul class="featured-resource-list">
				<?php foreach($research_custom_fields['featured_research'] as $featured_post_array) : 
					$args = array(
						'p'         => $featured_post_array['research_post'],
						'post_type' => 'any'
					);
					$featured_post = new WP_Query($args);
					if($featured_post->have_posts()) : while ( $featured_post->have_posts() ) : $featured_post->the_post();
						get_template_part( 'template-parts/content/content-single-research-card' );
					endwhile; endif;
				endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>

	<div class="filter-container alignfull">
		<div id="filter-wrapper" role="region" class="filter-wrapper animate-fade-in-slide-up">
			<div class="filter-form-wrapper">
				<?php echo do_shortcode( '[searchandfilter slug="research-archive-filters"]' ); ?>
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
			<ul class="research-archive-list archive-list">

				<?php while ( have_posts() ) : ?>

					<?php the_post(); ?>
					<?php get_template_part( 'template-parts/content/content-single-research-card' ); ?>
				
				<?php endwhile; ?>

			</ul>
			<?php 
			$pagination_args = array(
				'prev_text' => '<div class="pagination-icon"><p class="a11y-visible" style="color: #fff;">Previous Page</p></div>',
				'next_text' => '<div class="pagination-icon"><p class="a11y-visible" style="color: #fff;">Next Page</p></div>',
				'screen_reader_text' => 'Research Archive Navigation',
				'end_size' => 10,
				'mid_size' => 10,
				'aria_label' => 'Research'
			);
			the_posts_pagination($pagination_args); ?>
		</div>
		<div class="content-end-triangle"><img src="<?php echo get_stylesheet_directory_uri();?>/assets/images/content-end-triangle.svg" alt="" /></div>
	</div>