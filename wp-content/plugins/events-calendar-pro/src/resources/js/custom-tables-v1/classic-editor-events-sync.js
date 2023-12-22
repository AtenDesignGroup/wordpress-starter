/**
 * Makes sure we have all the required levels on the TEC Object.
 *
 * @since 6.0.0
 *
 * @type {PlainObject}
 */
window.tec = window.tec || {};

/**
 * Configures Classic Editor Events Sync Object in the Global TEC variable.
 *
 * @since 6.0.0
 *
 * @type {PlainObject}
 */
tec.classicEditorEvents = tec.classicEditorEvents || {};
tec.classicEditorEvents.sync = tec.classicEditorEvents.sync || {};

/**
 * Handles the syncing the state of recurrence and exclusions.
 *
 * @since 6.0.0
 *
 * @param {jQuery} $ A reference to the global jQuery instance.
 * @param {PlainObject} obj tec.classicEditorEvents.sync object.
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
		activeType: 'a.tribe-button-field.tribe-active',
		customDateContainer: '.recurrence-custom-container',
		eventStartDate: '#EventStartDate',
		recurrence: '.tribe-event-recurrence',
		recurrenceSameTimeSelect: '.tribe-same-time-select',
		timeSelect: '.tribe-same-time-select',
	};

	/**
	 * Set the same time value according to our current selection state, per rule.
	 *
	 * @since 6.0.0
	 *
	 * @return {void}
	 */
	obj.setRecurrenceSameTimeValue = function () {
		const recurrenceRules = document.querySelectorAll( obj.selectors.recurrence );

		// If no recurrence rules, return early.
		if ( ! recurrenceRules.length ) {
			return;
		}

		recurrenceRules.forEach( function ( rule ) {
			const $rule = $( rule );
			const activeType = rule.querySelector( ':scope > .tribe-buttonset ' + obj.selectors.activeType );

			// If no active rule type, return early.
			if ( ! activeType ) {
				return;
			}

			const $sameTimeSelect = $rule.find( obj.selectors.recurrenceSameTimeSelect );

			// If no same time select is found, return early.
			if ( ! $sameTimeSelect.length ) {
				return;
			}

			const isOffStart = tec.classicEditorEvents.offStart.isRuleOffStart( rule );

			// If rule type is off-start, return early.
			if ( isOffStart ) {
				return;
			} else if ( 'Date' === activeType.getAttribute( 'data-value' ) ) {
				// If active rule type is 'Once', set value to 'no'.
				// If value is already 'no', return early.
				if ( 'no' === $sameTimeSelect.val() ) {
					return;
				}

				$sameTimeSelect.val( 'no' ).trigger( 'change' );
				return;
			} else if ( 'yes' === $sameTimeSelect.val() ) {
				// If value is already 'yes', return early.
				return;
			}

			$sameTimeSelect.val( 'yes' ).trigger( 'change' );
		} );
	};


	/**
	 * Sets the visibility of the recurrence time select.
	 * The time select is the option to select "At the same time/a different time" for recurrence rules.
	 * If the active recurrence type is not "Once", hide the time select. If the active recurrence type
	 * is "Once", show the time select.
	 *
	 * @since 6.0.0
	 *
	 * @return {void}
	 */
	obj.setRecurrenceTimeSelectVisibility = function () {
		const timeSelects = document.querySelectorAll( obj.selectors.timeSelect );

		timeSelects.forEach( function ( timeSelect ) {
			const recurrenceRule = timeSelect.closest( obj.selectors.recurrence );
			const timeSelectRow = timeSelect.closest( '.recurrence-row' );

			// If neither element was found, return early.
			if ( ! recurrenceRule || ! timeSelectRow ) {
				return;
			}

			const recurrenceType = recurrenceRule.getAttribute( 'data-recurrence-type' );
			const isOffStart = tec.classicEditorEvents.offStart.isRuleOffStart( recurrenceRule );
			if ( 'Date' === recurrenceType || isOffStart ) {
				// Active recurrence type is "Once". Show the time select for this recurrence rule.
				timeSelectRow.style.removeProperty( 'display' );
			} else {
				// Active recurrence type is not "Once". Hide the time select for this recurrence rule.
				timeSelectRow.style.display = 'none';
			}
		} );
	};

	/**
	 * Sets the visibility of the recurrence or exclusion type buttonsets.
	 * This is the set of buttons labeled "Daily", "Weekly", "Monthly", "Yearly", and "Once".
	 * If there is at least one buttonset that has an active recurrence type that is not "Once",
	 * the other buttonsets are hidden and forced to only show the "Once" recurrence type.
	 *
	 * @since 6.0.0
	 *
	 * @param {string} type String representation of 'recurrence' or 'exclusion'.
	 *
	 * @return {void}
	 */
	obj.setRuleTypeButtonsetVisibility = function ( type ) {
		const ruleTypeButtonsets = document.querySelectorAll( '.tribe-event-' + type + ' > .tribe-buttonset' );

		// If no rule type buttonsets, return early.
		if ( ! ruleTypeButtonsets.length ) {
			return;
		}

		const activeNonOnceButtonsets = []; // Buttonsets with active rule type that is not "Once".
		const buttonsetsToHide = []; // Buttonsets to hide, active rule type is "Once".

		ruleTypeButtonsets.forEach( function ( buttonset ) {
			const activeType = buttonset.querySelector( obj.selectors.activeType );

			// If no active rule type, return early.
			if ( ! activeType ) {
				return;
			}

			if ( 'Date' === activeType.getAttribute( 'data-value' ) ) {
				// Active rule type is "Once".
				buttonsetsToHide.push( buttonset );
			} else {
				// Active rule type is not "Once".
				activeNonOnceButtonsets.push( buttonset );
			}
		} );

		if ( activeNonOnceButtonsets.length ) {
			// If there are buttonsets with active rule type that is not "Once", hide the other buttonsets.
			buttonsetsToHide.forEach( function ( buttonset ) {
				buttonset.style.display = 'none';
				const customDateContainer = buttonset
					.closest( '.tribe-event-' + type )
					.querySelector( obj.selectors.customDateContainer );

				if ( ! customDateContainer ) {
					return;
				}

				customDateContainer.style.paddingTop = 0;
			} );
			// Show the buttonsets with active rule type that is not "Once".
			activeNonOnceButtonsets.forEach( function ( buttonset ) {
				buttonset.style.removeProperty( 'display' );
				const customDateContainer = buttonset
					.closest( '.tribe-event-' + type )
					.querySelector( obj.selectors.customDateContainer );

				if ( ! customDateContainer ) {
					return;
				}

				customDateContainer.style.removeProperty( 'padding-top' );
			} );
		} else {
			// If there are no buttonsets with active rule type that is not "Once", show all buttonsets.
			ruleTypeButtonsets.forEach( function ( buttonset ) {
				buttonset.style.removeProperty( 'display' );
				const customDateContainer = buttonset
					.closest( '.tribe-event-' + type )
					.querySelector( obj.selectors.customDateContainer );

				if ( ! customDateContainer ) {
					return;
				}

				customDateContainer.style.removeProperty( 'padding-top' );
			} );
		}
	};

	/**
	 * Sets the visibility of the add recurrence or exclusion button.
	 * If there is more than 1 recurrence rule with a recurrence type that is not "Once",
	 * prevent adding a new rule until there is 0 or 1 recurrence rule with a recurrence
	 * type that is not "Once".
	 * The above also applies for exclusions.
	 *
	 * @since 6.0.0
	 *
	 * @param {string} type String representation of 'recurrence' or 'exclusion'.
	 *
	 * @return {void}
	 */
	obj.setAddRuleButtonVisibility = function ( type ) {
		const nonOnceRules = document.querySelectorAll(
			'.tribe-event-' + type + ' .tribe-recurrence-rule-type:not([value="Date"]):not([value=""])'
		);

		const addRuleButton = document.querySelector( '#tribe-add-' + type );
		if ( nonOnceRules.length > 1 ) {
			// There is more than 1 rules with a type that is not "Once", prevent adding a new rule.
			addRuleButton.style.display = 'none';
		} else {
			addRuleButton.style.removeProperty( 'display' );
		}
	};

	/**
	 * Syncs the recurrence state.
	 * This ensures that the state of having at most one recurrence rule with a recurrence type
	 * that is not "Once" is maintained.
	 *
	 * @since 6.0.0
	 *
	 * @return {void}
	 */
	obj.syncRecurrenceState = function () {
		obj.setRecurrenceSameTimeValue();
		obj.setRecurrenceTimeSelectVisibility();
		obj.setRuleTypeButtonsetVisibility( 'recurrence' );
		obj.setAddRuleButtonVisibility( 'recurrence' );
		tec.classicEditorEvents.lockedOptions.syncLockedOptionsRuleState( 'recurrence' );
		tec.classicEditorEvents.offStart.syncOffStartRuleState( 'recurrence' );
	};

} )( jQuery, tec.classicEditorEvents.sync );
