<?php
/**
 * Two-Column Callout List Template.
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
	$class_name = 'two-column-callout-list';
	if ( ! empty( $block['className'] ) ) {
		$class_name .= ' ' . $block['className'];
	}
	if ( ! empty( $block['align'] ) ) {
		$class_name .= ' align' . $block['align'];
	}

	// Load values and assign defaults.
    $list_type_prefix = get_field( 'list_display_style' );
    if( have_rows('two_column_list_items') ):
        $item_count = 0;
	?>

        <div class="two-column-callout-list-component">
            <<?php echo esc_attr( $list_type_prefix ); ?> class="two-column-list">
            <?php while( have_rows('two_column_list_items') ) : the_row();
                $item_heading = get_sub_field('item_heading'); 
                $item_body = (get_sub_field('item_body')) ? get_sub_field('item_body') : '';
                $item_count++;
                ?>
                
                <li class="two-column-list-item">
                    <?php if($list_type_prefix == 'ol') : ?>
                        <span class="two-column-callout-counter"><?php echo $item_count; ?></span>
                    <?php endif; ?>
                    <h4 class="two-column-list-item-heading"><?php echo strip_tags($item_heading, '<a>'); ?></h4>
                    <?php if($item_body) : ?>
                        <div class="two-column-list-item-body"><?php echo $item_body; ?></div>
                    <?php endif; ?>
                </li>

            <?php endwhile; ?>
           </<<?php echo esc_attr( $list_type_prefix ); ?>>
        </div>
    <?php endif; 
endif; ?>