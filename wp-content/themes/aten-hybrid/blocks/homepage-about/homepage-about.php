<?php
/**
 * Homepage About Block Template.
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
	$class_name = 'homepage-about-block';
	if ( ! empty( $block['className'] ) ) {
		$class_name .= ' ' . $block['className'];
	}
	if ( ! empty( $block['align'] ) ) {
		$class_name .= ' align' . $block['align'];
	}

	// Load values and assign defaults.
    $icon = (get_field( 'icon' )) ? get_field( 'icon' ) : 'group';
    $heading_text = (get_field( 'heading_text' )) ? get_field( 'heading_text' ) : 'About the Collaborative';
    $description_text = (get_field('description_text')) ? get_field( 'description_text' ) : '';

    if($description_text) : ?>

        <div class="homepage-about-component animate-fade-in-slide-up">
            <img class="card-icon a11y-hidden" src="<?php echo get_stylesheet_directory_uri();?>/assets/icons/gray/<?php echo $icon; ?>.svg" alt="" />
            <h3><?php echo $heading_text; ?></h3>
            <?php echo $description_text; ?>
        </div>
    <?php endif; // Check for description text
endif; ?>