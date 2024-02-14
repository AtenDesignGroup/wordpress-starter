<?php
/**
 * Dashboard Hero Block Template.
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
	$class_name = 'dashboard-hero';
	if ( ! empty( $block['className'] ) ) {
		$class_name .= ' ' . $block['className'];
	}
	if ( ! empty( $block['align'] ) ) {
		$class_name .= ' align' . $block['align'];
	}

	// Load values and assign defaults.
    $hero_text = (get_field( 'hero_text' )) ? get_field( 'hero_text' ) : '';
    $user_fname = (wp_get_current_user() && wp_get_current_user()->user_firstname) ? ', <strong>' . wp_get_current_user()->user_firstname . '</strong>' : '';
    $welecome_text = 'Welcome' . $user_fname . '!';
	?>

	<div class="dashboard-hero-component alignfull">
		<div class="dashboard-hero-text-wrap animate-fade-in">
            <h1><?php echo $welecome_text; ?></h1>
            <?php if($hero_text): ?>
                <p class="dashboard-hero-text">
                    <?php echo $hero_text; ?>
                </p>
            <?php endif; ?>
        </div>
	</div>
<?php endif; ?>