/**
 * Makes sure we have all the required levels on the TEC Object.
 *
 * @since 6.0.0
 *
 * @type {PlainObject}
 */
window.tec = window.tec || {};

/**
 * Configures Classic Editor Events Event Date Object in the Global TEC variable.
 *
 * @since 6.0.0
 *
 * @type {PlainObject}
 */
tec.classicEditorEvents = tec.classicEditorEvents || {};
tec.classicEditorEvents.eventDate = tec.classicEditorEvents.eventDate || {};

/**
 * Handles the event date cases of recurrence and exclusions.
 *
 * @since 6.0.0
 *
 * @param {jQuery} $ A reference to the global jQuery instance.
 * @param {PlainObject} obj tec.classicEditorEvents.eventDate object.
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
		eventStartDate: '#EventStartDate',
	};

	/**
	 * Elements used in the application.
	 */
	obj.el = {
		$eventStart: null,
	};

	/**
	 * Cardinal to ordinal map.
	 *
	 * @since 6.0.0
	 *
	 * @type {PlainObject}
	 */
	obj.cardinalToOrdinalMap = {
		1: 'First',
		2: 'Second',
		3: 'Third',
		4: 'Fourth',
		5: 'Fifth',
	};

	/**
	 * ISO weekday to day name (in English) map.
	 *
	 * @since 6.0.0
	 *
	 * @type {PlainObject}
	 */
	obj.isoWeekdayToDayNameMap = {
		1: 'Monday',
		2: 'Tuesday',
		3: 'Wednesday',
		4: 'Thursday',
		5: 'Friday',
		6: 'Saturday',
		7: 'Sunday',
	};

	/**
	 * Event start data.
	 *
	 * @since 6.0.0
	 *
	 * @type {PlainObject}
	 */
	obj.eventStart = {
		moment: null,
		prevMoment: null,
	};

	/**
	 * Get the event start weekday name.
	 * Possible return values are `Monday`, `Tuesday`, `Wednesday`, `Thursday`,
	 * `Friday`, `Saturday`, or `Sunday`.
	 *
	 * @since 6.0.0
	 *
	 * @return {string}
	 */
	obj.getEventStartWeekdayName = function () {
		const weekday = obj.eventStart.moment.isoWeekday();
		return obj.isoWeekdayToDayNameMap[ weekday ];
	};

	/**
	 * Get the event start weekday ordinal value.
	 * Possible return values are `first`, `second`, `third`, `fourth`, or `fifth`.
	 *
	 * @since 6.0.0
	 *
	 * @return {string}
	 */
	obj.getEventStartWeekdayOrdinalValue = function () {
		const date = obj.eventStart.moment.date();
		const cardinalWeek = Math.floor( ( date - 1 ) / 7 ) + 1;
		return obj.cardinalToOrdinalMap[ cardinalWeek ];
	};

	/**
	 * Determines whether the event start weekday is the last of the month or not.
	 *
	 * @since 6.0.0
	 *
	 * @return {boolean}
	 */
	obj.isEventStartWeekdayLast = function () {
		const daysInMonth = obj.eventStart.moment.daysInMonth();
		const date = obj.eventStart.moment.date();
		return daysInMonth - date < 7;
	};

	/**
	 * Determines whether the event start date is the last day of the month or not.
	 *
	 * @since 6.0.0
	 *
	 * @return {boolean}
	 */
	obj.isEventStartDateLastOfMonth = function () {
		const daysInMonth = obj.eventStart.moment.daysInMonth();
		const date = obj.eventStart.moment.date();
		return daysInMonth === date;
	};

	/**
	 * Check whether the event start month changed or not.
	 *
	 * @since 6.0.0
	 *
	 * @return {boolean}
	 */
	obj.didEventStartMonthChange = function () {
		// Initial pass, no prev moment set.
		if ( ! obj.eventStart.prevMoment ) {
			return false;
		}

		return obj.eventStart.moment.month() !== obj.eventStart.prevMoment.month();
	};

	/**
	 * Set current event date.
	 *
	 * @since 6.0.0
	 *
	 * @return {void}
	 */
	obj.setCurrentEventDate = function () {
		if ( ! obj.el.eventStart ) {
			obj.el.$eventStart = $document.find( obj.selectors.eventStartDate );
		}

		obj.eventStart.moment = moment( obj.el.$eventStart.val(), tribe_events_pro_admin.recurrence.date_format );
	};

	/**
	 * Set previous event date to current event date.
	 *
	 * @since 6.0.0
	 *
	 * @return {void}
	 */
	obj.setPreviousEventDate = function () {
		obj.eventStart.prevMoment = obj.eventStart.moment;
	};
} )( jQuery, tec.classicEditorEvents.eventDate );
