/**
 * Makes sure we have all the required levels on the TEC Object.
 *
 * @since 6.0.0
 *
 * @type {PlainObject}
 */
window.tec = window.tec || {};

/**
 * Configures Classic Editor Events Default Object in the Global TEC variable.
 *
 * @since 6.0.0
 *
 * @type {PlainObject}
 */
tec.classicEditorEvents = tec.classicEditorEvents || {};
tec.classicEditorEvents.default = tec.classicEditorEvents.default || {};

/**
 * Handles the defaults for new and existing recurrence and exclusion rules.
 *
 * @since 6.0.0
 *
 * @param {jQuery} $ A reference to the global jQuery instance.
 * @param {PlainObject} obj tec.classicEditorEvents.default object.
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
		eventEndDate: '#EventEndDate',
		eventStartDate: '#EventStartDate',
		exclusionDescription: '.tribe-event-recurrence-description',
		datepicker: '.tribe-datepicker',
		recurrenceEnd: '.recurrence_end',
		recurrenceEndCount: '.recurrence_end_count',
		recurrenceEndSelect: '.recurrence-end-range select',
		recurrenceSameTimeSelect: '.tribe-same-time-select',
		recurrenceTypeButton: '.tribe-event-recurrence-rule > .tribe-buttonset .tribe-button-field[data-value]',
		exclusionTypeButton: '.tribe-event-recurrence-exclusion > .tribe-buttonset .tribe-button-field[data-value]',
		ruleEndsText: '.recurrence-end-range .tribe-field-inline-text',
		ruleTypeDropdown: 'select.tec-events-pro-rule-type__dropdown',
		onceButton: 'a[data-value="Date"]',
	};

	/**
	 * Hides the description text on exclusions.
	 *
	 * @todo This function should be converted to template modifications when integrated into ECP.
	 *
	 * @since 6.0.0
	 *
	 * @param {NodeList} rows The rows to apply the defaults upon.
	 *
	 * @return {void}
	 */
	obj.hideExclusionDescription = function ( rows ) {
		rows.forEach( function ( row ) {
			const deprecatedDescriptions = row.querySelectorAll( obj.selectors.exclusionDescription );

			// @todo: Set this in PHP.
			deprecatedDescriptions.forEach( function ( description ) {
				description.style.display = 'none';
			} );
		} );
	};

	/**
	 * Sets up the min dates for the exclusion dates, allowing the user to go back to the original
	 * start date of the first occurrence of the event.
	 * This applies to recurring exclusions (Daily, Weekly, Monthly, Yearly) as well as Once.
	 *
	 * @since 6.0.0
	 *
	 * @param {NodeList} rows The rows to apply the defaults upon.
	 *
	 * @return {void}
	 */
	obj.setExclusionDatepickerMinDates = function ( rows ) {
		const startDate = $( obj.selectors.eventStartDate ).val();

		if ( ! startDate ) {
			return;
		}

		rows.forEach( function ( row ) {
			const $rowDatepicker = $( row ).find( obj.selectors.datepicker );
			const currentDate = $rowDatepicker.val();

			if( currentDate ){
				// Do not override an already existing value.
				return;
			}

			$rowDatepicker.datepicker( 'option', 'minDate', startDate );
		} );
	};

	/**
	 * Swaps the text "Recurring event ends" or "Exclusion ends" to "Ends".
	 *
	 * @since 6.0.0
	 *
	 * @param {NodeList} rows The rows to apply the defaults upon.
	 *
	 * @return {void}
	 */
	obj.setRuleEndsText = function ( rows ) {
		const newEndsText = tecEventSettings.textSubstitution && tecEventSettings.textSubstitution.ruleEnds
			? tecEventSettings.textSubstitution.ruleEnds
			: '';

		if ( ! newEndsText ) {
			return;
		}

		rows.forEach( function ( row ) {
			const ruleEndsText = row.querySelector( obj.selectors.ruleEndsText );

			if ( ! ruleEndsText ) {
				return;
			}

			ruleEndsText.innerText = newEndsText;
		} );
	};

	/**
	 * Sets the default value of the "Ends on" select to "Never".
	 *
	 * @since 6.0.0
	 *
	 * @param {NodeList} rows The rows to apply the defaults upon.
	 *
	 * @return {void} The function will change the default value of the ends on select.
	 */
	obj.setNewEndsOnDefault = function ( rows ) {
		rows.forEach( function ( row ) {
			const endsOnElement = row.querySelectorAll( obj.selectors.recurrenceEndSelect );

			// @todo: Set this in PHP.
			endsOnElement.forEach( function ( element ) {
				element.value = 'Never';
				element.dispatchEvent( new Event( 'change' ) );
			} );
		} );
	};

	/**
	 * Sets the default values for both the recurrence and exclusion end dates.
	 *
	 * @since 6.0.0
	 *
	 * @param {NodeList} rows The rows to apply the defaults upon.
	 *
	 * @return {void} The function will change an empty `.recurrence_end` field to today + 1 year.
	 */
	obj.setNewEndDateDefault = function ( rows ) {
		rows.forEach( function ( row ) {
			const dateFields = row.querySelectorAll( obj.selectors.recurrenceEnd );
			let eventDate = document.querySelector( obj.selectors.eventStartDate ).value;

			// If for some reason the start and/or end date are empty, try to not fatal.
			if ( ! eventDate ) {
				eventDate = document.querySelector( obj.selectors.eventEndDate ).value;
				if ( ! eventDate ) {
					return;
				}
			}

			// Handle differing formats. 1/2/2000 2000-1-2, etc.
			const _delimiter = eventDate.match( /\W/g )[0];
			const datePieces = eventDate.split( _delimiter );
			let oldYear, newYear;

			datePieces.forEach( function ( piece ) {
				// Year is always 4 digits. Unless someone is being sneaky with filters.
				if ( 4 !== piece.length ) {
					return;
				}

				oldYear = piece;
				newYear = parseInt( piece ) + 1;
			} );

			// We couldn't get the year right, bail.
			if ( ! oldYear || ! newYear ) {
				return;
			}

			eventDate = eventDate.replace( oldYear, newYear );
			dateFields.forEach( function ( element ) {
				// If a value is already set, don't change it!
				if ( element.value ) {
					return;
				}

				element.value = eventDate;
				// Set the placeholder too for good measure.
				element.placeholder = eventDate;
			} );
		} );
	};

	/**
	 * Sets the default values for both the recurrence and exclusion end count.
	 *
	 * @since 6.0.0
	 *
	 * @param {NodeList} rows The rows to apply the defaults upon.
	 *
	 * @return {void} The function will change an empty `.recurrence_end_count` field to 10.
	 */
	obj.setNewEndCountDefault = function ( rows ) {
		rows.forEach( function ( row ) {
			const dateFields = row.querySelectorAll( obj.selectors.recurrenceEndCount );

			dateFields.forEach( function ( dateField ) {
				// If a value is already set, don't change it!
				if ( dateField.value ) {
					return;
				}

				dateField.value = 10;
				// Set the placeholder too for good measure.
				dateField.placeholder = 10;
			} );
		} );
	};

	/**
	 * Sets the default value of the "Same Time" select to "no" (a different time).
	 *
	 * @since 6.0.0
	 *
	 * @param {NodeList} rows The rows to apply the defaults upon.
	 *
	 * @return {void} The function will change the default value of the same time select.
	 */
	obj.setNewOnceTimeSelectDefault = function ( rows ) {
		rows.forEach( function ( row ) {
			const onceTimeSelect = row.querySelectorAll( obj.selectors.recurrenceSameTimeSelect );

			// Change default value to 'no' - a different time. So the time drop downs always show.
			onceTimeSelect.forEach( function ( element ) {
				element.value = 'no';
				element.dispatchEvent( new Event( 'change' ) );
			} );
		} );
	};

	/**
	 * Set rule type for new recurrence/exclusion rule to "Once".
	 *
	 * @since 6.0.0
	 *
	 * @param {NodeList} rows The rows to apply the defaults upon.
	 *
	 * @return {void}
	 */
	obj.setNewRuleType = function ( rows ) {
		rows.forEach( function ( row ) {
			const onceButton = row.querySelector( obj.selectors.onceButton );

			if ( ! onceButton ) {
				return;
			}

			onceButton.click(); // Simulate a click event to select the "Once" recurrence type.
		} );
	};

	/**
	 * Add same time class to recurrence row with same time select.
	 * The same time select is the select with values "the same time" or
	 * "a different time".
	 *
	 * @since 6.0.0
	 *
	 * @param {NodeList} rows The recurrence rules to modify.
	 *
	 * @return {void}
	 */
	obj.addSameTimeRowClass = function ( rows ) {
		rows.forEach( function ( row ) {
			const timeSelect = row.querySelector( obj.selectors.recurrenceSameTimeSelect );
			const timeSelectRow = timeSelect.closest( '.recurrence-row' );
			timeSelectRow.classList.add( 'recurrence-same-time' );
		} );
	};

	/**
	 * Handle change event on rule type dropdown.
	 * Get the value of the dropdown on change event, look for the rule type button that has the
	 * same value as the selected dropdown option, and click the button.
	 *
	 * @since 6.0.0
	 *
	 * @param {Event} event Event object.
	 *
	 * @return {void}
	 */
	obj.handleRuleTypeDropdownChange = function ( event ) {
		const $dropdown = $( event.target );
		const $ruleTypeButtonset = $dropdown.closest( '.tribe-buttonset' );
		const $row = $ruleTypeButtonset.parent();
		const selectedOption = event.target.selectedOptions[0];
		const $button = $ruleTypeButtonset.find( 'a[data-value="' + event.target.value + '"]' );

		// Set rule off start attribute from selected option.
		tec.classicEditorEvents.offStart.setRuleOffStartFromSelectedOption( $row[0], selectedOption )

		if ( ! $button.length ) {
			return;
		}

		$button.click();
	};

	/**
	 * Bind events for rule.
	 *
	 * @since 6.0.0
	 *
	 * @param {NodeList} rows The rows to bind the rule events.
	 *
	 * @return {void}
	 */
	obj.bindRuleEvents = function ( rows ) {
		rows.forEach( function ( row ) {
			const ruleTypeDropdown = row.querySelector( obj.selectors.ruleTypeDropdown );

			if ( ! ruleTypeDropdown ) {
				return;
			}

			$( ruleTypeDropdown ).on( 'change', obj.handleRuleTypeDropdownChange );
		} );
	};

	/**
	 * Set rule type dropdown to match selected rule type button.
	 * On initialization, the dropdown will not have the same default selected as the
	 * rule type buttons. This checks the active rule type button and applies it to
	 * the rule type dropdown.
	 *
	 * @since 6.0.0
	 *
	 * @param {NodeList} rows The rows to set rule type dropdown value.
	 *
	 * @return {void}
	 */
	obj.setRuleTypeDropdownToMatchButtons = function ( rows ) {
		rows.forEach( function ( row ) {
			const ruleTypeDropdown = row.querySelector( obj.selectors.ruleTypeDropdown );

			if ( ! ruleTypeDropdown ) {
				return;
			}

			if ( tec.classicEditorEvents.offStart.isRuleOffStart( row ) ) {
				return;
			}

			const isRecurring = row.classList.contains(
				tec.classicEditorEvents.selectors.recurrence.replace( '.', '' )
			);
			const activeRuleTypeButton = isRecurring
					? row.querySelector( obj.selectors.recurrenceTypeButton + '.tribe-active' )
					: row.querySelector( obj.selectors.exclusionTypeButton + '.tribe-active' );

			if ( ! activeRuleTypeButton ) {
				return;
			}

			const activeRuleType = activeRuleTypeButton.getAttribute( 'data-value' );

			$( ruleTypeDropdown ).val( activeRuleType ).trigger( 'change' );
		} );
	};

	/**
	 * Applies a flag attribute to the supplied Recurrence and/or Exclusion rows.
	 *
	 * @since 6.0.0
	 *
	 * @param {NodeList} rows The rows to apply the defaults upon.
	 *
	 * @return {void} The function will apply the specified attribute and value to the rows.
	 */
	obj.setDefaultsSetFlag = function ( rows ) {
		rows.forEach( function ( row ) {
			row.setAttribute( 'data-defaults-set', '1' );
		} );
	};

} )( jQuery, tec.classicEditorEvents.default );
