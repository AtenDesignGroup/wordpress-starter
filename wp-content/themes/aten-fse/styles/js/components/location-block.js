/**
 * Custom JS for the Location block
 */

jQuery( document ).ready(function($) {
	// On hover of the location block title, zoom the location image 
	$('.location-block-title').each(function() {
		$( this ).hover(
			function() {
				$( this ).closest( '.location-information' ).siblings( '.location-image.custom-image' ).addClass( 'image-zoom' );
			  }, function() {
				$( this ).closest( '.location-information' ).siblings( '.location-image.custom-image' ).removeClass( 'image-zoom' );
			  }
		);
	});
});