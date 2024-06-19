/**
 * Custom JS for the Accordion Block
 */

jQuery( document ).ready(function($) {
	// Collapsing all panels by default on page load
	collapseAccordionPanels();
	// Removing default hidden class
	$('.accordion-block-panel.collapsed').removeClass('collapsed');

	// Toggle the panel open on click
	$('.accordion-block-button').click(function(){
		toggleAccordion($(this));
	});

	// Collapse all accordion panels and set the aria-expanded value accordingly
	function collapseAccordionPanels() {
		$('.accordion-block-panel').slideUp();
		$('.accordion-block-item.active').removeClass('active');
		$('.accordion-block-button').attr('aria-expanded', 'false');
        $('.accordion-icon.open').hide();
        $('.accordion-icon.closed').show();
	}

	// Toggle panel open and closed with animation
	function toggleAccordion(trigger) {
		let panel_id = $(trigger).attr('aria-controls');
		// If it's collapsed
		if($(trigger).attr('aria-expanded') === 'false') {
			// Hide all open panels
			collapseAccordionPanels();
			// Open the selected panel
			$(trigger).attr('aria-expanded', 'true');
			$(trigger).closest('.accordion-block-item').addClass('active');
            $(trigger).find('img.closed').hide();
            $(trigger).find('img.open').show();
			$('#' + panel_id).slideDown();
		} else {
			// If it's already open, close all panels
			collapseAccordionPanels();
		}
	}
});
