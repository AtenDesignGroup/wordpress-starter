<?php
/**
 * @var WP_Post $post A reference to the Series post object.
 */

global $pagenow;
$meta = 'post-new.php' !== $pagenow ? get_post_meta( $post->ID, '_tec-series-show-title', true ) : true;
?>

<div class="tec-series">
	<input type="checkbox" name="_tec-series-show-title" id="tec-series-show-title" <?php checked( $meta ); ?> value="1" />
	<label class="tec-series-show-title__label" for="tec-series-show-title">
		<?php esc_html_e( 'Show Series title on event pages and calendar views', 'tribe-events-calendar-pro' ); ?>
	</label>
</div>
