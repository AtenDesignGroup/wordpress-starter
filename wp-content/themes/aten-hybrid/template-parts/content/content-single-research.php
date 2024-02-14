<?php
/**
 * Template part for displaying Research posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_One
 * @since Twenty Twenty-One 1.0
 */

// Get related resource ACF repeater 
$related_resources = get_field('related_resources');
$archive_slug = get_post_type_object( 'research' )->has_archive;
$research_custom_fields = get_field('research_archive', 'option');
$archive_title = ($research_custom_fields && $research_custom_fields['research_archive_title']) ? $research_custom_fields['research_archive_title'] : 'Evidence Center';

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<div class="entry-content">
		<?php
		the_content();

		if(have_rows('related_resources')) : ?>
			<div class="related-resources-wrapper alignfull">
				<hr class="gradient">
				<h2>Related</h2>
				<ul class="related-resources archive-list">
					<?php while(have_rows('related_resources')) : the_row();
						$related_resource = get_sub_field('related_resource');
						$resource_type = get_post_type($related_resource->ID);

						get_template_part( 'template-parts/content/content-single-' . $resource_type . '-card', null, array( 
							'resource_id' => $related_resource->ID,
							'heading_level' => 'h3'
						   )
						); 
										
					endwhile; ?>
				</ul>
				<div class="archive-btn animate-fade-in-slide-up">
					<a href="<?php echo get_site_url(); ?>/<?php echo $archive_slug; ?>/" title="View <?php echo $archive_title; ?>">View <?php echo $archive_title; ?></a>
				</div>
			</div> 
		<?php endif; ?>
	</div><!-- .entry-content -->

	<?php if ( get_edit_post_link() ) : ?>
		<footer class="entry-footer default-max-width">
			<?php
			edit_post_link(
				sprintf(
					/* translators: %s: Post title. Only visible to screen readers. */
					esc_html__( 'Edit %s', 'twentytwentyone' ),
					'<span class="screen-reader-text">' . get_the_title() . '</span>'
				),
				'<span class="edit-link">',
				'</span>'
			);
			?>
		</footer><!-- .entry-footer -->
	<?php endif; ?>
</article><!-- #post-<?php the_ID(); ?> -->
