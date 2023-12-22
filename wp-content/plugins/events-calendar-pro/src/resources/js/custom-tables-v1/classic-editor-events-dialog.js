/**
 * Makes sure we have all the required levels on the TEC Object
 *
 * @since 6.0.0
 *
 * @type {Object}
 */

window.tec = window.tec || {};

/**
 * Configures Classic Editor Events Dialog Object in the Global TEC variable
 *
 * @since 6.0.0
 *
 * @type {Object}
 */
tec.classicEditorEventsDialog = tec.classicEditorEventsDialog || {};

/**
 * Adds a dialog confirmation to the Save, Publish and Update buttons to prompt the user about the
 * type of Update to perform on the Event.
 *
 * The code does not check if it's loaded in the correct context, an existing event and not a new one,
 * as that should be handled by the PHP side of the implementation.
 *
 * @since 6.0.0
 *
 * @param {jQuery} $ A reference to the global jQuery instance.
 * @param {Object} obj tec.classicEditorEventsDialog object.
 */
( function ( $, obj ) {
	"use-strict";

	/**
	 * Selectors used for configuration and setup.
	 *
	 * @since 6.0.0
	 *
	 * @type {Object}
	 */
	obj.selectors = {
		saveButton: 'input[name="save"]',
		publishButton: 'input[name="publish"]',
		communityEventsSubmit: '.events-community-submit',
		deleteButton: '#delete-action a.submitdelete',
	};

	/**
	 * Localize variable from PHP.
	 *
	 * @since 6.0.0
	 *
	 * @type {Object}
	 */
	obj.l10n = window.tecEventsSeriesClassicEditor;

	/**
	 * Update types to pass to the BE.
	 *
	 * @since 6.0.0
	 *
	 * @type {Object}
	 */
	obj.updateType = {
		all: 'all',
		single: 'single',
		upcoming: 'upcoming',
	};

	/**
	 * Map of date formats from PHP to Moment.
	 *
	 * @since 6.0.0
	 *
	 * @type {Object}
	 */
	obj.phpToMomentDateFormatMap = {
		d: 'DD',
		j: 'D',
		m: 'MM',
		n: 'M',
		Y: 'YYYY',
	};

	/**
	 * Clears the HTML content within the dialog element.
	 *
	 * @since 6.0.0
	 *
	 * @return {void}
	 */
	obj.clearDialogContent = function () {
		tec.editorDialog.el.$instance.empty();
	};

	/**
	 * Get radio input HTML for the dialog element.
	 *
	 * @since 6.0.0
	 *
	 * @param {Object} settings The settings object for dialog text.
	 *                               Possible parameters include:
	 *                               {
	 *                                 type: string, type of dialog content, should be "text",
	 *                                 text: string, the text content,
	 *                               }
	 *
	 * @return {string} The HTML code of the text to append to the dialog content.
	 */
	obj.getDialogText = function ( settings ) {
		// @todo: move this to a template.
		return '<div class="tec-events-pro-dialog__text-row">' +
		'<p class="tec-events-pro-dialog__text">' +
		settings.text +
		'</p>' +
		'</div>';
	};

	/**
	 * Get radio input HTML for the dialog element.
	 *
	 * @since 6.0.0
	 *
	 * @param {PlainObject} settings The settings object for dialog radio input.
	 *                               Possible parameters include:
	 *                               {
	 *                                 type: string, type of radio input ('single', 'upcoming', 'all'),
	 *                                 label: string, the label of the radio input,
	 *                                 classes: array, classes to be added to the input row,
	 *                                 inputClasses: array, classes to be added to the radio input,
	 *                                 labelClasses: array, classes to be added to the label,
	 *                                 checked: boolean, whether the radio input is checked or not,
	 *                               }
	 *
	 * @return {string} The HTML code of the radio input to append to the dialog content.
	 */
	obj.getDialogRadioInput = function ( settings ) {
		const checked = settings.checked ? 'checked' : '';
		const classes = [ 'tec-events-pro-dialog__input-row', 'tec-events-pro-dialog__input-row--' + settings.type ]
			.concat( settings.classes )
			.join( ' ' );
		const inputClasses = [ 'tec-events-pro-dialog__radio-input' ]
			.concat( settings.inputClasses )
			.join( ' ' );
		const labelClasses = [ 'tec-events-pro-dialog__radio-input-label' ]
			.concat( settings.labelClasses )
			.join( ' ' );
		const id = 'tec-events-pro-dialog__radio-input--' + settings.type;

		 let html = '<div class="' + classes + '">' +
			'<input type="radio" name="_tec_update_type" value="' + settings.type + '" class="' + inputClasses + '" id="' + id + '" ' + checked + ' />' +
			'<label class="' + labelClasses + '" for="' + id + '">' +
			settings.label;

		 if ( settings.labelHelpText ) {
			html += '<div><em style="font-style: italic; color: gray; font-size: 90%">'+settings.labelHelpText+'</em></div>';
		 }

		 html += '</label></div>';

		 return html;
	};

	/**
	 * Sets the HTML content within the dialog element.
	 *
	 * @since 6.0.0
	 *
	 * @param {array} contentSettings Array of content settings to include in the dialog content.
	 *
	 * @return {void}
	 */
	obj.setDialogContent = function ( contentSettings ) {
		contentSettings.forEach( function ( settings ) {
			if ( 'text' === settings.type ) {
				tec.editorDialog.el.$instance.append( obj.getDialogText( settings ) );
			} else {
				tec.editorDialog.el.$instance.append( obj.getDialogRadioInput( settings ) );
			}
		} );
	};

	/**
	 * Get the "This Event" label with the start datetime appended to the end.
	 *
	 * @since 6.0.0
	 *
	 * @return {string}
	 */
	obj.getDialogThisEventLabel = function () {
		const labelPieces = [ obj.l10n.thisEvent ];

		// Get datepicker format, convert it to moment date format.
		const datepickerFormat = tecEventSettings.datepickerFormat;
		const momentDateFormat = Object
			.keys( obj.phpToMomentDateFormatMap )
			.reduce( function ( format, phpFormat ) {
				return format.replace( phpFormat, obj.phpToMomentDateFormatMap[ phpFormat ] );
			}, datepickerFormat );

		// Get start date and all day.
		let startDateTime = $( tec.classicEditorEvents.selectors.eventStartDate ).val();
		let startDateTimeMomentFormat = momentDateFormat;
		const isAllDay = $( tec.classicEditorEvents.selectors.allDayCheckbox ).is( ':checked' );

		// If not all day, get start time and add start time format.
		if ( ! isAllDay ) {
			startDateTime += ' ' + $( tec.classicEditorEvents.selectors.eventStartTime ).val();
			startDateTimeMomentFormat += ' H:mma';
		}

		const startDateTimeMoment = moment( startDateTime, startDateTimeMomentFormat );

		// If moment is not valid, return label without start date time.
		if ( ! startDateTimeMoment.isValid() ) {
			return labelPieces[0];
		}

		// Get date string from start date time moment, build start date time string.
		let dateTimeString = startDateTimeMoment.format( 'MMMM D, YYYY' );
		dateTimeString += isAllDay ? ` ${obj.l10n.allDay}` : tecEventSettings.dateTimeSeparator + startDateTimeMoment.format( 'H:mma' );
		dateTimeString = '(' + dateTimeString + ')';
		labelPieces.push( dateTimeString );

		return labelPieces.join( ' ' );
	};

	/**
	 * Gets the update dialog content settings based on state changes.
	 *
	 * @since 6.0.0
	 *
	 * @return {array} The dialog content settings.
	 */
	obj.getUpdateDialogContentSettings = function () {
		const settings = [];

		const startDateChanged = tec.classicEditorEventsState.isStateKeyDirty( 'startDate' );
		const recurrenceRuleChanged = tec.classicEditorEventsState.isOneOfStateKeysDirty( [ 'recurrence', 'exclusion' ] );

		// Initially allow all types of updates.
		const updateOptions = {
			single: true,
			upcoming: true,
			all: true,
		};

		if( startDateChanged && ( !tecEventDetails || !tecEventDetails.isRdate ) ) {
			updateOptions.all = false;
		}

		if( recurrenceRuleChanged ){
			updateOptions.single = false;
		}

		// If UPCOMING is the only one allowed, then use some different wording.
		if ( ! updateOptions.all && ! updateOptions.single ) {
			settings.push( {
				type: 'text',
				text: obj.l10n.effectThisAndFollowingEventsWarning,
			} );
			settings.push( {
				type: obj.updateType.upcoming,
				label: obj.l10n.upcomingSetting,
				classes: [ 'hidden' ],
				inputClasses: [],
				labelClasses: [],
				checked: true,
			} );

			return settings;
		}

		if ( updateOptions.single ) {
			settings.push( {
				type: obj.updateType.single,
				label: obj.getDialogThisEventLabel(),
				labelHelpText: obj.l10n.thisEventHelpText,
				classes: [],
				inputClasses: [],
				labelClasses: [],
				checked: false,
			} );
		}

		// The UPCOMING update option will always be available.
		settings.push( {
			type: obj.updateType.upcoming,
			label: obj.l10n.upcomingSetting,
			classes: [],
			inputClasses: [],
			labelClasses: [],
			checked: ! updateOptions.all, // Check only if update all choice is not shown.
		} );

		// If start date did not change but start time changed, then show "All Events" radio button.
		if ( updateOptions.all ) {
			settings.push( {
				type: obj.updateType.all,
				label: obj.l10n.allEvents,
				classes: [],
				inputClasses: [],
				labelClasses: [],
				checked: true,
			} );
		}

		return settings;
	};

	/**
	 * Gets the delete dialog content settings.
	 *
	 * @since 6.0.0
	 *
	 * @return {array} The dialog content settings.
	 */
	obj.getDeleteDialogContentSettings = function () {
		const settings = [];

		settings.push( {
			type: obj.updateType.single,
			label: obj.getDialogThisEventLabel(),
			classes: [],
			inputClasses: [],
			labelClasses: [],
			checked: false,
		} );
		settings.push( {
			type: obj.updateType.upcoming,
			label: obj.l10n.upcomingSetting,
			classes: [],
			inputClasses: [],
			labelClasses: [],
			checked: false,
		} );
		settings.push( {
			type: obj.updateType.all,
			label: obj.l10n.allEvents,
			classes: [],
			inputClasses: [],
			labelClasses: [],
			checked: true,
		} );

		return settings;
	};

	/**
	 * Handles the confirm button click event for the dialog. Returns a closure to handle the click event.
	 *
	 * @since 6.0.0
	 *
	 * @param {jQuery}   $button      The button that was clicked to open the dialog. This could be the
	 *                                update or delete button.
	 * @param {Function} eventHandler The event handler attached to the `$button` (update or delete button).
	 * @param {string}   typeName     The name of the hidden input to pass data to the BE.
	 *
	 * @return {Function} The closure to handle the click event.
	 */
	obj.handleDialogConfirmButtonClick = function ( $button, eventHandler, typeName ) {
		return function () {
			// Somehow we got here without clicking an update or delete button, close dialog and return early.
			if ( ! $button || ! $button.length ) {
				tec.editorDialog.closeDialog();
				return;
			}

			// Get the selected option from the dialog.
			const selectedOption = tec.editorDialog.el.$instance.find( 'input:checked' ).val();

			// If value is empty, then close dialog and return.
			if ( ! selectedOption ) {
				tec.editorDialog.closeDialog();
				return;
			}

			if ( 'trash' !== typeName ) {
				// Attach hidden field for type of update or deletion.
				$button.closest( 'form' ).append( '<input type="hidden" name="' + typeName + '" value="' + selectedOption + '">' );
			} else {
				// Set the href for the trash link.
				const oldHref = $button.attr( 'href' );
				$button.attr( 'href', oldHref + '&_tec_update_type=' + selectedOption );
			}

			// Remove listeners to avoid infinite loop.
			$button.off( 'click', eventHandler );

			// Respect original user click.
			$button[0].click();
		};
	};

	/**
	 * Get the dialog settings for updating a recurring event.
	 *
	 * @since 6.0.0
	 *
	 * @param {jQuery} $updateButton jQuery object of the update button that was clicked to open the dialog.
	 *
	 * @return {PlainObject} The dialog settings for updating a recurring event.
	 */
	obj.getUpdateDialogSettings = function ( $updateButton ) {
		return Object.assign(
			{},
			tec.editorDialog.defaultDialogSettings,
			{
				buttons: [
					{
						class: 'button-primary',
						text: obj.l10n.okButton,
						click: obj.handleDialogConfirmButtonClick( $updateButton, obj.showUpdatePrompt, '_tec_update_type' ),
					},
				],
			},
		);
	};

	/**
	 * Get the dialog settings for deleting a recurring event.
	 *
	 * @since 6.0.0
	 *
	 * @param {jQuery} $deleteButton jQuery object of the delete button that was clicked to open the dialog.
	 *
	 * @return {PlainObject} The dialog settings for deleting a recurring event.
	 */
	obj.getDeleteDialogSettings = function ( $deleteButton ) {
		return Object.assign(
			{},
			tec.editorDialog.defaultDialogSettings,
			{
				buttons: [
					{
						class: 'button-primary',
						text: obj.l10n.okButton,
						click: obj.handleDialogConfirmButtonClick( $deleteButton, obj.showDeletePrompt, 'trash' ),
					},
				],
			},
		);
	};

	/**
	 * Handles saving when saving a duplicated event for the first time.
	 *
	 * @since 6.0.0
	 *
	 * @param {Event} event The click event object.
	 *
	 * @return {void}
	 */
	obj.handleDuplicatedEventSave = function ( event ) {
		event.preventDefault();

		// Attach hidden field for type of update or deletion.
		const $saveButton = $( event.currentTarget );
		$saveButton.closest( 'form' ).append( '<input type="hidden" name="_tec_update_type" value="' + obj.updateType.all + '">' );

		// Remove listeners to avoid infinite loop.
		$saveButton.off( 'click', obj.showUpdatePrompt );

		// Respect original user click.
		$saveButton.click();
	};

	/**
	 * Shows the Update dialog to the user when trying to save.
	 *
	 * @since 6.0.0
	 *
	 * @param {Event} event The click event object.
	 *
	 * @return {void}
	 */
	obj.showUpdatePrompt = function ( event ) {
		// Do not show the update prompt if event in not represented in the custom tables yet.
		if ( tecEventDetails.requiresFirstSave ) {
			obj.handleDuplicatedEventSave( event );
			return;
		}

		// If event is not recurring, return early.
		if ( ! tec.classicEditorEventsState.initialState.isRecurring ) {
			return;
		}

		event.preventDefault();

		tec.editorDialog.el.$instance.attr( 'title', obj.l10n.editModalTitle );
		obj.clearDialogContent();
		obj.setDialogContent( obj.getUpdateDialogContentSettings() );
		tec.editorDialog.setDialogSettings( obj.getUpdateDialogSettings( $( event.currentTarget ) ) );
		tec.editorDialog.openDialog();
	};

	/**
	 * Shows the user a prompt to choose how the delete/trash action should be applied.
	 *
	 * @since 6.0.0
	 *
	 * @param {Event} event The click event object.
	 *
	 * @return {void}
	 */
	obj.showDeletePrompt = function ( event ) {
		if ( ! tec.classicEditorEventsState.initialState.isRecurring ) {
			return;
		}

		event.preventDefault();

		tec.editorDialog.el.$instance.attr( 'title', obj.l10n.trashRecurringEvent );
		obj.clearDialogContent();
		obj.setDialogContent( obj.getDeleteDialogContentSettings() );
		tec.editorDialog.setDialogSettings( obj.getDeleteDialogSettings( $( event.currentTarget ) ) );
		tec.editorDialog.openDialog();
	};

	/**
	 * Binds event handlers to event listeners.
	 *
	 * @since 6.0.0
	 *
	 * @return {void}
	 */
	obj.bindEvents = function () {
		const showUpdatePrompt = !tecEventDetails || !tecEventDetails.occurrence || tecEventDetails.occurrence.has_recurrence;
		$( [
			obj.selectors.saveButton,
			// @todo: The community event submit doesn't differentiate between "submit" and "update", need to update in php.
			obj.selectors.communityEventsSubmit,
		].join() )
			.on( 'click', obj.showUpdatePrompt );

		if ( showUpdatePrompt ) {
			$( obj.selectors.publishButton )
				.on( 'click', obj.showUpdatePrompt );
		}

		$( obj.selectors.deleteButton )
			.on( 'click', obj.showDeletePrompt );
	};

	/**
	 * Initializes the classic editor events JS.
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
