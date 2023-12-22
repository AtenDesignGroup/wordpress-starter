/**
 * Makes sure we have all the required levels on the TEC Object.
 *
 * @since 6.0.0
 *
 * @type {PlainObject}
 */
window.tec = window.tec || {};

/**
 * Configures Classic Editor Events Day Of Month Object in the Global TEC
 * variable.
 *
 * @since 6.0.0
 *
 * @type {PlainObject}
 */
tec.classicEditorEvents = tec.classicEditorEvents || {};
tec.classicEditorEvents.dayOfMonth = tec.classicEditorEvents.dayOfMonth || {};

/**
 * Handles the day of month cases of recurrence and exclusions.
 *
 * @since 6.0.0
 *
 * @param {jQuery} $ A reference to the global jQuery instance.
 * @param {PlainObject} obj tec.classicEditorEvents.dayOfMonth object.
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
		monthDayDropdown: '.custom-recurrence-months select[data-field="custom-month-day"]',
		monthNumberDropdown: '.custom-recurrence-months select[data-field="custom-month-number"]',
		monthOnTheDropdown: '.tec-events-pro-month-on-the-dropdown',
		monthSameDayDropdown: '.custom-recurrence-months .tribe-same-day-select',
		yearMonthDayDropdown: '[data-condition="Yearly"] select[data-field="custom-year-month-day"]',
		yearMonthNumberDropdown: '[data-condition="Yearly"] select[data-field="custom-year-month-number"]',
		yearNotSameDayDropdown: '.tec-events-pro-year-not-same-day-dropdown',
		yearSameDayDropdown: '[data-condition="Yearly"] .tribe-dame-day-select .tribe-same-day-select',
	};

	/**
	 * Typed selectors based on rule type.
	 *
	 * @since 6.0.0
	 *
	 * @type {PlainObject}
	 */
	obj.typedSelectors = {
		dayDropdown: '',
		numberDropdown: '',
		sameDayDropdown: '',
		combinedDropdown: '',
	};

	/**
	 * Set the typed selectors object depending on the rule type.
	 *
	 * @since 6.0.0
	 *
	 * @param {string} ruleType The rule type, one of `Monthly` or `Yearly`.
	 *
	 * @return {void}
	 */
	obj.setTypedSelectors = function ( ruleType ) {
		switch ( ruleType ) {
			case 'Monthly':
				obj.typedSelectors.dayDropdown = obj.selectors.monthDayDropdown;
				obj.typedSelectors.numberDropdown = obj.selectors.monthNumberDropdown;
				obj.typedSelectors.sameDayDropdown = obj.selectors.monthSameDayDropdown;
				obj.typedSelectors.combinedDropdown = obj.selectors.monthOnTheDropdown;
				break;
			case 'Yearly':
				obj.typedSelectors.dayDropdown = obj.selectors.yearMonthDayDropdown;
				obj.typedSelectors.numberDropdown = obj.selectors.yearMonthNumberDropdown;
				obj.typedSelectors.sameDayDropdown = obj.selectors.yearSameDayDropdown;
				obj.typedSelectors.combinedDropdown = obj.selectors.yearNotSameDayDropdown;
				break;
			default:
				obj.typedSelectors.dayDropdown = '';
				obj.typedSelectors.numberDropdown = '';
				obj.typedSelectors.sameDayDropdown = '';
				obj.typedSelectors.combinedDropdown = '';
				break;
		}
	};

	/**
	 * Set the typed selectors object based on the rule type. Return whether
	 * setting the selectors was successful or not.
	 *
	 * @since 6.0.0
	 *
	 * @param {string} ruleType The rule type.
	 *
	 * @return {void}
	 */
	obj.setTypedSelectorsFromRuleType = function ( ruleType ) {
		obj.setTypedSelectors( ruleType );

		const syncableRuleTypes = [ 'Monthly', 'Yearly' ];
		return syncableRuleTypes.includes( ruleType );
	};

	/**
	 * Determine whether the date dropdown matches the event start date.
	 *
	 * @since 6.0.0
	 *
	 * @param {HTMLElement} row The row to check the date dropdown.
	 *
	 * @returns {boolean}
	 */
	obj.isDateDropdownMatchEventStart = function ( row ) {
		const dropdown = row.querySelector( obj.typedSelectors.combinedDropdown );
		const valueArr = dropdown.value.split( '-' );

		// If only one value, the dropdown is a date.
		if ( valueArr.length === 1 ) {
			// Return whether the dropdown value matches the date.
			const date = tec.classicEditorEvents.eventDate.eventStart.moment.date();
			return valueArr[0] === String( date );
		}

		// If two values, the dropdown is a pattern (eg. first Thursday).
		if ( valueArr.length === 2 ) {
			const weekday = tec.classicEditorEvents.eventDate.eventStart.moment.isoWeekday();
			const ordinal = tec.classicEditorEvents.eventDate.getEventStartWeekdayOrdinalValue();
			const isLastWeekday = tec.classicEditorEvents.eventDate.isEventStartWeekdayLast();
			const isLastDay = tec.classicEditorEvents.eventDate.isEventStartDateLastOfMonth();
			const ordinalMatches = ( valueArr[0] === ordinal ) || ( ( isLastWeekday || isLastDay ) && valueArr[0] === 'Last' );
			const weekdayMatches = valueArr[1] === String( weekday ) || ( isLastDay && valueArr[1] === '8' );
			return ordinalMatches && weekdayMatches;
		}

		// Something is weird if we are here, return false.
		return false;
	};

	/**
	 * Determine whether the date dropdown matches the old number and day
	 * dropdowns.
	 *
	 * @since 6.0.0
	 *
	 * @param {HTMLElement} row The row to check the dropdown values.
	 *
	 * @return {boolean}
	 */
	obj.isDateDropdownMatchNumberAndDayDropdowns = function ( row ) {
		const dropdown = row.querySelector( obj.typedSelectors.combinedDropdown );
		const numberDropdown = row.querySelector( obj.typedSelectors.numberDropdown );
		const dayDropdown = row.querySelector( obj.typedSelectors.dayDropdown );

		const valueArr = dropdown.value.split( '-' );

		// If `valueArr` has no items, return false.
		if ( ! valueArr.length ) {
			return false;
		}

		// Return whether number dropdown matches the first value.
		const firstValueMatch = valueArr[0] === numberDropdown.value;

		// If first value doesn't match, return false.
		if ( ! firstValueMatch ) {
			return false;
		}

		// If there is no second value, return true.
		if ( ! valueArr[1] ) {
			return true;
		}

		// Return whether day dropdown matches second value.
		return valueArr[1] === dayDropdown.value;
	};

	/**
	 * Sets the date dropdown value from the number and day dropdowns.
	 *
	 * @since 6.0.0
	 *
	 * @param {jQuery} $dropdown The combined dropdown jQuery object.
	 * @param {string|null} optionType The option type to select, if available.
	 *
	 * @return {void}
	 */
	obj.setDateDropdownValue = function ( $dropdown, optionType ) {
		const isInitialSync = ! moment.isMoment( tec.classicEditorEvents.eventDate.eventStart.prevMoment );

		let value;
		const dropdownEl = $dropdown[ 0 ];
		const defaultValue = dropdownEl.childNodes[ 0 ].value;

		// Try and match the previous option type to a new option, if possible.
		if ( optionType && ! isInitialSync ) {
			const optionTypes = Array.from( dropdownEl.childNodes ).
					map( ( option ) => obj.getMonthlyOnOptionTypeFromValue(
							option.value ) );
			const optionTypePos = optionTypes.indexOf( optionType );
			value = optionTypePos >= 0
					? dropdownEl.childNodes[ optionTypePos ].value
					: defaultValue;
		}

		if ( isInitialSync ) {
			/*
			 * Either this is the initial sync, or we could not match the previous
			 * option type to a new one.
			 */
			const number = $( obj.typedSelectors.numberDropdown ).val();

			if ( isNaN( number ) ) {
				// Day of month is a pattern.
				const day = $( obj.typedSelectors.dayDropdown ).val();
				value = [ number, day ].join( '-' );
			}
			else {
				// Day of month is a number.
				value = number;
			}
		}

		$dropdown.val( value || defaultValue );
	};

	/**
	 * Given the value of an "On the" option, return its type.
	 *
	 * @since 6.0.0
	 *
	 * @param {string|number} value The value of the option.
	 * @returns {string|null} Either the type of the option, or `null` if the
	 *     type could not be determined.
	 */
	obj.getMonthlyOnOptionTypeFromValue = function( value ){
		if ( ! value ) {
			return null;
		}

		if ( value.match( /last-8/i ) ) {
			return 'last-day-in-month';
		}

		if ( value.match( /(first|second|third|fourth|fifth)-\d/i ) ) {
			return 'day-of-week-in-month';
		}

		if ( value.match( /last-\d/i ) ) {
			return 'last-day-of-week-in-month';
		}

		return 'day-n';
	};

	/**
	 * Sync date dropdown options with event start date.
	 *
	 * @since 6.0.0
	 *
	 * @param {HTMLElement} row The row to sync date dropdown options for.
	 *
	 * @return {void}
	 */
	obj.syncDateDropdownOptions = function ( row ) {
		const dropdown = row.querySelector( obj.typedSelectors.combinedDropdown );
		const optionType = obj.getMonthlyOnOptionTypeFromValue( dropdown.value );

		// Remove all options from dropdown.
		while ( dropdown.firstChild ) {
			dropdown.removeChild( dropdown.lastChild );
		}

		const $dropdown = $( dropdown );

		// Create pattern option and add to dropdown.
		const ordinal = tec.classicEditorEvents.eventDate.getEventStartWeekdayOrdinalValue();
		const weekdayName = tec.classicEditorEvents.eventDate.getEventStartWeekdayName();
		const patternKey = ordinal.toLowerCase() + weekdayName;
		const patternObj = tribe_events_pro_recurrence_strings.customTablesV1.dayOfMonth.pattern[ patternKey ];
		const patternOption = new Option(
			patternObj.label,
			patternObj.ordinal + '-' + patternObj.day,
			false,
			true,
		);
		$dropdown.append( patternOption );

		// If the event start is last weekday of the month, add last weekday option.
		if ( tec.classicEditorEvents.eventDate.isEventStartWeekdayLast() ) {
			const wasLastWeekdayOfTheMonth = 'Last' === row.querySelector('select[data-field="custom-month-number"]').value;
			const lastWeekdayPatternKey = 'last' + weekdayName;
			const lastWeekdayPatternObj = tribe_events_pro_recurrence_strings.customTablesV1.dayOfMonth.pattern[ lastWeekdayPatternKey ];
			const lastWeekdayValue = lastWeekdayPatternObj.ordinal + '-' + lastWeekdayPatternObj.day;
			const lastWeekdayPatternOption = new Option(
				lastWeekdayPatternObj.label,
				lastWeekdayValue,
				false,
				wasLastWeekdayOfTheMonth,
			);
			$dropdown.append( lastWeekdayPatternOption );
		}

		// If the event start is the last day of the month, add last day option.
		if ( tec.classicEditorEvents.eventDate.isEventStartDateLastOfMonth() ) {
			const lastDayPatternKey = 'lastDay';
			const lastDayPatternObj = tribe_events_pro_recurrence_strings.customTablesV1.dayOfMonth.pattern[ lastDayPatternKey ];
			const lastDayPatternOption = new Option(
				lastDayPatternObj.label,
				lastDayPatternObj.ordinal + '-' + lastDayPatternObj.day,
				false,
				false,
			);
			$dropdown.append( lastDayPatternOption );
		}

		// Create date option and add to dropdown.
		const wasDayOfMonth = $.isNumeric( row.querySelector('select[data-field="custom-month-number"]').value );
		const date = tec.classicEditorEvents.eventDate.eventStart.moment.date();
		const label = tribe_events_pro_recurrence_strings.customTablesV1.dayOfMonth.date[ date ];
		const dateOption = new Option(
			label,
			date,
			false,
			wasDayOfMonth,
		);

		$dropdown.append( dateOption );
		obj.setDateDropdownValue( $dropdown, optionType );
		$dropdown.tribe_dropdowns().trigger( 'change' );
	};

	/**
	 * Sync date dropdown with event start date.
	 *
	 * @since 6.0.0
	 *
	 * @param {HTMLElement} row      The row to sync date dropdown for.
	 * @param {string}      ruleType The rule type, one of `Monthly` or `Yearly`.
	 *
	 * @return {void}
	 */
	obj.syncDateDropdown = function ( row, ruleType ) {
		// If rule is off-start, return early.
		if ( tec.classicEditorEvents.offStart.isRuleOffStart( row ) ) {
			return;
		}

		// If RDATE, we leave rules alone.
		if( tecEventDetails.isRdate ) {
			return;
		}

		// If typed selectors were not set, return early;
		if ( ! obj.setTypedSelectorsFromRuleType( ruleType ) ) {
			return;
		}

		const $row = $( row );

		// Set same day dropdown to `no` if not already.
		if ( $row.find( obj.typedSelectors.sameDayDropdown ).val() === 'yes' ) {
			$row.find( obj.typedSelectors.sameDayDropdown ).val( 'no' ).trigger( 'change' );
		}

		// Event start previous moment will not be set on initial sync.
		const isInitialSync = ! moment.isMoment( tec.classicEditorEvents.eventDate.eventStart.prevMoment );
		// Checks if the start day has changed or not.
		const isSameDay = moment.isMoment( tec.classicEditorEvents.eventDate.eventStart.moment ) &&
			moment.isMoment( tec.classicEditorEvents.eventDate.eventStart.prevMoment ) &&
			tec.classicEditorEvents.eventDate.eventStart.moment.isSame( tec.classicEditorEvents.eventDate.eventStart.prevMoment, 'day' );
		// Checks if the date dropdown matches event start date.
		const dropdownMatchesEventStart = obj.isDateDropdownMatchEventStart( row );

		// If is initial sync, is not the same day, or dropdown does not match event start, sync dropdown.
		if ( isInitialSync || ! isSameDay || ! dropdownMatchesEventStart ) {
			obj.syncDateDropdownOptions( row );
			return;
		}

		// If dropdown doesn't match old dropdowns, sync dropdown values.
		if ( ! obj.isDateDropdownMatchNumberAndDayDropdowns( row ) ) {
			$row.find( obj.typedSelectors.combinedDropdown ).trigger( 'change' );
		}
	};

	/**
	 * Handle date dropdown `change` event.
	 *
	 * @since 6.0.0
	 *
	 * @param {Event} event Event object.
	 *
	 * @return {void}
	 */
	obj.handleDropdownChange = function ( event ) {
		const valueArr = event.target.value.split( '-' );
		const $row = $( event.data.row );
		const ruleType = $row.attr( 'data-recurrence-type' );

		// If typed selectors were not set, return early;
		if ( ! obj.setTypedSelectorsFromRuleType( ruleType ) ) {
			return;
		}

		const $numberDropdown = $row.find( obj.typedSelectors.numberDropdown );
		const $dayDropdown = $row.find( obj.typedSelectors.dayDropdown );

		// If `valueArr` has no items, return early.
		if ( ! valueArr.length ) {
			return;
		}

		// If the first value is empty, return early.
		if ( ! valueArr[0] ) {
			return;
		}

		// Set the number dropdown to the date or ordinal (first value in `valueArr`).
		$numberDropdown.val( valueArr[0] ).trigger( 'change' );

		// If there is no second value in `valueArr`, return early.
		if ( ! valueArr[1] ) {
			return;
		}

		// Set the number dropdown to the weekday.
		$dayDropdown.val( valueArr[1] ).trigger( 'change' );
	};

	/**
	 * Bind events for rules.
	 *
	 * @since 6.0.0
	 *
	 * @param {NodeList} rows The rows to bind the rule events.
	 *
	 * @return {void}
	 */
	obj.bindRuleEvents = function ( rows ) {
		rows.forEach( function ( row ) {
			const $row = $( row );
			$row.find( obj.selectors.monthOnTheDropdown ).on( 'change', { row: row }, obj.handleDropdownChange );
			$row.find( obj.selectors.yearNotSameDayDropdown ).on( 'change', { row: row }, obj.handleDropdownChange );
		} );
	};
} )( jQuery, tec.classicEditorEvents.dayOfMonth );
