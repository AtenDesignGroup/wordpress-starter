<?php
/**
 * Pullquote Block Template.
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
	$class_name = 'pullquote-block';
	if ( ! empty( $block['className'] ) ) {
		$class_name .= ' ' . $block['className'];
	}
	if ( ! empty( $block['align'] ) ) {
		$class_name .= ' align' . $block['align'];
	}

	// Load values and assign defaults.
	$quote = get_field( 'quote' );
	$citation = get_field( 'citation' );

	?>

	<div <?php echo $anchor; ?>class="<?php echo esc_attr( $class_name ); ?> pullquote-component l-gutter">
		<figure class="pullquote-text">
			<blockquote cite="<?php echo esc_html( $citation ); ?>">
				<span class="quotation-punctuation opening-quote">&#34;</span><?php echo $quote; ?><span class="quotation-punctuation">&#34;</span>
			</blockquote>
			<figcaption class="pullquote-citation">
				<cite><?php echo esc_html( $citation ); ?></cite>
			</figcaption>
		</figure>
	</div>
<?php endif; ?>