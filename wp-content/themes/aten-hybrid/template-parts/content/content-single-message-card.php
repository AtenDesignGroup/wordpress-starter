<?php
/**
 * Template part for displaying Message posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_One
 * @since Twenty Twenty-One 1.0
 */
 $message_id = (isset($args['resource_id'])) ? $args['resource_id'] : get_the_id();
 $title_heading_level = (isset($args['heading_level'])) ? $args['heading_level'] : 'h2';

 // Check for customizable message ACF field
 $is_customizable = get_field('customizable_message', $message_id) ? get_field('customizable_message', $message_id) : false;

 // Get message category
 $message_category = 'Message';
 $message_category_icon = 'message';
 $message_category_link = '/message-category/';
 // Check for a message category
 $message_cat_id = get_field('message_category', $message_id);
 if ($message_cat_id) {
	// Get the message category ID
	 $message_cat = get_term($message_cat_id); 
	 if ($message_cat) {
		 // Set the message category name
		 $message_category = esc_html($message_cat->name);
		 $message_category_link .= $message_cat->slug;

		 // Check for custom icon on the message category
		 if(get_field('icon', 'message-category_' . $message_cat->term_id)) {
			$message_category_icon = get_field('icon', 'message-category_' . $message_cat->term_id);
		 } 
	 }
 }

 // Get message topics
 $message_topic_html = '';
 $message_topic_ids = get_field('message_topics', $message_id);
 $message_topic_link = '/message-topic/';
 // Check for message topics
 if (is_array($message_topic_ids)) {
	$message_topic_html = '<ul>';
	for($i=0; $i <= 5; $i++) {
		if(isset($message_topic_ids[$i])) {
			$topic = get_term($message_topic_ids[$i]);
			$message_topic_html .= '<li><a href="' . $message_topic_link . $topic->slug . '">' . $topic->name . '</a></li>';
		}
	 }
	 $message_topic_html .= '</ul>';
 }

?>

<li class="message-archive-card archive-card animate-fade-in-slide-up">
	<!-- Border color divs for transitioning opacity to "animate" gradient borders -->
	<div class="card-border-wrapper"></div>
	<div class="card-border-wrapper hover"></div>
	
	<article id="post-<?php echo $message_id; ?>" <?php post_class($message_id); ?>>

		<div class="entry-content">
			<div class="customizable-tag">
 				<?php if($is_customizable) : ?>
					<img class="message-customizable-icon" alt="" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/icons/white/pencil.svg">
					<p>Customizable</p>
				<?php endif; ?>
			</div>

			<div class="message-details card-details">
				<p class="message-category card-category">
					<img class="message-card-icon category-icon" alt="" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/icons/white/<?php echo $message_category_icon; ?>.svg">
					<a href="<?php echo $message_category_link; ?>">
						<?php echo $message_category; ?>
					</a>
				</p>
				<div class="card-title">
					<<?php echo $title_heading_level; ?> class="entry-title">
						<a href="<?php echo get_the_permalink($message_id); ?>" title="<?php echo get_the_title($message_id); ?>">
							<?php echo get_the_title($message_id); ?>
						</a>
					</<?php echo $title_heading_level; ?>>
				</div>
				<div class="message-topic-wrapper topic-wrapper">
					<?php echo $message_topic_html; ?>
				</div>
			</div>

		</div><!-- .entry-content -->

	</article><!-- #post-<?php the_ID(); ?> -->
</li>