<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if (isset($_POST['post_id']) && isset($_POST['pdf_url']) && isset($_POST['nonce']) && isset($_POST['imagedata']))
{

	// Get the data
	$imageData = $_POST['imagedata']; //$GLOBALS['HTTP_RAW_POST_DATA'];
	$attachment_post_id = $_POST['post_id'];
	$pdf_url = $_POST['pdf_url'];
	$nonce = $_POST['nonce'];

	$imagetype = isset($_POST['imagetype']) && in_array($_POST['imagetype'], array('png', 'jpg')) ? $_POST['imagetype'] : 'jpg';

	if (!wp_verify_nonce($nonce, $pdf_url.'|'.$attachment_post_id)) {
		echo json_encode(Array('error' => 'Invalid nonce in thumbnail_receive'));
		exit;
	}

	// Remove the headers (data:,) part.
	// A real application should use them according to needs such as to check image type
	$filteredData=substr($imageData, strpos($imageData, ",")+1);

	// Need to decode before saving since the data we received is already base64 encoded
	$unencodedData=base64_decode($filteredData);


	// How to deal with attachment data

	$attachment_obj = get_post( $attachment_post_id );

	//Check for mime type pdf
	if( 'application/pdf' == get_post_mime_type( $attachment_obj ) ) {

		$filename = get_attached_file($attachment_post_id);
		$basename = str_replace('.', '-', basename($filename)) . '-image.'.$imagetype;

		$thumbnail_filename = trailingslashit(dirname($filename)).$basename;

		// Save image file. This example uses a hard coded filename for testing,
		// but a real application can specify filename in POST variable
		$fp = fopen( $thumbnail_filename, 'wb' );
		fwrite( $fp, $unencodedData);
		fclose( $fp );


		// Any existing thumbnail for this post?
		$existing_thumbnail_id = get_post_meta( $attachment_post_id, '_thumbnail_id', true );

		// Create thumbnail
		$attachment = array(
			'post_mime_type' => 'image/'.$imagetype,
			'post_type' => 'attachment',
			'post_excerpt' => $attachment_obj->post_excerpt,
			'post_content' => $attachment_obj->post_content,
			'post_title' => $attachment_obj->post_title . '-thumbnail',
			'post_parent' => $attachment_obj->ID
		);

		if ($existing_thumbnail_id) {
			// Update in this case
			$attachment['ID'] = $existing_thumbnail_id;
		}

		$thumbnailId = wp_insert_attachment($attachment, $thumbnail_filename);

		if (!function_exists('wp_generate_attachment_metadata')) {
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
		}

		$thumbnailMetadata = wp_generate_attachment_metadata($thumbnailId, $thumbnail_filename);
		wp_update_attachment_metadata($thumbnailId, $thumbnailMetadata);
		set_post_thumbnail($attachment_post_id, $thumbnailId);

		do_action('pdfemb_thumbnail_created', $attachment_post_id, $thumbnailId);

		echo json_encode(Array('success' => true));
	}
	else {
		echo json_encode(Array('error' => 'Call to thumbnail_receive is not for type PDF'));
	}

}
else {
	echo json_encode(Array('error' => 'Call to thumbnail_receive is missing a parameter'));
}

?>