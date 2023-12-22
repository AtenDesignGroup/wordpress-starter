/**
 * Makes sure we have all the required levels on the TEC Object
 *
 * @since 6.0.0
 *
 * @type {PlainObject}
 */
window.tec = window.tec || {};

/**
 * Configures Event Series Metabox Object in the Global TEC variable
 *
 * @since 6.0.0
 *
 * @type {PlainObject}
 */
tec.eventSeriesMetabox = tec.eventSeriesMetabox || {};

/**
 * Handles the JavaScript for assigning an event to a series.
 *
 * @since 6.0.0
 *
 * @param {jQuery} $ jQuery.
 * @param {PlainObject} obj tec.eventSeriesMetabox object.
 */
( function ( $, obj ) {
	"use strict";

	/**
	 * Selectors used for configuration and setup.
	 *
	 * @since 6.0.0
	 *
	 * @type {PlainObject}
	 */
	obj.selectors = {
		eventSeriesSelect: '#_tec_relationship_event_to_series',
		editLinkContainer: '.tec-events-pro-series__edit-link-container',
		editLink: '.tec-events-pro-series__edit-link',
	};

	/**
	 * Classes used for adding to or removing from elements.
	 *
	 * @since 6.0.0
	 *
	 * @type {PlainObject}
	 */
	obj.classes = {
		hidden: 'hidden',
	};

	/**
	 * Elements used in this module.
	 *
	 * @since 6.0.0
	 *
	 * @type {PlainObject}
	 */
	obj.el = {
		$select: null,
		$linkContainer: null,
		$link: null,
	};

	/**
	 * Handle change event for event series metabox.
	 *
	 * @since 6.0.0
	 */
	obj.handleChange = function () {
		var $selected = obj.el.$select.find( ':selected' );

		// If for some reason there is no selected option, return early.
		if ( ! $selected.length ) {
			return;
		}

		// If selected option is "Create or find a series", hide edit link.
		if ( '-1' === $selected.val() ) {
			obj.el.$linkContainer.addClass( obj.classes.hidden );
			return;
		}

		var editLink = $selected.data( 'editLink' );

		// If edit link is empty, hide edit link.
		if ( ! editLink ) {
			obj.el.$linkContainer.addClass( obj.classes.hidden );
			return;
		}

		obj.el.$link.attr( 'href', editLink );
		obj.el.$linkContainer.removeClass( obj.classes.hidden );
	};

	/**
	 * Binds events for the dropdown.
	 *
	 * @since 6.0.0
	 */
	obj.bindEvents = function () {
		obj.el.$select
			.on( 'change', obj.handleChange );
	};

	/**
	 * Get elements used in event series metabox.
	 *
	 * @since 6.0.0
	 */
	obj.getElements = function () {
		obj.el.$linkContainer = obj.el.$select.parent().find( obj.selectors.editLinkContainer );
		obj.el.$link = obj.el.$linkContainer.find( obj.selectors.editLink );
	};

	/**
	 * Initializes the event series metabox JS.
	 *
	 * @since 6.0.0
	 */
	obj.ready = function () {
		obj.el.$select = $( obj.selectors.eventSeriesSelect );

		if ( ! obj.el.$select.length ) {
			return;
		}

		obj.getElements();
		obj.bindEvents();
	};

	// Run when document is ready.
	$( obj.ready );
} )( jQuery, tec.eventSeriesMetabox );
