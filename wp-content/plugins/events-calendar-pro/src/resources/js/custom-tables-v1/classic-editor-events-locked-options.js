/**
 * Makes sure we have all the required levels on the TEC Object.
 *
 * @since 6.0.0
 *
 * @type {PlainObject}
 */
window.tec = window.tec || {};

/**
 * Configures Classic Editor Events Locked Options Object in the Global TEC
 * variable.
 *
 * @since 6.0.0
 *
 * @type {PlainObject}
 */
tec.classicEditorEvents = tec.classicEditorEvents || {};
tec.classicEditorEvents.lockedOptions = tec.classicEditorEvents.lockedOptions || {};

/**
 * Handles the locked options cases of recurrence and exclusions.
 *
 * @since 6.0.0
 *
 * @param {jQuery} $ A reference to the global jQuery instance.
 * @param {PlainObject} obj tec.classicEditorEvents.lockedOptions object.
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
		eventStartDate: '#EventStartDate',
		recurrenceWeekDay: '.custom-recurrence-weeks .tribe-button-field',
		recurrenceWeekDayInput: '.custom-recurrence-weeks .tribe-button-field .tribe-button-input',
		recurrenceYearsDropdown: '.custom-recurrence-years',
		weekDaysLock: '.tec-events-pro-week-days-lock',
		weekDaysOverlay: '.tec-events-pro-week-days-overlay',
	};

	/**
	 * Classes used for configuration and setup.
	 *
	 * @since 6.0.0
	 *
	 * @type {PlainObject}
	 */
	obj.classes = {
		weekDayActive: 'tribe-active',
		weekDayLocked: 'tribe-button-field--locked',
	};

	/**
	 * Set week day active state based on the locked state.
	 *
	 * @since 6.0.0
	 *
	 * @param {HTMLElement} row The row to lock week day for.
	 *
	 * @return {void}
	 */
	obj.setWeekDayActiveFromLockedState = function ( row ) {
		const lockedWeekDay = row.getAttribute( 'data-locked-week-day' );

		// No locked week day, return early.
		if ( ! lockedWeekDay ) {
			return;
		}

		const lockedDayInput = row.querySelector( obj.selectors.recurrenceWeekDayInput + '[value="' + lockedWeekDay + '"]' );
		const lockedDay = lockedDayInput.parentNode;

		const hasLockClass = lockedDay.classList.contains( obj.classes.weekDayLocked );
		const hasActiveClass = lockedDay.classList.contains( obj.classes.weekDayActive );

		// If already active/inactive based on lock state, return early.
		if ( ( hasLockClass && hasActiveClass ) || ( ! hasLockClass && ! hasActiveClass ) ) {
			return;
		}

		lockedDay.click();
	};

	/**
	 * Set week day lock state based on `lock` argument.
	 *
	 * @since 6.0.0
	 *
	 * @param {HTMLElement} day  The day to set as locked or not.
	 * @param {boolean}     lock Whether the day should be locked or not.
	 *
	 * @return {void}
	 */
	obj.setWeekDayLockState = function ( day, lock ) {
		const hasLockClass = day.classList.contains( obj.classes.weekDayLocked );

		// If already in lock/unlocked state, return early.
		if ( ( hasLockClass && lock ) || ( ! hasLockClass && ! lock ) ) {
			return;
		}

		const hasActiveClass = day.classList.contains( obj.classes.weekDayActive );

		if ( lock && ! hasLockClass ) {
			// If we want to lock, add locked class.
			day.classList.add( obj.classes.weekDayLocked );

			// If not active, click to make active.
			if ( ! hasActiveClass ) {
				day.click();
			}
		} else if ( ! lock && hasLockClass ) {
			// If we want to unlock, remove locked class.
			day.classList.remove( obj.classes.weekDayLocked );

			// If active, click to not make active.
			if ( hasActiveClass ) {
				day.click();
			}
		}
	};

	/**
	 * Set locked week day based on start date.
	 *
	 * @since 6.0.0
	 *
	 * @param {HTMLElement} row The row to lock week day for.
	 *
	 * @return {void}
	 */
	obj.setLockedWeekDay = function ( row ) {
		const overlay = row.querySelector( obj.selectors.weekDaysOverlay );

		// Don't need this logic for RDATE occurrences. Keep the default settings.
		if ( tecEventDetails?.isRdate ) {
			return;
		}

		// If rule is off-start, remove styles, remove locked week day, and return early.
		if ( tec.classicEditorEvents.offStart.isRuleOffStart( row ) ) {
			row
				.querySelectorAll( obj.selectors.recurrenceWeekDayInput )
				.forEach( function ( dayInput ) {
					obj.setWeekDayLockState( dayInput.parentNode, false );
				} );

			row.removeAttribute( 'data-locked-week-day' );
			overlay.removeAttribute( 'style' );
			return;
		}

		const lockedWeekDay = row.getAttribute( 'data-locked-week-day' );
		const startWeekDay = tec.classicEditorEvents.eventDate.eventStart.moment.isoWeekday();

		// If week day is already locked, return.
		if ( lockedWeekDay === startWeekDay ) {
			return;
		}

		// Get locked day position and width.
		const lockedDayInput = row.querySelector( obj.selectors.recurrenceWeekDayInput + '[value="' + startWeekDay + '"]' );
		const lockedDay = lockedDayInput.parentNode;
		const lockedDayLeft = lockedDay.offsetLeft;
		const lockedDayWidth = lockedDay.offsetWidth;

		// Position the lock only when the element is rendering, skip when hidden..
		if ( lockedDayLeft && lockedDayWidth ) {
			// Set overlay styles to cover locked week day.
			overlay.style.left = lockedDayLeft + 'px';
			overlay.style.width = lockedDayWidth + 'px';

			// Set lock icon position.
			const lockIcon = row.querySelector( obj.selectors.weekDaysLock );
			const lockIconLeft = lockedDayLeft + lockedDayWidth - 14;
			lockIcon.style.left = lockIconLeft + 'px';
		}

		// Set week days to locked/unlocked state.
		row.querySelectorAll( obj.selectors.recurrenceWeekDayInput ).
				forEach( function( dayInput ) {
					const lock = dayInput.getAttribute( 'value' ) ===
							String( startWeekDay );
					obj.setWeekDayLockState( dayInput.parentNode, lock );
				} );
		row.setAttribute( 'data-locked-week-day', startWeekDay );
	};

	/**
	 * Hide inline text for month rule if not off-start.
	 *
	 * @since 6.0.0
	 *
	 * @param {HTMLElement} row The row to hide inline text for.
	 *
	 * @return {void}
	 */
	obj.hideMonthInlineText = function ( row ) {
		// If rule is off-start, return early.
		if ( tec.classicEditorEvents.offStart.isRuleOffStart( row ) ) {
			return;
		}

		// Hide all inline texts we want to hide in recurrence months row.
		row
			.querySelectorAll( '.custom-recurrence-months > .tribe-field-inline-text:not(:first-child):not(:last-child):not(.first-label-in-line)' )
			.forEach( function ( inlineText ) {
				inlineText.style.display = 'none';
			} );
		row.querySelector( '.custom-recurrence-months .recurrence-month-on-the > .tribe-field-inline-text' ).style.display = 'none';
	};

	/**
	 * Set the locked month icon on year rule type.
	 *
	 * @since 6.0.0
	 *
	 * @param {HTMLElement} row The row to set the locked month icon for.
	 *
	 * @return {void}
	 */
	obj.setLockedYearMonthIcon = function ( row ) {
		// If rule is off-start, return early.
		if ( tec.classicEditorEvents.offStart.isRuleOffStart( row ) ) {
			return;
		}

		const $dropdown = $( row ).find( obj.selectors.recurrenceYearsDropdown );
		const values = $dropdown.val();
		const month = String( tec.classicEditorEvents.eventDate.eventStart.moment.month() + 1 );
		const index = values.indexOf( month );

		// month doesn't exist in values, return early.
		if ( -1 === index ) {
			return;
		}

		const choices = row.querySelectorAll( obj.selectors.recurrenceYearsDropdown + ' + .select2 .select2-selection__choice' );
		const lockedChoice = choices[ index ];

		// If the locked choice does not have the locked class, add it.
		if ( ! lockedChoice.classList.contains( 'select2-selection__choice--locked' ) ) {
			lockedChoice.classList.add( 'select2-selection__choice--locked' );
		}

		// If the locked choice does not have the icon, add it.
		if ( ! lockedChoice.querySelector( '.tec-events-pro-month-lock' ) ) {
			const icon = document.createElement( 'span' );
			icon.classList.add( 'tec-events-pro-month-lock' );
			icon.classList.add( 'dashicons' );
			icon.classList.add( 'dashicons-lock' );
			icon.title = tribe_events_pro_recurrence_strings.customTablesV1.recurrence.lockIconTooltip;
			lockedChoice.appendChild( icon );
		}
	};

	/**
	 * Set the locked month on year rule type.
	 *
	 * @since 6.0.0
	 *
	 * @param {HTMLElement} row The row to set the locked month for.
	 *
	 * @return {void}
	 */
	obj.setLockedYearMonth = function ( row ) {
		// If rule is off-start, return early.
		if ( tec.classicEditorEvents.offStart.isRuleOffStart( row ) ) {
			return;
		}

		const $dropdown = $( row ).find( obj.selectors.recurrenceYearsDropdown );
		const values = $dropdown.val();
		const newValues = values.slice();
		const month = String( tec.classicEditorEvents.eventDate.eventStart.moment.month() + 1 );

		// If the event start month changed, remove the previous month from the values.
		if ( tec.classicEditorEvents.eventDate.didEventStartMonthChange() ) {
			const prevMonth = String( tec.classicEditorEvents.eventDate.eventStart.prevMoment.month() + 1 );
			const index = newValues.indexOf( prevMonth );

			if ( -1 !== index ) {
				newValues.splice( index, 1 );
			}
		}

		// If the rule does not include the month, add it.
		if ( ! newValues.includes( month ) ) {
			newValues.push( month );
		}

		const valuesEqual = newValues.every( function ( value, index ) {
			return value === values[ index ];
		} );

		// If the new values are not equal to the previous values, trigger a change.
		if ( ! valuesEqual ) {
			$dropdown.val( newValues ).trigger( 'change' );
		}

	};

	/**
	 * Hide inline text for year rule if not off-start.
	 *
	 * @since 6.0.0
	 *
	 * @param {HTMLElement} row The row to hide inline text for.
	 *
	 * @return {void}
	 */
	obj.hideYearlyInlineText = function ( row ) {
		// If rule is off-start, return early.
		if ( tec.classicEditorEvents.offStart.isRuleOffStart( row ) ) {
			return;
		}

		// Hide all inline texts we want to hide in year same day row.
		row
			.querySelectorAll( '[data-condition="Yearly"] .tribe-dame-day-select > span > .tribe-field-inline-text' )
			.forEach( function ( inlineText ) {
				inlineText.style.display = 'none';
			} );
	};

	/**
	 * Syncs the state of rules that require locking options.
	 *
	 * @since 6.0.0
	 *
	 * @param {string} type String representation of 'recurrence' or 'exclusion'.
	 *
	 * @return {void}
	 */
	obj.syncLockedOptionsRuleState = function ( type ) {
		document.querySelectorAll( '.tribe-event-' + type ).forEach( function ( row ) {
			const ruleType = row.getAttribute( 'data-recurrence-type' );
			const syncableRuleTypes = [ 'Weekly', 'Monthly', 'Yearly' ];

			// If rule type is not one of syncable rule types, return early.
			if ( ! syncableRuleTypes.includes( ruleType ) ) {
				return;
			}

			switch ( ruleType ) {
				case 'Weekly':
					obj.setLockedWeekDay( row );
					obj.setWeekDayActiveFromLockedState( row );
					break;
				case 'Monthly':
					obj.hideMonthInlineText( row );
					tec.classicEditorEvents.dayOfMonth.syncDateDropdown( row, ruleType );
					break;
				case 'Yearly':
					obj.hideYearlyInlineText( row );
					obj.setLockedYearMonth( row );
					obj.setLockedYearMonthIcon( row );
					tec.classicEditorEvents.dayOfMonth.syncDateDropdown( row, ruleType );
					break;
				default:
					break;
			}
		} );
	};
} )( jQuery, tec.classicEditorEvents.lockedOptions );
