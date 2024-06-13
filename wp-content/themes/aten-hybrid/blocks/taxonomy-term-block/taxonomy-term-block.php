<?php
/**
 * Taxonomy Term Block Template
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
	$class_name = 'taxonomy-term-block';
	if ( ! empty( $block['className'] ) ) {
		$class_name .= ' ' . $block['className'];
	}
	if ( ! empty( $block['align'] ) ) {
		$class_name .= ' align' . $block['align'];
	}

	// Load values and assign defaults.
	$post_id = get_the_ID();
	$post_type = get_post_type($post_id);
	$publication_date = get_the_date('n/j/y');
	$resource_category_id = $resource_topic_ids = $research_partner_id = '';
	$is_resource = $is_message = false;

	if($post_type === 'research'){
		$is_resource = true;
		$research_partner_id = (get_field('research_partner', $post_id)) ? get_field('research_partner', $post_id) : '';
		$publication_date = (get_field('research_publication_date', $post_id)) ? get_field('research_publication_date', $post_id) : $publication_date;
		$resource_category_id = (get_field('research_category', $post_id)) ? get_field('research_category', $post_id) : '';
		$resource_topic_ids = (get_field('research_topics', $post_id)) ? get_field('research_topics', $post_id) : '';
	} elseif($post_type === 'message') {
		$is_message = true;
		$resource_category_id = (get_field('message_category', $post_id)) ? get_field('message_category', $post_id) : '';
		$resource_topic_ids = (get_field('message_topics', $post_id)) ? get_field('message_topics', $post_id) : '';
	}

	if($post_type === 'research' || $post_type === 'message') :

	?>

	<div class="resource-taxonomy-term-wrapper">
		<div class="publication-information">
			<h2 class="a11y-visible">Resource Details</h2>
			<?php if($research_partner_id != '') : 
				$partner = get_term($research_partner_id); 
			?>
				<p class="research-partner"><span class="a11y-visible">Research Partner: </span><?php echo $partner->name; ?></p>
			<?php endif; ?>
			<p class="publication-date">Published <?php echo $publication_date; ?></p>
		</div>
		<div class="taxonomy-button-wrapper">
			<h3 class="a11y-visible">Resource Topics</h3>
			<ul class="taxonomy-button-list">
				<?php if($resource_category_id) : 
					$category = get_term($resource_category_id);
					$link_prefix = ($post_type === 'research') ? '/research-category/' : '/message-category/';
				?>
					<li class="taxonomy-button">
						<a href="<?php echo ($link_prefix . $category->slug); ?>" title="<?php echo $category->name; ?>">
							<?php echo $category->name; ?>
						</a>
					</li>
				<?php endif; ?>
				<?php if($resource_topic_ids) : 
					foreach($resource_topic_ids as $topic_id) :
						$topic = get_term($topic_id); 
						$link_prefix = ($post_type === 'research') ? '/research-topic/' : '/message-topic/';
				?>
					<li class="taxonomy-button">
						<a href="<?php echo ($link_prefix . $topic->slug); ?>" title="<?php echo $topic->name; ?>">
							<?php echo $topic->name; ?>
						</a>
					</li>
				<?php endforeach;
				endif; ?>
			</ul>
		</div>
	</div>
<?php endif; 
endif; ?>