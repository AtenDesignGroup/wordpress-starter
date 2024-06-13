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

 $research_id = (isset($args['resource_id'])) ? $args['resource_id'] : get_the_id();
 $title_heading_level = (isset($args['heading_level'])) ? $args['heading_level'] : 'h2';

 $card_link = get_the_permalink($research_id);
 $card_link_target = '_self';

 // Check for customizable research ACF field
 $is_external = get_field('link_to_external_research', $research_id) ? get_field('link_to_external_research', $research_id) : false;
 if($is_external) {
	$card_link = get_field('external_research_link', $research_id)['url'];
	$card_link_target = '_blank';
 }

 // Get research category
 $research_category = 'Research';
 $research_category_icon = 'target';
 $research_category_link = '/research-category/';
 // Check for a research category
 $research_cat_id = get_field('research_category', $research_id);
 if ($research_cat_id) {
	// Get the research category ID
	 $research_cat = get_term($research_cat_id); 
	 if ($research_cat) {
		 // Set the research category name
		 $research_category = esc_html($research_cat->name);
		 $research_category_link .= $research_cat->slug;

		 // Check for custom icon on the research category
		 if(get_field('icon', 'research-category_' . $research_cat->term_id)) {
			$research_category_icon = get_field('icon', 'research-category_' . $research_cat->term_id);
		 } 
	 }
 }

 // Get research topics
 $research_topic_html = '';
 $research_topic_ids = get_field('research_topics', $research_id);
 $research_topic_link = '/research-topic/';
 // Check for research topics
 if (is_array($research_topic_ids)) {
	$research_topic_html = '<ul>';
	for($i=0; $i <= 5; $i++) {
		if(isset($research_topic_ids[$i])) {
			$topic = get_term($research_topic_ids[$i]);
			$research_topic_html .= '<li><a href="' . $research_topic_link . $topic->slug . '">' . $topic->name . '</a></li>';
		}
	 }
	 $research_topic_html .= '</ul>';
 }

 // Get research partner
 $research_partner_html = '<p>';
 $research_partner_id = get_field('research_partner', $research_id);
 if($research_partner_id) {
	$partner = get_term($research_partner_id); 
	$research_partner_html .= $partner->name; 
 }
 $research_partner_html .= '</p>';

?>

<li class="research-archive-card archive-card animate-fade-in-slide-up">
	<!-- Border color divs for transitioning opacity to "animate" gradient borders -->
	<div class="card-border-wrapper"></div>
	<div class="card-border-wrapper hover"></div>

	<article id="post-<?php echo $research_id; ?>" <?php post_class($research_id); ?>>

		<div class="entry-content">
			<div class="external-icon">
 				<?php if($is_external) : ?>
					<img class="research-external-icon" alt="External Link" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/icons/ui/white/external.svg">
				<?php endif; ?>
			</div>

			<div class="research-details card-details">
				<p class="research-category card-category">
					<img class="research-card-icon category-icon" alt="" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/icons/white/<?php echo $research_category_icon; ?>.svg">
					<a href="<?php echo $research_category_link; ?>">
						<?php echo $research_category; ?>
					</a>
				</p>
				<div class="card-title">
					<<?php echo $title_heading_level; ?> class="entry-title">
						<a href="<?php echo $card_link; ?>" title="<?php echo get_the_title($research_id); ?>" target="<?php echo $card_link_target; ?>">
							<?php echo get_the_title($research_id); ?>
						</a>
					</<?php echo $title_heading_level; ?>>
				</div>
				<div class="research-topic-wrapper topic-wrapper">
					<?php echo $research_partner_html; ?>
					<?php echo $research_topic_html; ?>
				</div>
			</div>

		</div><!-- .entry-content -->

	</article><!-- #post-<?php the_ID(); ?> -->
</li>