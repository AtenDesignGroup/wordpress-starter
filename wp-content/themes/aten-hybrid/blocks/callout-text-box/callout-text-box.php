<?php
/**
 * Callout Text Box Template.
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
	$class_name = 'callout-text-box';
	if ( ! empty( $block['className'] ) ) {
		$class_name .= ' ' . $block['className'];
	}
	if ( ! empty( $block['align'] ) ) {
		$class_name .= ' align' . $block['align'];
	}

	// Load values and assign defaults.
    $title = (get_field( 'heading_text' )) ? get_field( 'heading_text' ) : '';
    $description = (get_field( 'callout_body_text' )) ? get_field( 'callout_body_text' ) : '';
    $button = (get_field('button') && is_array(get_field('button'))) ? get_field( 'button' ) : '';
    $button_type = (is_array($button) && isset($button['button_type'])) ? $button['button_type'] : '';
    $button_text = (is_array($button) && isset($button['button_text'])) ? $button['button_text'] : '';
    $button_link = (is_array($button) && isset($button['button_link'])) ? $button['button_link'] : '';
    $button_target = (isset($button['button_link']['target']) && $button['button_link']['target'] === '_blank') ? '_blank' : '_self';

	?>

	<div class="callout-text-box-component">
        <div class="callout-text-box-content">
            <?php if($title) { echo '<h2>' . $title . '</h2>'; } ?>
            <?php if($description) { echo $description; } 
                if($button && $button_type && $button_text && $button_link) : ?>
                <a href="<?php echo $button_link['url']; ?>" title="<?php echo $button_link['title']; ?>" class="custom-button callout-text-box-button <?php echo $button_type; if($button_target === '_blank') { echo ' external-link'; } ?>" target="<?php echo $button_target; ?>">
                    <?php echo $button_text; ?>
                </a>
            <?php endif; ?>
        </div>
	</div>
<?php endif; ?>