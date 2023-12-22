<?php
/**
 * PDF VIew
 *
 * @package WP PDF Secure
 */

// get instance of embedder.
$pdf_embedder = WP_PDF_Premium::get_instance();

// process any override options/arguments passed in the url.
$args = isset( $_GET ) ? $_GET : array(); // @codingStandardsIgnoreLine

// should we enable some caching (javascript, etc.).
$should_cache = apply_filters( 'pdfemb_view_should_cache', time(), $args );

?> <!DOCTYPE html>
<html lang="en-us" id="wp-pdf-embbed" style="height: 100%;">
<head>
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<meta name='robots' content='noindex, nofollow' />
	<link rel="resource" type="application/l10n" href="<?php echo esc_url( trailingslashit( WP_PDF_SECURE_URL ) . '/assets/js/pdfjs/locale/locale.properties' ); ?>">

<?php

	do_action( 'wp_pdf_viewer_head' );
	?>

</head>
<body id="wppdf-iframe-body">
	<?php

	echo $pdf_embedder->pdfemb_shortcode_display_pdf_noncanvas_process( apply_filters( 'pdfemb_shortcode_display_args', $args ) ); //@codingStandardsIgnoreLine

	do_action( 'wp_pdf_viewer_footer' );
	?>

</body>
</html>
