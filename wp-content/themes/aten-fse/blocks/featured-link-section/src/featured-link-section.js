/**
 * Custom JS for the Featured Link Section block
 */

jQuery( document ).ready(function($) {
	// On hover of the featured button links, zoom the display image in the background
	$('.featured-button').each(function() {
		$( this ).hover(
			function() {
				$( this ).closest( '.title-wrapper' ).siblings( '.title-bg' ).addClass( 'image-zoom' );
			  }, function() {
				$( this ).closest( '.title-wrapper' ).siblings( '.title-bg' ).removeClass( 'image-zoom' );
			  }
		);
	});
});