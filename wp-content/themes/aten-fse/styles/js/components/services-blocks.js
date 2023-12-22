/**
 * Custom JS for the All Services and Services List blocks
 */

jQuery( document ).ready(function($) {
    // On hover of the service card link, change the background color of the card
	$('.service-card-link').each(function() {
		$( this ).hover(
			function() {
				$( this ).closest( '.service-card' ).addClass( 'focused' );
			  }, function() {
				$( this ).closest( '.service-card' ).removeClass( 'focused' );
			  }
		);
	});
});