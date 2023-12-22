<?php
/**
 * Admin View: Admin Manager Page
 *
 * @version  5.9.0
 *
 * @var Page $page The manager page.
 * @var Shortcode $shortcode The manager of the shortcode pieces.
 * @var array $views The post page's views.
 * @var array $bulk_messages The messages for the current single/bulk action that was performed.
 * @var array $bulk_counts The post counts for current single/bulk action that was performed.
 */

use Tribe\Events\Pro\Admin\Manager\Page;
use Tribe\Events\Pro\Admin\Manager\Shortcode;
use Tribe__Events__Main as TEC;

$add_new_url = admin_url( 'post-new.php' );
$args = [
	'post_type' => TEC::POSTTYPE,
];
$add_new_url = add_query_arg( $args, $add_new_url );

$edit_url = admin_url( 'edit.php' );
$args = [
	'post_type' => TEC::POSTTYPE,
];
$edit_url = add_query_arg( $args, $edit_url );

foreach ( $views as $class => $view ) {
	$views[ $class ] = "\t<li class='{$class}'>{$view}";
}
?>
<div class="wrap">
	<h1 class="wp-heading-inline">
		<?php esc_html_e( 'Events Manager', 'tribe-events-calendar-pro' ); ?>
	</h1>

	<a href="<?php echo esc_url( $add_new_url ); ?>" class="page-title-action"><?php esc_html_e( 'Add New', 'tribe-events-calendar-pro' ); ?></a>
	<a href="<?php echo esc_url( $edit_url ); ?>" class="page-title-action tec-admin-manager__link--list"><?php esc_html_e( 'List', 'tribe-events-calendar-pro' ); ?></a>
	<hr class="wp-header-end">
	<?php
	// Message output was largely taken from wp-admin/edit.php.
	// If we have a bulk message to issue:
	global $post_type, $post_type_object;
	$messages = [];
	foreach ( $bulk_counts as $message => $count ) {
		if ( isset( $bulk_messages[ $post_type ][ $message ] ) ) {
			$messages[] = sprintf( $bulk_messages[ $post_type ][ $message ], number_format_i18n( $count ) );
		} elseif ( isset( $bulk_messages['post'][ $message ] ) ) {
			$messages[] = sprintf( $bulk_messages['post'][ $message ], number_format_i18n( $count ) );
		}

		if ( 'trashed' === $message && isset( $_REQUEST['ids'] ) ) {
			$ids        = preg_replace( '/[^0-9,]/', '', $_REQUEST['ids'] );
			$url        = wp_nonce_url( "edit.php?tec_render=tribe-admin-manager&post_type=$post_type&doaction=undo&action=untrash&ids=$ids", 'bulk-posts' );
			if ( isset( $_REQUEST['paged'] ) ) {
				$url = add_query_arg( 'paged', $_REQUEST['paged'], $url );
			}
			$messages[] = '<a href="' . esc_url( $url ) . '">' . __( 'Undo' ) . '</a>';
		}

		if ( 'untrashed' === $message && isset( $_REQUEST['ids'] ) ) {
			$ids = explode( ',', $_REQUEST['ids'] );

			if ( 1 === count( $ids ) && current_user_can( 'edit_post', $ids[0] ) ) {
				$messages[] = sprintf(
					'<a href="%1$s">%2$s</a>',
					esc_url( get_edit_post_link( $ids[0] ) ),
					esc_html( get_post_type_object( get_post_type( $ids[0] ) )->labels->edit_item )
				);
			}
		}
	}

	if ( $messages ) {
		echo '<div id="message" class="updated notice is-dismissible"><p>' . implode( ' ', $messages ) . '</p></div>';
	}
	unset( $messages );
	?>
	<ul class='subsubsub'>
		<?php echo implode( " |</li>\n", $views ) . "</li>\n"; ?>
	</ul>

	<?php echo do_shortcode( $shortcode->get_shortcode_string() ); ?>

</div>
