/**
 * Makes sure we have all the required levels on the TEC Object.
 *
 * @since 6.0.0
 *
 * @type {PlainObject}
 */
window.tec = window.tec || {};

/**
 * Configures Classic Editor Events Off-Start Object in the Global TEC variable.
 *
 * @since 6.0.0
 *
 * @type {PlainObject}
 */
tec.classicEditorEvents = tec.classicEditorEvents || {};
tec.classicEditorEvents.offStart = tec.classicEditorEvents.offStart || {};

/**
 * Handles the off-start cases of recurrence and exclusions.
 *
 * @since 6.0.0
 *
 * @param {jQuery} $ A reference to the global jQuery instance.
 * @param {PlainObject} obj tec.classicEditorEvents.offStart object.
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
		disabledOffStartFields: [
			'[data-is-off-start="1"] .tribe-active select[disabled][data-field="end-type"]',
			'[data-is-off-start="1"] .tribe-active input[disabled][data-field="end-count"]',
			'[data-is-off-start="1"] .tribe-active input[disabled][data-field="end"]'
		],
		monthlySameDaySelect: '.custom-recurrence-months .tribe-same-day-select',
		recurrenceEnd: '.recurrence_end',
		recurrenceEndCount: '.recurrence_end_count',
		recurrenceEndDay: '.recurrence-time select',
		recurrenceEndTime: '.recurrence-time .tribe-field-end_time',
		recurrenceEndType: '.recurrence-end-range select',
		recurrenceMonthOnTheDaySelect: '.recurrence-month-on-the > span > select',
		recurrenceMonthOnTheNumberSelect: '.recurrence-month-on-the > select',
		recurrenceMonthOnTheSelect: '.recurrence-month-on-the select',
		recurrenceRuleInterval: '.tribe-recurrence-rule-interval',
		recurrenceSameTimeSelect: '.tribe-same-time-select',
		recurrenceStartTime: '.recurrence-time .tribe-field-start_time',
		recurrenceYearMonths: '.custom-recurrence-years',
		recurrenceYearOnTheDaySelect: '.tribe-dame-day-select > span > span > select',
		recurrenceYearOnTheNumberSelect: '.tribe-dame-day-select > span > select',
		recurrenceYearOnTheSelect: '.tribe-dame-day-select select',
		recurrenceWeekDayInput: '.custom-recurrence-weeks .tribe-button-field .tribe-button-input',
		recurrenceYears: '.custom-recurrence-years',
		ruleTypeDropdown: 'select.tec-events-pro-rule-type__dropdown',
		sameDaySelect: '.tribe-same-day-select',
		yearlySameDaySelect: '[data-condition="Yearly"] .tribe-same-day-select',
	};

	/**
	 * Classes used for configuration and setup.
	 *
	 * @since 6.0.0
	 *
	 * @type {PlainObject}
	 */
	obj.classes = {
		dropdownOption: 'tec-events-pro-rule-type__dropdown-option',
		dropdownOptionCustom: 'tec-events-pro-rule-type__dropdown-option--custom',
	};

	/**
	 * Set is off start attribute for rule.
	 *
	 * @since 6.0.0
	 *
	 * @param {HTMLElement} row   The row to set is off start attribute for.
	 * @param {string}      value The value of the is off start attribute.
	 *
	 * @return {void}
	 */
	obj.setRuleOffStart = function ( row, value ) {
		row.setAttribute( 'data-is-off-start', value );
	};

	/**
	 * Determine whether a rule is off-start or not.
	 *
	 * @since 6.0.0
	 *
	 * @param {HTMLElement} row The row to determine whether rule is off-start or
	 *   not.
	 *
	 * @return {boolean}
	 */
	obj.isRuleOffStart = function ( row ) {
		return '1' === row.getAttribute( 'data-is-off-start' );
	};

	/**
	 * Disables all fields for a given recurrence or exclusion rule.
	 *
	 * @since 6.0.0
	 *
	 * @param {HTMLElement} row The row to disable all fields for.
	 *
	 * @return {void}
	 */
	obj.disableAllFields = function ( row ) {
		const $row = $( row );
		const selectors = [
			obj.selectors.recurrenceEnd,
			obj.selectors.recurrenceEndCount,
			obj.selectors.recurrenceEndDay,
			obj.selectors.recurrenceEndTime,
			obj.selectors.recurrenceEndType,
			obj.selectors.recurrenceMonthOnTheSelect,
			obj.selectors.recurrenceRuleInterval,
			obj.selectors.recurrenceSameTimeSelect,
			obj.selectors.recurrenceStartTime,
			obj.selectors.recurrenceYearOnTheSelect,
			obj.selectors.recurrenceYears,
			obj.selectors.sameDaySelect,
		];
		selectors.forEach( function ( selector ) {
			$row.find( selector ).prop( 'disabled', true );
		} );
	};

	/**
	 * Set week day to active or not based on `active` parameter.
	 *
	 * @since 6.0.0
	 *
	 * @param {HTMLElement} day    The day to set as active or not.
	 * @param {boolean}     active Whether the day should be active or not.
	 *
	 * @return {void}
	 */
	obj.setWeekDay = function ( day, active ) {
		const isActive = day.classList.contains( 'tribe-active' );

		if ( ( active && isActive ) || ( ! active && ! isActive ) ) {
			return;
		}

		day.click();
	};

	/**
	 * Select week days based on the array of days.
	 *
	 * @since 6.0.0
	 *
	 * @param {HTMLElement} row  The row to select week days for.
	 * @param {array}       days The days to select.
	 *
	 * @return {void}
	 */
	obj.selectWeekDays = function ( row, days ) {
		row
			.querySelectorAll( obj.selectors.recurrenceWeekDayInput )
			.forEach( function ( dayInput ) {
				const active = days.includes( dayInput.value );
				const dayButton = dayInput.parentNode;
				obj.setWeekDay( dayButton, active );
			} );
	};

	/**
	 * Compare 2 arrays of months to see if they are equal.
	 *
	 * @since 6.0.0
	 *
	 * @param {array} month1 The first array of months to compare.
	 * @param {array} month2 The second array of months to compare.
	 *
	 * @return {boolean} Whether the months are the same or not.
	 */
	obj.isYearMonthsSame = function ( months1, months2 ) {
		// If neither arguments are an array, return false.
		if ( ! Array.isArray( months1 ) || ! Array.isArray( months2 ) ) {
			return false;
		}

		// If the lengths are different, return false.
		if ( months1.length !== months2.length ) {
			return false;
		}

		// Check that each value in the array are equal.
		const length = months1.length;
		for ( var i = 0; i < length; i++ ) {
			if ( months1[ i ] !== months2[ i ] ) {
				// If the values are not equal, return false.
				return false;
			}
		}

		// Both arrays are equal, return true.
		return true;
	};

	/**
	 * Set field to provided value if field value is different.
	 * Call trigger if trigger is provided.
	 *
	 * @since 6.0.0
	 *
	 * @param {jQuery}         $field Field to check and set value on.
	 * @param {string}         value  Value to set field value to.
	 * @param {string|boolean} trigger Trigger type, or false if no trigger.
	 *
	 * @return {void}
	 */
	obj.setFieldValueIfDifferent = function ( $field, value, trigger ) {
		// No field was passed, return early.
		if ( ! $field.length ) {
			return;
		}

		// Field values are equal, return early.
		if ( $field.val() === value ) {
			return;
		}

		$field.val( value );

		if ( trigger ) {
			$field.trigger( trigger );
		}
	};

	/**
	 * Sets field values for weekly rule type from recurrence data.
	 *
	 * @since 6.0.0
	 *
	 * @param {HTMLElement} row  The row to set field values for.
	 * @param {PlainObject} rule The rule data.
	 *
	 * @return {void}
	 */
	obj.setWeeklyFieldValuesFromData = function ( row, rule ) {
		// Set week days.
		if ( rule.custom.week && rule.custom.week.day ) {
			obj.selectWeekDays( row, rule.custom.week.day );
		}
	};

	/**
	 * Sets field values for monthly rule type from recurrence data.
	 *
	 * @since 6.0.0
	 *
	 * @param {HTMLElement} row  The row to set field values for.
	 * @param {PlainObject} rule The rule data.
	 *
	 * @return {void}
	 */
	obj.setMonthlyFieldValuesFromData = function ( row, rule ) {
		const $row = $( row );

		// Set same day select value.
		if ( rule.custom.month && rule.custom.month[ 'same-day' ] ) {
			obj.setFieldValueIfDifferent(
				$row.find( obj.selectors.monthlySameDaySelect ),
				rule.custom.month[ 'same-day' ],
				'change',
			);
		}
		// Set "month on the" number select value.
		if ( rule.custom.month && rule.custom.month.number ) {
			obj.setFieldValueIfDifferent(
				$row.find( obj.selectors.recurrenceMonthOnTheNumberSelect ),
				rule.custom.month.number,
				'change',
			);
		}
		// Set "month on the" day select value.
		if ( rule.custom.month && rule.custom.month.day ) {
			obj.setFieldValueIfDifferent(
				$row.find( obj.selectors.recurrenceMonthOnTheDaySelect ),
				rule.custom.month.day,
				'change',
			);
		}
	};

	/**
	 * Sets field values for yearly rule type from recurrence data.
	 *
	 * @since 6.0.0
	 *
	 * @param {HTMLElement} row  The row to set field values for.
	 * @param {PlainObject} rule The rule data.
	 *
	 * @return {void}
	 */
	obj.setYearlyFieldValuesFromData = function ( row, rule ) {
		const $row = $( row );

		// Set months.
		if ( rule.custom.year && rule.custom.year.month ) {
			const $monthsSelect = $row.find( obj.selectors.recurrenceYearMonths );

			if ( ! obj.isYearMonthsSame( $monthsSelect.val(), rule.custom.year.month ) ) {
				$monthsSelect.val( rule.custom.year.month ).trigger( 'change' );
			}
		}
		// Set same day select value.
		if ( rule.custom.year && rule.custom.year[ 'same-day' ] ) {
			obj.setFieldValueIfDifferent(
				$row.find( obj.selectors.yearlySameDaySelect ),
				rule.custom.year[ 'same-day' ],
				'change',
			);
		}
		// Set "year on the" number select value.
		if ( rule.custom.year && rule.custom.year.number ) {
			obj.setFieldValueIfDifferent(
				$row.find( obj.selectors.recurrenceYearOnTheNumberSelect ),
				rule.custom.year.number,
				'change',
			);
		}
		// Set "year on the" day select value.
		if ( rule.custom.year && rule.custom.year.day ) {
			obj.setFieldValueIfDifferent(
				$row.find( obj.selectors.recurrenceYearOnTheDaySelect ),
				rule.custom.year.day,
				'change',
			);
		}
	};

	/**
	 * Sets field values for common recurrence fields from recurrence data.
	 * These fields include the rule interval, same time / different time
	 * select, start time, end time, end day, end count, and end date.
	 *
	 * @since 6.0.0
	 *
	 * @param {HTMLElement} row  The row to set field values for.
	 * @param {PlainObject} rule The rule data.
	 *
	 * @return {void}
	 */
	obj.setCommonRecurrenceFieldValuesFromData = function ( row, rule ) {
		const $row = $( row );

		// Set interval.
		if ( rule.custom.interval ) {
			obj.setFieldValueIfDifferent(
				$row.find( obj.selectors.recurrenceRuleInterval ),
				rule.custom.interval,
				'change',
			);
		}

		// Set same time select value.
		if ( rule.custom[ 'same-time' ] ) {
			obj.setFieldValueIfDifferent(
				$row.find( obj.selectors.recurrenceSameTimeSelect ),
				rule.custom[ 'same-time' ],
				'change',
			);
		}
		// Set start time.
		if ( rule.custom[ 'start-time' ] ) {
			obj.setFieldValueIfDifferent(
				$row.find( obj.selectors.recurrenceStartTime ),
				rule.custom[ 'start-time' ],
				false,
			);
		}
		// Set end time.
		if ( rule.custom[ 'end-time' ] ) {
			obj.setFieldValueIfDifferent(
				$row.find( obj.selectors.recurrenceEndTime ),
				rule.custom[ 'end-time' ],
				false,
			);
		}
		// Set end day.
		if ( rule.custom[ 'end-day' ] ) {
			obj.setFieldValueIfDifferent(
				$row.find( obj.selectors.recurrenceEndDay ),
				rule.custom[ 'end-day' ],
				'change',
			);
		}

		// Set end type.
		if ( rule[ 'end-type' ] ) {
			obj.setFieldValueIfDifferent(
				$row.find( obj.selectors.recurrenceEndType ),
				rule[ 'end-type' ],
				'change',
			);
		}
		// Set end count.
		if ( rule[ 'end-count'] ) {
			obj.setFieldValueIfDifferent(
				$row.find( obj.selectors.recurrenceEndCount ),
				rule[ 'end-count' ],
				false,
			);
		}
		// Set end date.
		if ( rule.end ) {
			obj.setFieldValueIfDifferent(
				$row.find( obj.selectors.recurrenceEnd ),
				rule.end,
				false,
			);
		}
	};

	/**
	 * Sets field values from recurrence data.
	 *
	 * @since 6.0.0
	 *
	 * @param {HTMLElement} row  The row to set field values for.
	 * @param {string}      type String representation of 'recurrence' or
	 *   'exclusion'.
	 *
	 * @return {void}
	 */
	obj.setFieldValuesFromData = function ( row, type ) {
		const offStartIndex = row.getAttribute( 'data-off-start-index' );

		// Return early if no off-start index exists.
		if ( ! offStartIndex ) {
			return;
		}

		let rule;
		if ( 'recurrence' === type ) {
			rule = tribe_events_pro_recurrence_data.rules[ offStartIndex ];
		} else if ( 'exclusion' === type ) {
			rule = tribe_events_pro_recurrence_data.exclusions[ offStartIndex ];
		}

		// Return early if no rule was found.
		if ( ! rule ) {
			return;
		}

		const ruleType = rule.custom.type;

		// Return early if no rule type was found.
		if ( ! ruleType ) {
			return;
		}

		switch ( ruleType ) {
			case 'Weekly':
				obj.setWeeklyFieldValuesFromData( row, rule );
				obj.setCommonRecurrenceFieldValuesFromData( row, rule );
				break;
			case 'Monthly':
				obj.setMonthlyFieldValuesFromData( row, rule );
				obj.setCommonRecurrenceFieldValuesFromData( row, rule );
				break;
			case 'Yearly':
				obj.setYearlyFieldValuesFromData( row, rule );
				obj.setCommonRecurrenceFieldValuesFromData( row, rule );
				break;
			default:
				break;
		}
	};

	/**
	 * Syncs the state of off-start rules.
	 *
	 * @since 6.0.0
	 *
	 * @param {string} type String representation of 'recurrence' or 'exclusion'.
	 *
	 * @return {void}
	 */
	obj.syncOffStartRuleState = function ( type ) {
		document.querySelectorAll( '.tribe-event-' + type ).forEach( function ( row ) {
			if ( ! obj.isRuleOffStart( row ) ) {
				return;
			}

			obj.setFieldValuesFromData( row, type );
			obj.disableAllFields( row );
		} );
	};

	/**
	 * Set is off start attribute for rule based on selected option.
	 *
	 * @since 6.0.0
	 *
	 * @param {HTMLElement} row            The row to set is off start attribute
	 *   for.
	 * @param {HTMLElement} selectedOption The selected option.
	 *
	 * @return {void}
	 */
	obj.setRuleOffStartFromSelectedOption = function ( row, selectedOption ) {
		const isOffStart = obj.isRuleOffStart( row );
		const selectedOptionIsCustom = selectedOption.classList.contains( obj.classes.dropdownOptionCustom );

		if ( isOffStart && ! selectedOptionIsCustom ) {
			// If rule is off-start and selected option is not custom
			obj.setRuleOffStart( row, '0' );
		} else if ( ! isOffStart && selectedOptionIsCustom ) {
			// If rule is not off-start and selected option is custom
			obj.setRuleOffStart( row, '1' );
		}
	};

	/**
	 * Initialize new rule by setting the off-start flag to 0.
	 *
	 * @since 6.0.0
	 *
	 * @param {NodeList} rows The rows to initialize off-start.
	 *
	 * @return {void};
	 */
	obj.initNewRules = function ( rows ) {
		rows.forEach( function ( row ) {
			obj.setRuleOffStart( row, '0' );
		} );
	};

	/**
	 * Initialize off-start rules for given recurrence or exclusion rows.
	 * For rules that do not match the RRULE pattern, these are considered
	 * off-starts.
	 *
	 * @since 6.0.0
	 *
	 * @param {NodeList} rows The rows to check for off-starts.
	 *
	 * @return {void}
	 */
	obj.initOffStartRules = function ( rows ) {
		rows.forEach( function ( row, index ) {
			const isRecurring = row.classList.contains(
				tec.classicEditorEvents.selectors.recurrence.replace( '.', '' )
			);
			const rule = isRecurring
				? tribe_events_pro_recurrence_data.rules[ index ]
				: tribe_events_pro_recurrence_data.exclusions[ index ];
			const $dropdown = $( row ).find( obj.selectors.ruleTypeDropdown );

			if ( ! rule || ! rule.isOffStart ) {
				obj.setRuleOffStart( row, '0' );
				$dropdown.select2TEC();
				return;
			}

			row.setAttribute( 'data-off-start-index', index );
			obj.setRuleOffStart( row, '1' );

			// Add option to dropdown.
			const option = new Option(
				tribe_events_pro_recurrence_strings.customTablesV1.ruleTypes.custom[ rule.custom.type ],
				rule.custom.type,
				false,
				true,
			);
			option.classList.add( obj.classes.dropdownOption );
			option.classList.add( obj.classes.dropdownOptionCustom );

			$dropdown.append( option ).trigger( 'change' );
			$dropdown.select2TEC();
		} );
	};

	/**
	 * This handles removing disabled attribute on submission for off start fields.
	 * The fields should remain disabled until submission, so the data is not lost.
	 *
	 * @since 6.0.0
	 *
	 * @return {void}
	 */
	obj.enableOffStartFieldsForSubmission = function () {
		$( obj.selectors.disabledOffStartFields.join( ',' ) ).removeAttr( 'disabled' );
	}
} )( jQuery, tec.classicEditorEvents.offStart );
