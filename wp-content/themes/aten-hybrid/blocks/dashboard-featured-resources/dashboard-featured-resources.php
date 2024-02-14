<?php
/**
 * Dashboard Featured Resources Template.
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
	$class_name = 'dashboard-featured-resources';
	if ( ! empty( $block['className'] ) ) {
		$class_name .= ' ' . $block['className'];
	}
	if ( ! empty( $block['align'] ) ) {
		$class_name .= ' align' . $block['align'];
	}

	// Load values and assign defaults.
    $title = (get_field( 'featured_resources_title' )) ? get_field( 'featured_resources_title' ) : 'Featured Resources';
    $additional_classes = $button_link = $button_text = $button_target = $cta_text = $cta_link = $cta_link_text = '';
    if( have_rows('featured_resources') ): ?>
        <div class="dashboard-featured-resource-component">
            <div class="resource-title-wrapper">
                <h2><?php echo $title; ?></h2>
                <hr class="white" />
            </div>

            <div class="featured-resource-wrapper">
                <ul class="featured-resources">
                    <?php while( have_rows('featured_resources') ): the_row();
                        $resource_id = get_sub_field('resource');
                        $resource_type = $resource_icon = $resource_link = $resource_title = '';
                        if($resource_id) {
                            $resource_post_type = get_post_type($resource_id);
                            $resource_type = ($resource_post_type == 'research') ? 'Research' : 'Message';
                            $resource_icon = ($resource_post_type == 'research') ? 'target' : 'message';
                            $resource_link = get_the_permalink($resource_id);
                            $resource_title = get_the_title($resource_id);
                            // Get resource topics
                            $resource_topic_html = '';
                            $resource_topic_ids = ($resource_post_type == 'research') ? get_field('research_topics', $resource_id) : get_field('message_topics', $resource_id);
                            $resource_topic_link = '/' . strtolower($resource_type) . '-topic/';
                            // Check for resource topics
                            if (is_array($resource_topic_ids)) {
                                $resource_topic_html = '<ul>';
                                for($i=0; $i <= 5; $i++) {
                                    if(isset($resource_topic_ids[$i])) {
                                        $topic = get_term($resource_topic_ids[$i]);
                                        $resource_topic_html .= '<li><a href="' . $resource_topic_link . $topic->slug . '">' . $topic->name . '</a></li>';
                                    }
                                }
                                $resource_topic_html .= '</ul>';
                            }

                            // Check for a resource category
                            $resource_cat_id = get_field('' . $resource_post_type . '_category', $resource_id);
                            if ($resource_cat_id) {
                                // Get the resource category ID
                                $resource_cat = get_term($resource_cat_id); 
                                if ($resource_cat) {
                                    $resource_type = esc_html($resource_cat->name);
                                    // Check for custom icon on the research category
                                    if(get_field('icon', '' . $resource_post_type . '-category_' . $resource_cat->term_id)) {
                                        $resource_icon = get_field('icon', '' . $resource_post_type . '-category_' . $resource_cat->term_id);
                                    } 
                                }
                            }
                        } ?>
                        <li class="featured-resource animate-fade-in-slide-up">
                            <!-- Border color divs for transitioning opacity to "animate" gradient borders -->
                            <div class="resource-border-wrapper"></div>
                            <div class="resource-border-wrapper hover"></div>
                            <div class="resource-contents">
                                <div class="resource-type">
                                    <img src="<?php echo get_stylesheet_directory_uri();?>/assets/icons/white/<?php echo $resource_icon; ?>.svg" alt="" class="a11y-hidden resource-icon" />
                                    <p><?php echo $resource_type; ?></p>
                                </div>
                                <h3>
                                    <a href="<?php echo $resource_link; ?>" target="_self">
                                        <?php echo $resource_title; ?>
                                    </a>
                                </h3>
                                <div class="resource-topic-wrapper topic-wrapper">
                                    <?php echo $resource_topic_html; ?>
                                </div>
                            </div>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>
        </div>
    <?php endif; 
endif; ?>