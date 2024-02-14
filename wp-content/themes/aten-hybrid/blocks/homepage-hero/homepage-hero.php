<?php
/**
 * Homepage Hero Block Template.
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
	$class_name = 'homepage-hero-block';
	if ( ! empty( $block['className'] ) ) {
		$class_name .= ' ' . $block['className'];
	}
	if ( ! empty( $block['align'] ) ) {
		$class_name .= ' align' . $block['align'];
	}

	// Load values and assign defaults.
    $title = get_field( 'hero_text' );
    $additional_classes = $button_link = $button_text = $button_target = $cta_text = $cta_link = $cta_link_text = '';

    $has_button = get_field( 'display_button_in_hero' );
    if($has_button) {
        $additional_classes .= ' with-hero-btn';
        $button = get_field( 'hero_button' );
        $button_link = (isset($button['button_link']['url'])) ? $button['button_link']['url'] : '';
        $button_text = (isset($button['button_text'])) ? $button['button_text'] : '';
        $button_link_title = (isset($button['button_link']['title'])) ? $button['button_link']['title'] : $button_text;
        $button_target = (isset($button['button_link']['target']) && $button['button_link']['target'] === '_blank') ? '_blank' : '_self';    
    }

    $has_cta = get_field( 'display_cta_banner_beneath_hero' );
    if($has_cta) {
        $additional_classes .= ' with-hero-cta';
        $cta_panel = get_field( 'homepage_cta_panel' );
        $cta_text = (isset($cta_panel['cta_text'])) ? $cta_panel['cta_text'] : '';
        $cta_link = (isset($cta_panel['cta_link']['url'])) ? $cta_panel['cta_link']['url'] : '';
        $cta_link_text = (isset($cta_panel['cta_link_text'])) ? $cta_panel['cta_link_text'] : '';
        $cta_link_title = (isset($cta_panel['cta_link']['title'])) ? $cta_panel['cta_link']['title'] : $cta_link_text;
        $cta_link_target = (isset($cta_panel['cta_link']['target'])) ? $cta_panel['cta_link']['target'] : '_self';
    }

	?>

	<div class="homepage-hero-component alignfull <?php echo $additional_classes; ?>">
		<div class="hero-text-wrap animate-fade-in">
            <h1><?php echo strip_tags($title, '<strong>'); ?></h1>
            <?php if($has_button): ?>
                <div class="hero-btn-wrap">
                    <a href="<?php echo $button_link; ?>" title="<?php echo $button_link_title; ?>" target="<?php echo $button_target; ?>">
                        <?php echo $button_text; ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
        <?php if($has_cta): ?>
            <div class="hero-cta-wrap">
                <div class="hero-cta">
                    <div class="hero-cta-text">
                        <h3><?php echo $cta_text; ?></h3>
                    </div>
                    <div class="hero-cta-btn">
                        <a class="" href="<?php echo $cta_link; ?>" title="<?php echo $cta_link_title; ?>" target="<?php echo $cta_link_target; ?>">
                            <?php echo $cta_link_text; ?>
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <div class="hero-decorative-lines"></div>
	</div>
<?php endif; ?>