/**
 * Custom JS for default WP Search functionality
 */

jQuery( document ).ready(function($) {
    $('.search-results .wp-block-query h2.wp-block-post-title').each(function(){
        $(this).convertElement('h3');
    });

    $('.search-query h3 a').hover(       
		function(){ $(this).closest('li').addClass('hover-state') },
		function(){ $(this).closest('li').removeClass('hover-state') }
	);

	$('.search-query h3 a').focus( function() {
		$(this).closest('li').addClass('hover-state');
	});
	
	$('.search-query h3 a').blur( function() {
		$(this).closest('li').removeClass('hover-state');
	});
});