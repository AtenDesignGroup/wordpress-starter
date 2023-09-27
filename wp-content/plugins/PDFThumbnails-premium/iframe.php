<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

function pdfth_my_plugin_url() {
	$basename = plugin_basename(__FILE__);
	if ('/'.$basename == __FILE__) { // Maybe due to symlink
		return plugins_url().'/'.basename(dirname(__FILE__)).'/';
	}
	// Normal case (non symlink)
	return plugin_dir_url( __FILE__ );
}

$useminified = true;
$maxwidth = 2000;
$imagetype = 'jpg';

$option = get_site_option('pdfth', Array());
if (is_array($option)) {
	if (isset($option['pdfth_maxwidth'])) {
		$maxwidth = intval( $option['pdfth_maxwidth'] );
	}
	if (isset($option['pdfth_imagetype']) && in_array($option['pdfth_imagetype'], array('png', 'jpg'))) {
		$imagetype = $option['pdfth_imagetype'];
	}
}

$pdfth_trans = array(
	'pdf_url' => set_url_scheme($_GET['pdfth_pdfurl']),
	'attachment_post_id' => $_GET['pdfth_postid'],
	'thumbnail_receive' => get_site_url().'/?pdfth_is_thumbnail_receive=1',
	'worker_src' => pdfth_my_plugin_url().'js/pdfjs/pdf.worker'.($useminified ? '.min' : '').'.js',
	'cmap_url' =>  pdfth_my_plugin_url().'js/pdfjs/cmaps/',
	'span_id' => $_GET['pdfth_span_id'],
	'nonce' => $_GET['pdfth_nonce'],
	'maxwidth' => $maxwidth,
	'imagetype' => $imagetype
);

?>
<html>
<head>
	<meta charset="UTF-8">
	<script type="text/javascript" src="<?php echo get_site_url(); ?>/wp-includes/js/jquery/jquery.js"></script>

	<script type="text/javascript" src="<?php echo pdfth_my_plugin_url().'js/pdfjs/pdf'.($useminified ? '.min' : '').'.js'; ?>"></script>

	<script type="text/javascript" src="<?php echo pdfth_my_plugin_url(); ?>js/iframe.js"></script>

	<script type="text/javascript">
		var pdfth_trans = <?php echo json_encode($pdfth_trans); ?>;
		console.log(pdfth_trans);
	</script>

</head>
<body>
</body>
</html>
