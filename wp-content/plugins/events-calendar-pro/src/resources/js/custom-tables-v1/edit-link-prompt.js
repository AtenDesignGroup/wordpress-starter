/**
 * Handles the user clicking the edit links in the context of the
 * Series post editor and the Events Manager.
 */

( function( jQuery ) {
	'use strict';

	/**
	 * Handles the click on the update prompt link to
	 * get a confirmation from the user.
	 *
	 * @param {Event} event The dispatched click event.
	 *
	 * @returns {void}
	 */
	function onClick( event ) {
		const anchor = event.target;

		if ( ! anchor instanceof Element && anchor.tagName !== 'A' ) {
			return;
		}

		event.preventDefault();

		const confirmationMessage = anchor.dataset.confirm;
		if ( ! window.confirm( confirmationMessage ) ) {
			return;
		}

		if ( anchor.target === '_blank' ) {
			window.open( anchor.href, '_blank' );
			return;
		}

		window.location.href = anchor.href;
	}

	/**
	 * Fires when the document is ready to hook the click event handler.
	 *
	 * @return {void}
	 */
	function onReady() {
		Array.from( document.querySelectorAll( '.tec-edit-link[data-confirm]' ) ).
				map( link => link.addEventListener( 'click', onClick ) );
	}

	// On ready, bind to listen on clicks.
	if ( document.readyState !== 'loading' ) {
		onReady();
	}
	else {
		document.addEventListener( 'DOMContentLoaded', onReady );
	}

	/**
	 * Binds an event handler to re-bind the click handlers after the replaced
	 * container has finished setting up.
	 *
	 * @param {CustomEvent} event The event dispatched after the container
	 *     replacement
	 *
	 * @return {void} Listens for the event and rebinds the click handlers.
	 */
	function onContainerReplace( event ) {
		const $container = event?.detail;

		if ( ! $container instanceof jQuery ) {
			return;
		}

		$container.on( 'afterAjaxSuccess.tribeEvents', onReady );
	}

	// After the container is replaced, we need to rebind the click event.
	document.addEventListener(
			'containerReplaceAfter.tribeEvents',
			onContainerReplace,
	);
} )( jQuery );