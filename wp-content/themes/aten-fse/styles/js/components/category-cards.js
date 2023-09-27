/**
 * Custom JS for the News and Category cards
 */

jQuery( document ).ready(function($) {
	$('.news-query .news .wp-block-post-title a').hover(       
		function(){ $(this).closest('.news').addClass('hover-state') },
		function(){ $(this).closest('.news').removeClass('hover-state') }
	);

	$('.news-query .news .wp-block-post-title a').focus( function() {
		$(this).closest('.news').addClass('hover-state');
	});
	
	$('.news-query .news .wp-block-post-title a').blur( function() {
		$(this).closest('.news').removeClass('hover-state');
	});
});
