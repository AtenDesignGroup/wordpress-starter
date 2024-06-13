<?php
/**
 * Dashboard CTA Panel Template.
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
	$class_name = 'dashboard-cta-panel';
	if ( ! empty( $block['className'] ) ) {
		$class_name .= ' ' . $block['className'];
	}
	if ( ! empty( $block['align'] ) ) {
		$class_name .= ' align' . $block['align'];
	}

	// Load values and assign defaults.
    if( have_rows('cta_cards') ): ?>

        <div class="dashboard-cta-panel-component">
            <ul class="dashboard-cta-cards">
                <?php while( have_rows('cta_cards') ): the_row(); 
                    $card_title = (get_sub_field('card_title')) ? get_sub_field('card_title') : '';
                    $has_button = get_sub_field('display_cta_button');
                    $button_text = $button_link = $link_target = $link_title = $link_url = '';
                    if($has_button) {
                        $button_text = (get_sub_field('button_text')) ? get_sub_field('button_text') : 'Learn More';
                        $button_link = (get_sub_field('button_link')) ? get_sub_field('button_link') : '';
                    }
                    if(is_array($button_link)) {
                        $link_target = (isset($button_link['target'])) ? $button_link['target'] : '_self';
                        $link_title = (isset($button_link['title'])) ? $button_link['title'] : $button_text;
                        $link_url = (isset($button_link['url'])) ? $button_link['url'] : '';
                    }
                    ?>
                    <li class="dashboard-cta-card animate-fade-in-slide-up">
                        <h3><?php echo $card_title; ?></h3>
                        <?php if($has_button): ?>
                            <div class="dashboard-cta-btn-wrap button-with-icon large-button-purple">
                                <a href="<?php echo $link_url; ?>" title="<?php echo $link_title; ?>" target="<?php echo $link_target; ?>">
                                    <?php echo $button_text; ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </li>
                <?php endwhile; ?>
            </ul>
        </div>

    <?php endif; // If CTA cards exist
endif; ?>