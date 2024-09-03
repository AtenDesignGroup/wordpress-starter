<?php
/**
 * Column Block Template.
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
    $class_name = 'column-block';
    if ( ! empty( $block['className'] ) ) {
        $class_name .= ' ' . $block['className'];
    }
    if ( ! empty( $block['align'] ) ) {
        $class_name .= ' align' . $block['align'];
    }

    // Looping through repeater for Column Blocks
    if( have_rows('column-blocks') ): 
        $column_count = 0;
        $columns = get_field('column-blocks');
        if (is_array($columns)) {
            $column_count = count($columns);
        }
    ?>

    <div <?php echo $anchor; ?>class="<?php echo esc_attr( $class_name ); ?>">
        <div class="column-blocks-wrapper">
            <ul class="column-layout-<?php echo $column_count; ?>">
                <?php while( have_rows('column-blocks') ) : the_row();
                    // Getting the subfield values
                    $title = get_sub_field( 'title' );
                    $image = get_sub_field( 'image' );
                    $link = get_sub_field( 'link' );
                    $body_text = get_sub_field( 'body_text' );
                    ?>
                    <li class="column-block-column <?php if( !empty( $image ) ) { echo "with-image"; } ?> <?php if($column_count === 1) { echo "single-column"; } ?>">
                        <?php
                        if( !empty( $image ) ): ?>
                            <div class="column-block-image-wrapper">
                                <img src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt']); ?>" />
                            </div>
                        <?php endif; ?>
                        <div class="column-block-content">
                            <h2><?php echo $title; ?></h2>

                            <?php if ($body_text) : ?> 
                                <div class="column-block-text">
                                    <?php echo $body_text; ?>
                            </div>
                            <?php endif; ?>

                            <?php
                                if( $link ):
                                $link_url = $link['url'];
                                $link_title = $link['title'];
                                $link_target = $link['target'] ? $link['target'] : '_self';
                                ?>
                                <div class="column-block-link">
                                    <a class="button btn-large--navy" href="<?php echo esc_url( $link_url ); ?>" target="<?php echo esc_attr( $link_target ); ?>">
                                        <?php echo esc_html( $link_title ); ?>&nbsp;<span class="button-icon notranslate" aria-hidden="true">arrow_circle_right</span>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if($column_count === 1) : ?>
                            <div class="wave-border">
                                <img src="<?php echo get_template_directory_uri(); ?>/assets/img/callout-wave-cloud.svg" alt="" />
                            </div>
                        <?php endif; ?>
                    </li>
                <?php endwhile; ?>
            </ul>
        </div>
    </div>

    <?php endif;
endif; ?>