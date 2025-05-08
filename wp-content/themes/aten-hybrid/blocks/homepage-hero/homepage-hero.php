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
    echo '<img src="' . esc_url( get_site_url() . $block['data']['preview_image'] ) . '" style="width:100%; height:auto;">';
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

	// Load values and assign defaults.
	$subtitle = get_field( 'subtitle' );
	$link = get_field( 'link' );
	$images = get_field('background_slider_images');
	$size = 'full';

	?>

	<div <?php echo $anchor; ?>class="<?php echo esc_attr( $class_name ); ?> homepage-hero-component">
		<section id="homepage-image-carousel" class="splide" role="group" aria-label="A carousel of images" aria-roledescription="carousel">
			<div class="splide__track">
				<ul class="splide__list" id="homepage-hero-images">
					<?php if($images) : foreach( $images as $img ): ?>
						<li class="splide__slide homepage-hero-image">
							<?php echo wp_get_attachment_image( $img['id'], $size ); ?>
						</li>
					<?php endforeach; endif; ?>
				</ul>
				<div id="homepage-slider-overlay"></div>
			</div>

			<div class="slide-autoplay-controls">
				<button class="pause-toggle-button" type="button" aria-label="Pause autoplay">pause_circle</button>
			</div>
		</section>

		<div class="homepage-hero-wrapper">
			<div class="homepage-hero-text-content">
				<div class="homepage-hero-title">
					<h1>Hello <span class="to">from</span> Aten<span class="california"> Design Group </span></h1>
				</div>
				<div class="homepage-hero-subtitle">
					<p><?php echo do_shortcode( '[city_hall_hours]' ); ?></p>
					<?php if( $link ):
						$link_target = isset($link['target']) ? $link['target'] : '_self'; ?>
						<a class="button btn-large--white" href="<?php echo esc_url( $link['url'] ); ?>" target="<?php echo esc_attr( $link_target ); ?>"><?php echo esc_html( $link['title'] ); ?></a>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<div class="hero-bottom-border" aria-hidden="true" >
			<img alt="" src="<?php echo get_template_directory_uri(); ?>/assets/img/homepage-hero-border.svg" />
		</div>
	</div>
<?php endif; ?>
