/**
 * Makes sure we have all the required levels on the TEC Object
 *
 * @since 6.0.0
 *
 * @type {PlainObject}
 */
window.tec = window.tec || {};

/**
 * Configures Editor Dialog Object in the Global TEC variable
 *
 * @since 6.0.0
 *
 * @type {PlainObject}
 */
tec.editorDialog = tec.editorDialog || {};

/**
 * Handles the JavaScript for editor dialog.
 *
 * @since 6.0.0
 *
 * @param {jQuery} $ jQuery.
 * @param {PlainObject} obj tec.editorDialog object.
 */
( function ( $, obj ) {
	"use strict";
	const $document = $( document );

	/**
	 * Selectors used for configuration and setup.
	 *
	 * @since 6.0.0
	 *
	 * @type {PlainObject}
	 */
	obj.selectors = {
		dialog: '#tec-events-pro-dialog',
	};

	/**
	 * Elements used in this module.
	 *
	 * @since 6.0.0
	 *
	 * @type {PlainObject}
	 */
	obj.el = {
		$instance: null,
	};

	/**
	 * Default settings for the dialog.
	 *
	 * @since 6.0.0
	 *
	 * @type {PlainObject}
	 */
	obj.defaultDialogSettings = {
		autoOpen: false,
		buttons: [],
		closeOnEscape: true,
		closeText: 'Cancel', // @todo: Move this to translatable string in PHP.
		draggable: false,
		modal: true,
		resizable: false,
		width: '330px',
	};

	/**
	 * Set the dialog settings.
	 *
	 * @since 6.0.0
	 *
	 * @param {PlainObject} settings The dialog settings.
	 *
	 * @return {void}
	 */
	obj.setDialogSettings = function ( settings ) {
		obj.el.$instance.dialog( settings );
	};

	/**
	 * Opens the dialog.
	 *
	 * @since 6.0.0
	 *
	 * @return {void}
	 */
	obj.openDialog = function () {
		obj.el.$instance.dialog( 'open' );
	};

	/**
	 * Closes the dialog.
	 *
	 * @since 6.0.0
	 *
	 * @return {void}
	 */
	obj.closeDialog = function () {
		obj.el.$instance.dialog( 'close' );
	};

	/**
	 * Returns the HTML code for the dialog element to append to the page.
	 *
	 * @todo: remove this after properly moving this to PHP.
	 *
	 * @since 6.0.0
	 *
	 * @return {string} The HTML code of the dialog element to append to the page.
	 */
	obj.getDialogElement = function () {
		return '<div id="tec-events-pro-dialog" class="tec-events-pro-dialog"></div>';
	};

	/**
	 * Sets up the dialog element and style.
	 *
	 * @todo: remove this after properly moving this to PHP or CSS.
	 *
	 * @since 6.0.0
	 *
	 * @return {void}
	 */
	obj.setupDialog = function () {
		const $body = $( document.body );
		$body.append( obj.getDialogElement() );
		obj.el.$instance = $( obj.selectors.dialog );
	};

	/**
	 * Binds events for the editor dialog change listeners.
	 *
	 * @since 6.0.0
	 *
	 * @return {void}
	 */
	obj.bindEvents = function () {
		$document.on( 'setup.dependency', obj.setupDialog );
	};

	/**
	 * Initializes the editor dialog JS.
	 *
	 * @since 6.0.0
	 *
	 * @return {void}
	 */
	obj.ready = function () {
		obj.bindEvents();
	};

	$( obj.ready );
} )( jQuery, tec.editorDialog );
