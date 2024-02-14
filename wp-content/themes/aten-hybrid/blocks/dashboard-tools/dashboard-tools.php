<?php
/**
 * Dashboard Tools Panel Template.
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
	$class_name = 'dashboard-tools-panel';
	if ( ! empty( $block['className'] ) ) {
		$class_name .= ' ' . $block['className'];
	}
	if ( ! empty( $block['align'] ) ) {
		$class_name .= ' align' . $block['align'];
	}

	// Load values and assign defaults.
    if( have_rows('tool_cards') ): 
        $card_counter = 0;
        $heading_text = (get_field('heading_text')) ? get_field('heading_text') : 'Tools';
        ?>

        <div class="dashboard-tools-component">
            <div class="tool-heading-wrap">
                <h2><?php echo $heading_text; ?></h2>
                <hr class="gray" />
            </div>
            <div class="tool-card-list-wrap">
                <ul class="tool-cards">
                    <?php while( have_rows('tool_cards') ): the_row(); 
                        $card_counter++;
                        $card_content = (get_sub_field('card_description')) ? get_sub_field('card_description') : '';
                        
                        if($card_counter == 1) {
                            $card_color = 'orange';
                            $card_icon = (get_sub_field('card_icon')) ? get_sub_field('card_icon') : 'message';
                            $card_title = (get_sub_field('card_title')) ? get_sub_field('card_title') : 'Message Lab';
                        } else if($card_counter == 2) {
                            $card_color = 'red';
                            $card_icon = (get_sub_field('card_icon')) ? get_sub_field('card_icon') : 'binoculars';
                            $card_title = (get_sub_field('card_title')) ? get_sub_field('card_title') : 'Research';
                        } else if($card_counter == 3) {
                            $card_color = 'purple';
                            $card_icon = (get_sub_field('card_icon')) ? get_sub_field('card_icon') : 'thumbs_up';
                            $card_title = (get_sub_field('card_title')) ? get_sub_field('card_title') : 'Social Dashboards';
                        }

                        $card_link = (get_sub_field('card_link')) ? get_sub_field('card_link') : '';
                        $card_link_title = $card_link_target = $card_link_url = '';
                        if(is_array($card_link) && isset($card_link['url'])) {
                            $card_link_title = (isset($card_link['title'])) ? $card_link['title'] : $card_title;
                            $card_link_target = (isset($card_link['target'])) ? $card_link['target'] : '_self';
                            $card_link_url = (isset($card_link['url'])) ? $card_link['url'] : '';
                        }
                        
                        ?>
                        <li class="tool-card <?php echo $card_color; ?> animate-fade-in-slide-up">
                            <img class="card-icon a11y-hidden" src="<?php echo get_stylesheet_directory_uri();?>/assets/icons/<?php echo $card_color; ?>/<?php echo $card_icon; ?>.svg" alt="" />
                            <h3>
                                <?php if($card_link) : ?>
                                    <a href="<?php echo $card_link_url; ?>" title="<?php echo $card_link_title; ?>" target="<?php echo $card_link_title; ?>">
                                <?php endif; 

                                 echo $card_title; 

                                if($card_link) : ?>
                                    </a>
                                 <?php endif; ?>
                            </h3>
                            <?php if($card_content): ?>
                                <p><?php echo $card_content; ?></p>
                            <?php endif; ?>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>
        </div>

    <?php endif; // If tool cards exist
endif; ?>