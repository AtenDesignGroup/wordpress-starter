<?php
/**
 * Admin View: Events Manager - Split Upcoming warning wrapper
 *
 * @version  5.9.0
 *
 * @var string $modal_content The content of the modal.
 * @var string $modal_id      The ID of the modal.
 * @var string $modal_target  The target element for the modal.
 */
?>
<div class="tribe-common tec-pro-event-manager__modal-container tec-pro-event-manager__modal-container--split-upcoming-dialog" aria-hidden="true">
	<span id="<?php echo esc_attr( $modal_target ); ?>" data-js="trigger-dialog-<?php echo esc_attr( $modal_id ); ?>" data-content="dialog-content-<?php echo esc_attr( $modal_id ); ?>"></span>
	<?php echo $modal_content; ?>
</div>