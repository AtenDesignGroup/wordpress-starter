/**
 * Makes sure we have all the required levels on the TEC Object.
 *
 * @since 6.0.0
 *
 * @type {PlainObject}
 */
window.tec = window.tec || {};

/**
 * Configures Classic Editor Events Object in the Global TEC variable.
 *
 * @since 6.0.0
 *
 * @type {PlainObject}
 */
tec.classicEditorEvents = tec.classicEditorEvents || {};

/**
 * Handles the initial loading of the recurrence and exclusions.
 * Also handles syncing of the state when adding recurrence rules or exclusions,
 * as well as when the datetime and recurrence and exclusion rules change.
 *
 * @since 6.0.0
 *
 * @param {jQuery} $ A reference to the global jQuery instance.
 * @param {PlainObject} obj tec.classicEditorEvents object.
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
		postForm: 'form#post',
		recurrenceActive: '#tribe-recurrence-active',
		addExclusion: '#tribe-add-exclusion',
		addRecurrence: '#tribe-add-recurrence',
		eventTable: '#EventInfo .eventtable, .tribe-section-datetime.eventForm',
		eventForm: '.eventForm',
		eventStartDate: '#EventStartDate',
		eventEndDate: '#EventEndDate',
		eventStartTime: '#EventStartTime',
		eventEndTime: '#EventEndTime',
		allDayCheckbox: '#allDayCheckbox',
		saveButton: 'input[name="save"]',
		publishButton: 'input[name="publish"]',
		communityEventsSubmit: '.events-community-submit',
		deleteButton: '#delete-action a.submitdelete',
		recurrence: '.tribe-event-recurrence',
		exclusion: '.tribe-event-exclusion',
		recurrenceDescription: '.tribe-recurrence-description',
		recurrenceDescriptionInput: '[name="recurrence[description]"]',
		recurrenceTypeButton: '.tribe-event-recurrence-rule > .tribe-buttonset .tribe-button-field[data-value]',
	};


	/* ################################################################################
	 *
	 * GLOBAL EVENT HANDLERS
	 *
	 * ################################################################################ */


	/**
	 * Handles changing default values to the recurrence rows that require it.
	 *
	 * @since 6.0.0
	 *
	 * @return {void} The function will apply different new default values to
	 *     rows that require it.
	 */
	obj.setNewRecurrenceDefaults = function () {
		// requestAnimationFrame is required here so the updates happen after DOM modifications.
		// The order of the actions below matter, be careful when modifying them.
		requestAnimationFrame( function () {
			const targetRows = document.querySelectorAll( obj.selectors.recurrence + ':not([data-defaults-set])' );
			obj.default.setRuleEndsText( targetRows );
			obj.default.setNewEndsOnDefault( targetRows );
			obj.default.setNewEndDateDefault( targetRows );
			obj.default.setNewEndCountDefault( targetRows );
			obj.default.setNewOnceTimeSelectDefault( targetRows );
			obj.default.setNewRuleType( targetRows );
			obj.default.addSameTimeRowClass( targetRows );
			obj.offStart.initNewRules( targetRows );
			obj.default.bindRuleEvents( targetRows );
			obj.dayOfMonth.bindRuleEvents( targetRows );
			obj.default.setDefaultsSetFlag( targetRows );
		} );
	};

	/**
	 * Handles changing default values to the exclusion rows that require it.
	 *
	 * @since 6.0.0
	 *
	 * @return {void} The function will apply different new default values to
	 *     rows that require it.
	 */
	obj.setNewExclusionDefaults = function () {

		// requestAnimationFrame is required here so the updates happen after DOM modifications.
		// The order of the actions below matter, be careful when modifying them.
		requestAnimationFrame( function () {
			const targetRows = document.querySelectorAll( obj.selectors.exclusion + ':not([data-defaults-set])' );
			obj.default.hideExclusionDescription( targetRows );
			obj.default.setExclusionDatepickerMinDates( targetRows );
			obj.default.setNewEndsOnDefault( targetRows );
			obj.default.setRuleEndsText( targetRows );
			obj.default.setNewEndCountDefault( targetRows );
			obj.default.setNewRuleType( targetRows );
			obj.offStart.initNewRules( targetRows );
			obj.default.bindRuleEvents( targetRows );
			obj.dayOfMonth.bindRuleEvents( targetRows );
			obj.default.setDefaultsSetFlag( targetRows );
		} );
	};

	/**
	 * Sets the datepicker min dates for all exclusions.
	 *
	 * @since 6.0.0
	 *
	 * @return {void}
	 */
	obj.setAllExclusionDatepickerMinDates = function () {
		// requestAnimationFrame is required here so the updates happen after DOM modifications.
		requestAnimationFrame( function () {
			const targetRows = document.querySelectorAll( obj.selectors.exclusion );
			obj.default.setExclusionDatepickerMinDates( targetRows );
		} );
	};

	/**
	 * Initializes existing Recurrence and Exclusion rows.
	 * This step hides the Exclusion description and applies a flag attribute
	 * to the existing Recurrence and Exclusion rows.
	 *
	 * @since 6.0.0
	 *
	 * @return {void}
	 */
	obj.initExistingRecurrenceExclusionRows = function () {
		tec.classicEditorEvents.eventDate.setCurrentEventDate();

		const recurrenceRows = document.querySelectorAll( obj.selectors.recurrence + ':not([data-defaults-set])' );
		const exclusionRows = document.querySelectorAll( obj.selectors.exclusion + ':not([data-defaults-set])' );
		obj.default.hideExclusionDescription( exclusionRows );
		obj.default.setRuleEndsText( recurrenceRows );
		obj.default.setRuleEndsText( exclusionRows );
		obj.default.setExclusionDatepickerMinDates( exclusionRows );
		obj.default.addSameTimeRowClass( recurrenceRows );
		obj.offStart.initOffStartRules( recurrenceRows );
		obj.default.setRuleTypeDropdownToMatchButtons( recurrenceRows );
		obj.default.setRuleTypeDropdownToMatchButtons( exclusionRows );
		obj.default.bindRuleEvents( recurrenceRows );
		obj.default.bindRuleEvents( exclusionRows );
		obj.default.setDefaultsSetFlag( recurrenceRows );
		obj.default.setDefaultsSetFlag( exclusionRows );
		obj.dayOfMonth.bindRuleEvents( recurrenceRows );
		obj.dayOfMonth.bindRuleEvents( exclusionRows );
		obj.sync.syncRecurrenceState();

		tec.classicEditorEvents.eventDate.setPreviousEventDate();
	};

	/**
	 * Hides the recurrence description section and sets the description input to
	 * a blank string.
	 *
	 * @todo This function should be converted to template modifications when
	 *     integrated into ECP.
	 *
	 * @since 6.0.0
	 *
	 * @return {void} The function will hide the description section.
	 */
	obj.hideRecurrenceDescription = function () {
		const deprecatedDescriptions = document.querySelectorAll( obj.selectors.recurrenceDescription );

		deprecatedDescriptions.forEach( function ( description ) {
			description.style.display = 'none';
			const descriptionInput = description.querySelector( obj.selectors.recurrenceDescriptionInput );
			if ( ! descriptionInput ) {
				return;
			}
			descriptionInput.value = '';
		} );
	};

	/**
	 * Handle mutations observed by mutation observer.
	 *
	 * @since 6.0.0
	 *
	 * @param {array<MutationRecord>} mutations A set of Mutation Records
	 *     detected by the Mutation Observer.
	 * @param {MutationObserver} observer A reference to the Mutation Observer
	 *     instance that is observing the changes.
	 *
	 * @return {void}
	 */
	obj.handleMutations = function ( mutations, observer ) {
		const sync = mutations.reduce( function ( carry, mutation ) {
			// If syncing already, return early.
			if ( carry ) {
				return carry;
			}

			if ( mutation.type === 'attributes' ) {
				if (
					mutation.attributeName === 'class' &&
					(
						mutation.target.classList.contains( 'tribe-event-recurrence' ) ||
						mutation.target.classList.contains( 'tribe-event-exclusion' )
					)
				) {
					return true;
				} else {
					return false;
				}
			}

			return true;
		}, false );

		// If sync is false, return early.
		if ( ! sync ) {
			return;
		}

		// requestAnimationFrame is required here so the updates happen after DOM modifications.
		requestAnimationFrame( function () {
			tec.classicEditorEvents.eventDate.setCurrentEventDate();
			obj.sync.syncRecurrenceState();
			tec.classicEditorEvents.eventDate.setPreviousEventDate();
		} );
	};

	/**
	 * Sets up the mutation observer to watch the DOM for any DOM manipulations.
	 * Syncs the recurrence state when any DOM manipulations occur.
	 *
	 * @since 6.0.0
	 *
	 * @return {void} The function does not return any value.
	 */
	obj.setupMutationObserver = function () {
		const eventInfo = document.querySelector( obj.selectors.eventTable );

		if ( ! eventInfo ) {
			return;
		}

		const observer = new MutationObserver( obj.handleMutations );
		observer.observe( eventInfo, {
			subtree: true,
			childList: true,
			attributes: true,
		} );
	};

	/**
	 * Binds event handlers to event listeners.
	 *
	 * @since 6.0.0
	 *
	 * @return {void}
	 */
	obj.bindEvents = function () {
		$( [
			obj.selectors.recurrenceActive,
			obj.selectors.addRecurrence,
		].join() )
			.on( 'click', obj.setNewRecurrenceDefaults );
		$( obj.selectors.addExclusion )
			.on( 'click', obj.setNewExclusionDefaults );
		$( obj.selectors.eventForm )
			.on(
				'change',
				[
					obj.selectors.eventStartDate,
					obj.selectors.eventEndDate,
					obj.selectors.eventStartTime,
					obj.selectors.eventEndTime,
					obj.selectors.allDayCheckbox,
				].join(),
				obj.setAllExclusionDatepickerMinDates
			);
		// On submission, we need to enable some fields so the data is persisted.
		$( obj.selectors.postForm )
			.on( 'submit', obj.offStart.enableOffStartFieldsForSubmission )
	};

	/**
	 * New update rule recurrence text function to replace the old function.
	 * This replaces
	 * tribe_events_pro_admin.recurrence.update_rule_recurrence_text.
	 *
	 * @since 6.0.0
	 * @since 6.0.8 Now parsing start_time here instead of via the legacy text parser.
	 *
	 * @param {jQuery} $rule The recurrence or exclusion rule.
	 *
	 * @return {void}
	 */
	obj.updateRuleRecurrenceText = function ( $rule ) {
		obj.classicUpdateRuleRecurrenceText( $rule );

		let startMoment;
		if (
			Array.isArray( tecEventDetails.occurrence ) ||
			! tecEventDetails.occurrence.has_recurrence ||
			tecEventDetails.occurrence.isFirst
		) {
			/**
			 * If tecEventDetails.occurrence is array, this is a new event.
			 * If tecEventDetails.occurrence.has_recurrence is false, this is a
			 * single event. If tecEventDetails.occurrence.has_recurrence is true and
			 * tecEventDetails.occurrence.isFirst is true, this is a recurring event
			 * and we are editing the first occurrence. For the above 3 scenarios,
			 * replace [first_occurrence_date] with start date from the edit event
			 * screen.
			 */
			const dateFormat = tribe_events_pro_admin.recurrence.date_format + ' hh:mm a';
			const $startDate = $document.find( obj.selectors.eventStartDate );
			const $startTime = $document.find( obj.selectors.eventStartTime );
			const startDate = $startDate.val() + ' ' + $startTime.val().toUpperCase();
			startMoment = moment( startDate, dateFormat );

		} else {
			/**
			 * Otherwise, use the event start date passed in tecEventDetails to
			 * replace
			 * [first_occurrence_date].
			 */
			startMoment = moment( tecEventDetails.event.start_date );
		}

		const displayFormat = tribe_events_pro_admin.recurrence.convert_date_format_php_to_moment( tribe_dynamic_help_text.date_with_year );
		const $recurrenceDescription = $rule.find( '.tribe-event-recurrence-description' );
		const text = $recurrenceDescription.text();
		let updatedText = text.replace( '[first_occurrence_date]', startMoment.format( displayFormat ) );
		/**
		 * We need to apply our start time here, now that we support RDATE editing with new logic, the start_time
		 * needs to pull this occurrences' time appropriately, legacy is not retrieving the correct time in edge cases.
		 */
		updatedText = updatedText.replace( '[first_occurrence_start_time]', startMoment.format( 'h:mma' ).toUpperCase() );
		$recurrenceDescription.html( updatedText );
	};

	/**
	 * Set up the recurrence text modification.
	 *
	 * @since 6.0.0
	 *
	 * @return {void}
	 */
	obj.setupRecurrenceTextModification = function () {
		/**
		 * Replace tribe_events_pro_admin.recurrence.update_rule_recurrence_text
		 * with obj.updateRuleRecurrenceText.
		 */
		obj.classicUpdateRuleRecurrenceText = tribe_events_pro_admin.recurrence.update_rule_recurrence_text;
		tribe_events_pro_admin.recurrence.update_rule_recurrence_text = obj.updateRuleRecurrenceText;

		// Add obj.date_format and obj.convert_date_format_php_to_moment to this object.
		obj.date_format = tribe_events_pro_admin.recurrence.date_format;
		obj.convert_date_format_php_to_moment = tribe_events_pro_admin.recurrence.convert_date_format_php_to_moment;
	};


	/* ################################################################################
	 *
	 * INITIALIZE
	 *
	 * ################################################################################ */


	/**
	 * Initializes the classic editor events JS.
	 *
	 * @since 6.0.0
	 *
	 * @return {void}
	 */
	obj.ready = function () {
		// Fire an Event before the ready code runs.
		const beforeReadyEvent = new Event( 'TECClassicEditorEventsBeforeReady' );
		beforeReadyEvent.object = obj;
		document.dispatchEvent( beforeReadyEvent );

		$document.on( 'setup.dependency', function () {
			obj.initExistingRecurrenceExclusionRows();
			obj.hideRecurrenceDescription();
			obj.setupMutationObserver();
			obj.bindEvents();

			// Fire an Event after the ready code did run.
			const readyEvent = new Event( 'TECClassicEditorEventsReady' );
			readyEvent.object = obj;
			document.dispatchEvent( readyEvent );
		} );
		obj.setupRecurrenceTextModification();

	};

	$( obj.ready );
} )( jQuery, tec.classicEditorEvents );
