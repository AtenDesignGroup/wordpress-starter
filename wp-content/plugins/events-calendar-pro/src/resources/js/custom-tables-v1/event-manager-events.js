/**
 * Makes sure we have all the required levels on the TEC Object
 *
 * @since 6.0.0
 *
 * @type {PlainObject}
 */

window.tec = window.tec || {};

/**
 * Configures Classic Editor Events Dialog Object in the Global TEC variable
 *
 * @since 6.0.0
 *
 * @type {PlainObject}
 */
tec.classicEditorEventsDialog = tec.classicEditorEventsDialog || {};

/**
 * Adds an update type to the event manager page trash links.
 *
 * @since 6.0.0
 *
 * @param {jQuery} $ A reference to the global jQuery instance.
 * @param {PlainObject} obj tec.classicEditorEventsDialog object.
 */
( function ( $, obj ) {
	"use-strict";


	obj.selectors = {
		deleteButton: '.tribe-events .trash a',
		actionRow: '.tribe-events .row-actions',
	};

	/**
	 * Helper function to determine if a url string has the _tec_update_type query param already.
	 *
	 * @since 6.0.0
	 *
	 * @param {string}
	 *
	 * @return {boolean}
	 */
	function hasUpdateType(url) {
		if(url) {
			return url.indexOf('_tec_update_type') > 0;
		}
		return false;
	}

	/**
	 * Event listener for links that may need the update type param applied.
	 *
	 * @since 6.0.0
	 *
	 * @return {void}
	 */
	obj.addUpdateTypeToHref = function () {
		// someone is looking at the actions, lets parse the generated href
		$('.trash a', this).each(
			function (linkIndex, linkNode) {
				if(!hasUpdateType(linkNode.href)) {
					linkNode.href = linkNode.href + '&_tec_update_type=single';
				}
			}
		);
	}

	/**
	 * Binds event handlers to event listeners.
	 *
	 * @since 6.0.0
	 *
	 * @return {void}
	 */
	obj.bindEvents = function () {
		$(document)
			.on( 'mouseenter', obj.selectors.actionRow, obj.addUpdateTypeToHref );
	};

	/**
	 * Callback for jQuery ready event.
	 *
	 * @since 6.0.0
	 *
	 * @return {void}
	 */
	obj.ready = function () {
		obj.bindEvents();
	};

	$( obj.ready );
} )( jQuery, tec.classicEditorEventsDialog );
