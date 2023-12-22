/**
 * Build the Object path for series handling.
 *
 * @since 6.0.0
 *
 * @type  {Object}
 */
tribe.events = tribe.events || {};
tribe.events.pro = tribe.events.pro || {};
tribe.events.pro.admin = tribe.events.pro.admin || {};
tribe.events.pro.admin.series = tribe.events.pro.admin.series || {};

/**
 * Trash Handler object for Series on the Admin.
 *
 * @since 6.0.0
 *
 * @type  {Object}
 */
tribe.events.pro.admin.series.trashHandler = {};

// Wrapper to avoid global namespace.
( ( $, obj ) => {
	'use strict';

	obj.selectors = {
		dialogElement: '#tec-events-pro-series-trash-dialog',
	};

	/**
	 * Builds and appends to the page body the dialog that will inform
	 * the user the Trash operation on a Series cannot be performed.
	 *
	 * @since 6.0.0
	 *
	 * @return {jQuery} The dialog Element.
	 */
	obj.dialogElement = () => {
		const $dialogElement = $( obj.selectors.dialogElement );

		if ( $dialogElement.length ) {
			return $dialogElement;
		}
		$( 'body' ).append( '<div id="tec-events-pro-series-trash-dialog"></div>' );

		// Formatting this way allows us to add more if needed with less git string thrash.
		const dialogCss = `
			<style>
			.ui-dialog[aria-describedby="tec-events-pro-series-trash-dialog"] {
				background:#f1f1f1;
			}

			.ui-dialog[aria-describedby="tec-events-pro-series-trash-dialog"] .ui-dialog-titlebar-close {
				top: 1em;
				padding-top:.5em
			}
			</style>
		`;

		$( 'head' ).append( dialogCss );

		return $( obj.selectors.dialogElement );
	};

	/**
	 * Finds the post ID of a clicked trash link in both the Admin List view context
	 * and in the Classic Editor context.
	 *
	 * @param {Node} node An HTML node to start the search from.
	 *
	 * @return {int} Either the post ID of the node belongs to, or `0` if it could not be found.
	 */
	obj.findPostId = ( node ) => {
		const postIDElement = document.getElementById( 'post_ID' );

		if ( null !== postIDElement ) {
			// Single context.
			return parseInt( postIDElement.value ) || 0;
		}

		const parent = $( node ).closest( '.level-0' );

		if ( parent.length === 0 ) {
			return 0;
		}

		const idAttribute = parent[ 0 ].getAttribute( 'id' );
		return idAttribute ? parseInt( idAttribute.replace( 'post-', '' ) ) : 0;
	};

	/**
	 * Returns a list of currently checked Bulk Action checkboxes from the list.
	 *
	 * @since 6.0.0
	 *
	 * @return {Element[]} A list of currently checked Bulk Action checkboxes from the list.
	 */
	obj.getCheckedListCheckboxes = () => {
		return Array.from( document.querySelectorAll( 'input[type="checkbox"][name="post[]"]:checked' ) );
	};

	/**
	 * Get the total number of Recurring Events associated with that particular series, looking for the <span></span> tag
	 * where this value is stored as `data-recurring-events-count`.
	 *
	 * @since 6.0.0
	 *
	 * @param {int} postId The ID of the series we are looking for.
	 *
	 * @return {int} The total number of Recurring Events related to the Series.
	 */
	obj.getTotalRelatedRecurringEvents = ( postId ) => {
		const totalElement = document.getElementById( `series-occurrences-count-${postId}` );

		if ( totalElement === null ) {
			return 0;
		}

		const related = parseInt( totalElement.getAttribute( 'data-recurring-events-count' ), 10 );

		return isNaN( related ) ? 0 : related;
	};

	/**
	 * Returns the current Bulk Action value, if any.
	 *
	 * @since 6.0.0
	 *
	 * @param {Event} e The Event triggered on the Bulk Action control.
	 *
	 * @return {string|null} Either the value of the currently selecte Bulk Action to apply, or `null`.
	 */
	obj.currentBulkAction = ( e ) => {
		const bulkActionSelect = $( e.target ).closest( '.bulkactions' ).find( '[name="action"]' ).first();

		if ( bulkActionSelect.length !== 1 ) {
			return null;
		}

		return bulkActionSelect[ 0 ].value;
	};

	/**
	 * Returns a clone of the anchor link to edit the post that will open the edit in a new tab.
	 *
	 * @since 6.0.0
	 *
	 * @param {int} postId The Series post ID to return the anchor Element for.
	 *
	 * @return {Node|null} Either a reference to the anchor Element clone, or `null`.
	 */
	obj.findPostEditLink = ( postId ) => {
		const anchorElement = document.querySelector( `#post-${postId} .title a.row-title` );

		if ( null === anchorElement ) {
			return null;
		}

		const clone = anchorElement.cloneNode( true );
		clone.setAttribute( 'target', '_blank' );

		return clone;
	};

	/**
	 * Returns a list of the currently selected Series post IDs.
	 *
	 * @since 6.0.0
	 *
	 * @param {int} minRelated The minimum number of related Recurring Events
	 *                         required for a post ID to be included.
	 *
	 * @return {int[]} The list of currently selected post IDs.jJ;w
	 */
	obj.getCheckedListPostIds = ( minRelated = 0 ) => {
		return obj.getCheckedListCheckboxes()
			.reduce( ( carry, checkboxElement ) => {
				const postId = obj.findPostId( checkboxElement );
				const relatedRecurringEvents = obj.getTotalRelatedRecurringEvents( postId );

				if ( relatedRecurringEvents >= minRelated ) {
					carry.push( postId );
				}

				return carry;
			}, [] );
	};

	/**
	 * Returns the HTML of the list of links required to edit the specified
	 * Series in new tabs.
	 *
	 * @since 6.0.0
	 *
	 * @param {int[]} postIds A list of the post IDs to build the list for.
	 *
	 * @return {string} The HTML required to edit the checked Series in new tabs.
	 */
	obj.getEditListHtml = ( postIds = [] ) => {
		let html = '<p><ul>';
		postIds.map( ( postId ) => {
			const postLink = obj.findPostEditLink( postId ).outerHTML;
			html += `<li>${postLink}</li>`;
		} );
		html += '</ul></p>';

		return html;
	};

	/**
	 * Builds and returns a closure that will display a dialog for the specified
	 * number of Series.
	 *
	 * @since 6.0.0
	 *
	 * @param {int} seriesCount The number of Series to display the dialog for.
	 * @param {string} action   The type of action to perform, either `trash` or `delete`.
	 *
	 * @return {(function(*): void)|*} The closure that will display the modal.
	 */
	obj.displayModal = ( seriesCount, action ) => {
		return ( event ) => {
			const legitActions = [ 'trash', 'delete' ];
			action = -1 === legitActions.indexOf( action ) ? 'trash' : action;
			const {tecEventsProSeriesTrashHandler = {}} = window;
			const {
				messages = {},
				dialog = {},
			} = tecEventsProSeriesTrashHandler;
			const message = seriesCount > 1 ?
				messages[ action ].plural
				: messages[ action ].singular;
			const $element = obj.dialogElement();
			let html = '<p>' + message + '</p>';

			if ( seriesCount > 1 ) {
				html += obj.getEditListHtml( obj.getCheckedListPostIds( 1 ) );
			}

			// Reset the dialog message.
			$element.html( html );

			event.preventDefault();
			event.stopPropagation();

			const settings = {
				autoOpen: true,
				modal: true,
				width: '400px',
				closeOnEscape: true,
				closeText: 'X', // Use an `X` to close the dialog.
				create: ( createEvent ) => {
					// On creation, change the `title` of the close control to have the desired tooltip.
					$( createEvent.target )
						.closest( '.ui-dialog' )
						.find( '.ui-dialog-titlebar-close' )
						.attr( 'title', dialog.closeButtonTooltip );
				},
				buttons: [
					{
						text: dialog.okButtonLabel,
						click: () => {
							$element.dialog( 'close' );
						}
					}
				]
			};

			$element.dialog( settings );
		};
	};

	/**
	 * Listens for clicks on the "Trash" or "Delete" buttons in the Single edit page or in
	 * admin List edit page.
	 *
	 * @since 6.0.0
	 *
	 * @return {void} The function wil prevent trashing and display the dialog, if required.
	 */
	obj.listenForTrashClicks = () => {
		// We'll show the same over and over: let's optimize a bit.
		const displayTrashModalFor1 = obj.displayModal( 1, 'trash' );
		const displayDeleteModalFor1 = obj.displayModal( 1, 'delete' );
		const trashLinksSelector = '.trash a.submitdelete, #delete-action a.submitdelete[href*="action=trash"]';
		const deleteLinksSelector = '.delete a.submitdelete, #delete-action a.submitdelete[href*="action=delete"]';

		Array.from( document.querySelectorAll( trashLinksSelector ) )
			.map( ( trashLink ) => {
				const postId = obj.findPostId( trashLink );

				if ( 0 === postId || obj.getTotalRelatedRecurringEvents( postId ) < 1 ) {
					return;
				}

				trashLink.addEventListener( 'click', displayTrashModalFor1 );
			} );

		Array.from( document.querySelectorAll( deleteLinksSelector ) )
			.map( ( deleteLink ) => {
				const postId = obj.findPostId( deleteLink );

				if ( 0 === postId || obj.getTotalRelatedRecurringEvents( postId ) < 1 ) {
					return;
				}

				deleteLink.addEventListener( 'click', displayDeleteModalFor1 );
			} );
	};

	/**
	 * Listens for clicks on the "Apply" button beside the bulk action select
	 * control to prevent trashing or deletion of Series that have Recurring
	 * Events related to them.
	 *
	 * @since 6.0.0
	 *
	 * @return {void} The function wil prevent trashing and display the dialog, if required.
	 */
	obj.listenForBulkTrashClicks = () => {
		const firstApplyButton = document.getElementById( 'doaction' );

		if ( null === firstApplyButton ) {
			// Not List, bail.
			return;
		}

		const secondApplyButton = document.getElementById( 'doaction2' );
		const clickHandler = ( event ) => {
			const action = obj.currentBulkAction( event );

			if ( 'trash' !== action && 'delete' !== action ) {
				return;
			}

			const allChecked = obj.getCheckedListCheckboxes();
			const checkedWithRecurringEvents = allChecked.filter( ( checkboxElement ) => {
				const postId = obj.findPostId( checkboxElement );

				return obj.getTotalRelatedRecurringEvents( postId );
			} );

			if ( checkedWithRecurringEvents.length <= 0 ) {
				return;
			}

			// Set the value to 2 to force the plural.
			obj.displayModal( 2, action )( event );
		};

		Array.from( [ firstApplyButton, secondApplyButton ] )
			.map( ( applyButton ) => {
				applyButton.addEventListener( 'click', clickHandler );
			} );
	};

	/**
	 * Hides the "Empty Trash" button on the Series screen.
	 *
	 * @since 6.0.0
	 *
	 * @return {void} The function will hide the "Empty Trash" button.
	 */
	obj.hideEmptyTrashButton = () => {
		const emptyTrashEl = document.getElementById( 'delete_all' )

		if ( ! ( emptyTrashEl instanceof Element ) ) {
			return
		}

		emptyTrashEl.style.display = 'none'
	};

	document.addEventListener( 'DOMContentLoaded', () => {
		obj.dialogElement();
		obj.listenForTrashClicks();
		obj.listenForBulkTrashClicks();
		obj.hideEmptyTrashButton();
	} );

} )( jQuery, tribe.events.pro.admin.series.trashHandler );
