/**
 * Makes sure we have all the required levels on the Tribe Object.
 *
 * @since 5.9.0
 *
 * @type {Object}
 */
tribe.events = tribe.events || {};
tribe.events.admin = tribe.events.admin || {};
tribe.events.admin.manager = tribe.events.admin.manager || {};

/**
 * Configures Views Toggle Recurrence Object in the Global Tribe variable.
 *
 * @since 5.9.0
 *
 * @type {Object}
 */
tribe.events.admin.manager.page = {};

/**
 * Initializes in a Strict env for managing the Toggle Recurrence Input.
 *
 * @since 5.9.0
 *
 * @param  {Object} $   jQuery
 * @param  {Object} obj tribe.events.admin.manager.page
 *
 * @return {void}
 */
( function( $, obj ) {
	const $document = $( document );

	/**
	 * Selectors used for configuration and setup.
	 *
	 * @since 5.9.0
	 *
	 * @type {Object}
	 */
	obj.selectors = {
		splitLink: '.tribe_events_page_tribe-admin-manager .tribe-split-all',
		splitSingleLink: '.tribe_events_page_tribe-admin-manager .tribe-split-single',
		splitLinkClose: '.tribe_events_page_tribe-admin-manager .tribe-dialog__wrapper .tribe-dialog__close-button--hidden', // eslint-disable-line max-len
		splitLinkDialogCancel: '.tribe_events_page_tribe-admin-manager .tribe-dialog__button-cancel',
		splitLinkDialogConfirm: '.tribe_events_page_tribe-admin-manager .tribe-dialog__button-continue',
	};

	/**
	 * Property to store the currently selected/clicked "Edit Upcoming" link.
	 *
	 * @since 5.9.0
	 * @type {Object}
	 */
	obj.splitLinkSelection = null;

	/**
	 * Handles clicks on the split single links.
	 *
	 * @since 5.9.0
	 *
	 * @param {Object} event The event object.
	 *
	 * @return {boolean} Always false because we use the dialog to choose to proceed or not.
	 */
	obj.handleSplitSingleLinkClick = function() {
		let $el = $( this );
		obj.splitLinkSelection = $el;

		$el.trigger( 'mouseleave' );

		$( document.getElementById( 'tec-pro-event-manager__split-single-dialog' ) ).click();
		return false;
	};

	/**
	 * Handles clicks on the split all links.
	 *
	 * @since 5.9.0
	 *
	 * @param {Object} event The event object.
	 *
	 * @return {boolean} Always false because we use the dialog to choose to proceed or not.
	 */
	obj.handleSplitLinkClick = function() {
		let $el = $( this );
		obj.splitLinkSelection = $el;

		$el.trigger( 'mouseleave' );

		$( document.getElementById( 'tec-pro-event-manager__split-upcoming-dialog' ) ).click();
		return false;
	};

	/**
	 * Handles the Split Link Dialog Close Click.
	 *
	 * @since 5.9.0
	 */
	obj.handleSplitLinkDialogCancelClick = function() {
		obj.splitLinkSelection = null;
		$( obj.selectors.splitLinkClose ).click();
	};

	/**
	 * Handles the Split Link Dialog Confirm Click.
	 *
	 * @since 5.9.0
	 */
	obj.handleSplitLinkDialogConfirmClick = function() {
		document.location = obj.splitLinkSelection.attr( 'href' );
	};

	/**
	 * Handles the initialization of the scripts when Document is ready.
	 *
	 * @since 5.9.0
	 *
	 * @return {void}
	 */
	obj.ready = function() {
		$document.on( 'click', obj.selectors.splitLink, obj.handleSplitLinkClick );
		$document.on( 'click', obj.selectors.splitSingleLink, obj.handleSplitSingleLinkClick );
		$document.on( 
			'click', obj.selectors.splitLinkDialogCancel, obj.handleSplitLinkDialogCancelClick 
		);
		$document.on( 
			'click', obj.selectors.splitLinkDialogConfirm, obj.handleSplitLinkDialogConfirmClick 
		);
	};

	// Configure on document ready.
	$( obj.ready );
} )( jQuery, tribe.events.admin.manager.page );