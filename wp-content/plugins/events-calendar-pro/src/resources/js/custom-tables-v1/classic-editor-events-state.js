/**
 * Makes sure we have all the required levels on the TEC Object
 *
 * @since 6.0.0
 *
 * @type {PlainObject}
 */
window.tec = window.tec || {};

/**
 * Configures Classic Editor Events State Object in the Global TEC variable
 *
 * @since 6.0.0
 *
 * @type {PlainObject}
 */
tec.classicEditorEventsState = tec.classicEditorEventsState || {};

/**
 * Handles the state of the event. The initial state is captured when the event is loaded.
 * Helper functions compare the current state of the event to the initial state to determine
 * whether the event state is dirty or not.
 *
 * @since 6.0.0
 *
 * @param {jQuery} $ A reference to the global jQuery instance.
 * @param {PlainObject} obj tec.classicEditorEventsState object.
 */
( function ( $, obj ) {
	"use strict";
	const $document = $( document );

	/**
	 * Object holding the initial state of this event.
	 *
	 * @since 6.0.0
	 *
	 * @type {PlainObject}
	 */
	obj.initialState = {};

	/**
	 * Returns the field value.
	 *
	 * @since 6.0.0
	 *
	 * @param {jQuery} $field jQuery object of the field.
	 *
	 * @return {*} The field value.
	 */
	obj.getFieldValue = function ( $field ) {
		return $field.val();
	};

	/**
	 * Returns a closure that returns the field attribute.
	 *
	 * @since 6.0.0
	 *
	 * @param {string} attribute The attribute name.
	 *
	 * @return {Function} A function that returns the field attribute.
	 */
	obj.getFieldAttribute = function ( attribute ) {
		/**
		 * Returns the field attribute.
		 *
		 * @since 6.0.0
		 *
		 * @param {jQuery} $field jQuery object of the field.
		 *
		 * @return {*} The field attribute value.
		 */
		return function ( $field ) {
			return $field.attr( attribute );
		};
	};

	/**
	 * Returns whether the number of fields is greater than 0.
	 *
	 * @since 6.0.0
	 *
	 * @param {jQuery} $field jQuery object of the field.
	 *
	 * @return {boolean} Whether the number of fields is greater than 0.
	 */
	obj.getFieldCountGtZero = function ( $field ) {
		return $field.length > 0;
	};

	/**
	 * Concatenates the values of each field.
	 *
	 * @since 6.0.0
	 *
	 * @param {jQuery} $fields jQuery object of the fields.
	 *
	 * @return {string} A concatenation of each field's value, or empty string.
	 */
	obj.concatFieldValues = function ( $fields ) {
		let value = '';

		$fields.not( ':disabled' ).each( function ( index, element ) {
			value += element.value;
		} );

		return value;
	};

	/**
	 * A map of each field selector and value function.
	 *
	 * @since 6.0.0
	 *
	 * @type {PlainObject}
	 */
	obj.fieldsMap = {
		startDate: {
			selector: 'input#EventStartDate',
			valueGetter: obj.getFieldValue,
		},
		startTime: {
			selector: 'input#EventStartTime',
			valueGetter: obj.getFieldValue,
		},
		endDate: {
			selector: 'input#EventEndDate',
			valueGetter: obj.getFieldValue,
		},
		endTime: {
			selector: 'input#EventEndTime',
			valueGetter: obj.getFieldValue,
		},
		timezone: {
			selector: 'select#event-timezone',
			valueGetter: obj.getFieldAttribute( 'data-timezone-value' ),
		},
		isRecurring: {
			selector: '[name="is_recurring[]"]',
			valueGetter: obj.getFieldCountGtZero,
		},
		recurrence: {
			selector: '.recurrence-row [name^="recurrence[rules]["]',
			valueGetter: obj.concatFieldValues,
		},
		exclusion: {
			selector: '.recurrence-row [name^="recurrence[exclusions]["]',
			valueGetter: obj.concatFieldValues,
		},
	};

	/**
	 * Gets the current state of the event.
	 *
	 * @since 6.0.0
	 *
	 * @return {PlainObject} The current state of the event.
	 */
	obj.getCurrentState = function () {
		const currentState = {};

		for ( const key in obj.fieldsMap ) {
			const selector = obj.fieldsMap[ key ].selector;
			const valueGetter = obj.fieldsMap[ key ].valueGetter;
			currentState[ key ] = valueGetter( $( selector ) );
		}

		return currentState;
	};

	/**
	 * Compares the key of the current state of the event to the same key of the initial state
	 * and determines whether the key is dirty or not. The state key is dirty if the value of
	 * the key in the current state and the value of the key in the initial state does not match.
	 *
	 * @since 6.0.0
	 *
	 * @param {string} key Key in the state.
	 *
	 * @return {boolean} Whether the key of the event state is dirty or not.
	 */
	obj.isStateKeyDirty = function ( key ) {
		// If initial state does not have key, return false.
		if ( ! Object.prototype.hasOwnProperty.call( obj.initialState, key ) ) {
			return false;
		}

		const currentState = obj.getCurrentState();

		return obj.initialState[ key ] !== currentState[ key ];
	};

	/**
	 * Compares the keys of the current state of the event to the same keys of the initial state
	 * and determines whether the keys are dirty or not. The state keys are dirty if all the values
	 * of the keys in the current state and all the values of the keys in the initial state do not
	 * match.
	 *
	 * @since 6.0.0
	 *
	 * @param {array} keys An array of strings representing the keys in the state.
	 *
	 * @return {boolean} Whether all the provided keys of the event state are dirty or not.
	 */
	obj.isAllStateKeysDirty = function ( keys ) {
		// If `keys` is not an array, nothing to check, return false.
		if ( ! Array.isArray( keys ) ) {
			return false;
		}

		const currentState = obj.getCurrentState();

		/**
		 * Initially `isDirty` is true. Loop through initial state and compare to current state.
		 * If one comparison between states is found to be the same, set `isDirty` to false.
		 */
		const isDirty = keys.reduce( function ( dirty, key ) {
			// If `dirty` is `false`, no need to check, just return the value.
			if ( ! dirty ) {
				return dirty;
			}

			// If initial state does not have key, return current value of `dirty`.
			if ( ! Object.prototype.hasOwnProperty.call( obj.initialState, key ) ) {
				return dirty;
			}

			return obj.initialState[ key ] !== currentState[ key ];
		}, true );


		return isDirty;
	};

	/**
	 * Compares the keys of the current state of the event to the same keys of the initial state
	 * and determines whether the keys are dirty or not. The state keys are dirty if at least one
	 * of the values of the keys in the current state and the values of the keys in the initial
	 * state do not match.
	 *
	 * @since 6.0.0
	 *
	 * @param {array} keys An array of strings representing the keys in the state.
	 *
	 * @return {boolean} Whether at least one of the provided keys of the event state is dirty or not.
	 */
	obj.isOneOfStateKeysDirty = function ( keys ) {
		// If `keys` is not an array, nothing to check, return false.
		if ( ! Array.isArray( keys ) ) {
			return false;
		}

		const currentState = obj.getCurrentState();

		/**
		 * Initially `isDirty` is false. Loop through initial state and compare to current state.
		 * If a difference in state is found, set `isDirty` to true.
		 */
		const isDirty = keys.reduce( function ( dirty, key ) {
			// If `dirty` is `true`, no need to check, just return the value.
			if ( dirty ) {
				return dirty;
			}

			// If initial state does not have key, return current value of `dirty`.
			if ( ! Object.prototype.hasOwnProperty.call( obj.initialState, key ) ) {
				return dirty;
			}

			return obj.initialState[ key ] !== currentState[ key ];
		}, false );


		return isDirty;
	};

	/**
	 * Compares the current state of the event to the initial state and determines whether the
	 * event state is dirty or not. The event state is dirty if the current state and the initial
	 * state do not match.
	 *
	 * @since 6.0.0
	 *
	 * @return {boolean} Whether the event state is dirty or not.
	 */
	obj.isEventStateDirty = function () {
		const currentState = obj.getCurrentState();

		/**
		 * Initially `isDirty` is false. Loop through initial state and compare to current state.
		 * If a difference in state is found, set `isDirty` to true.
		 */
		const isDirty = Object.keys( obj.initialState ).reduce( function ( dirty, key ) {
			// If `dirty` is `true`, no need to check, just return the value.
			if ( dirty ) {
				return dirty;
			}

			return obj.initialState[ key ] !== currentState[ key ];
		}, false );

		return isDirty;
	};

	/**
	 * Sets up the initial state of the event.
	 *
	 * @since 6.0.0
	 *
	 * @return {void}
	 */
	obj.setupInitialState = function () {
		obj.initialState = obj.getCurrentState();
	};

	/**
	 * Initializes the classic editor events state JS.
	 *
	 * @since 6.0.0
	 *
	 * @return {void}
	 */
	obj.ready = function () {
		$document.on( 'TECClassicEditorEventsReady', obj.setupInitialState );
	};

	$( obj.ready );
} )( jQuery, tec.classicEditorEventsState );
