<?php
/**
 * Rotating Banner Block Template.
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
    $class_name = 'rotating-banner-block';
    if ( ! empty( $block['className'] ) ) {
        $class_name .= ' ' . $block['className'];
    }
    if ( ! empty( $block['align'] ) ) {
        $class_name .= ' align' . $block['align'];
    }
    ?>

    <div <?php echo $anchor; ?>class="<?php echo esc_attr( $class_name ); ?> rotating-banner-component">
        <div class="rotating-banner-wrapper">
            <section id="rotating-banner" class="splide" role="group" aria-label="A rotating carousel of news and events" aria-roledescription="carousel">
                <div class="splide__track">
                    <ul class="splide__list">
                        <?php
                        // Check if the ACF repeater field has rows
                        if (have_rows('banner_slides')) {
                            // Loop through the rows of the repeater field
                            while (have_rows('banner_slides')) {
                                the_row();
                                // Get the image array using ACF's "sub_field" function
                                $image = get_sub_field('image');
                                $link = get_sub_field('link');
                                $description = get_sub_field('description');
                                ?>
                                <li class="splide__slide rotating-banner-slide">
                                    <div class="rotating-banner-slide-content">
                                        <?php
                                        if($image):
                                            ?>
                                            <div class="rotating-slide-image">
                                                <img src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt']); ?>">
                                            </div>
                                        <?php endif; ?>
                                        <div class="rotating-slide-description">
                                            <?php if( $link ):
                                                $link_url = $link['url'];
                                                $link_title = $link['title'];
                                                $link_target = $link['target'] ? $link['target'] : '_self';
                                                ?>
                                                    <h2><a class="button" href="<?php echo esc_url( $link_url ); ?>" target="<?php echo esc_attr( $link_target ); ?>"><?php echo esc_html( $link_title ); ?></a></h2>
                                            <?php endif; 
                                            if ($description) :  ?>
                                                <p><?php echo $description; ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </li>
                                <?php
                            }
                        }
                        ?>
                    </ul>
                </div>

                <div class="splide__arrows">
                    <button class="splide__arrow splide__arrow--prev" aria-label="Previous Slide">
                        arrow_circle_left
                    </button>
                    <button class="splide__arrow splide__arrow--next" aria-label="Next Slide">
                        arrow_circle_right
                    </button>
                </div>
            </section>
        </div>
    </div>
<?php endif; ?>
