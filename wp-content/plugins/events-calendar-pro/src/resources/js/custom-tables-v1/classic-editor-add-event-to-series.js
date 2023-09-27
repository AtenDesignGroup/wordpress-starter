/**
 * Makes sure we have all the required levels on the TEC Object
 *
 * @since 6.0.0
 *
 * @type {PlainObject}
 */
window.tec = window.tec || {};

/**
 * Configures Add Event to Series Object in the Global TEC variable
 *
 * @since 6.0.0
 *
 * @type {PlainObject}
 */
tec.addEventToSeries = tec.addEventToSeries || {};

/**
 * Handles the JavaScript for adding an event to a series.
 *
 * @since 6.0.0
 *
 * @param {jQuery} $ jQuery.
 * @param {PlainObject} obj tec.addEventToSeries object.
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
		seriesToEventsSelect: '#_tec_relationship_series_to_events',
		resultTemplate: '#tec-events-pro-series__result-template',
		resultLabelTitle: '.tec-events-pro-series__result-label-title',
		resultLabelCountEvents: '.tec-events-pro-series__result-label-count-events',
		resultLabelStatus: '.tec-events-pro-series__result-label-status',
		resultDate: '.tec-events-pro-series__result-date',
		selection: '.select2-selection__choice',
		selectionRemove: '.select2-selection__choice__remove',
		selectionTemplate: '#tec-events-pro-series__selection-template',
		selectionTitle: '.tec-events-pro-series__selection-title',
		selectionTitleText: '.tec-events-pro-series__selection-title-text',
		selectionCountEvents: '.tec-events-pro-series__selection-count-events',
		selectionsLabel: '.tec-events-pro-series__selections-label',
		selectionsList: '.tec-events-pro-series__selections-list',
		searchField: '.select2-search__field',
	};

	/**
	 * Classes used for adding to or removing from elements.
	 *
	 * @since 6.0.0
	 *
	 * @type {PlainObject}
	 */
	obj.classes = {
		hidden: 'hidden',
	}

	/**
	 * Elements used in this module.
	 *
	 * @since 6.0.0
	 *
	 * @type {PlainObject}
	 */
	obj.el = {
		$instance: null,
	};

	/**
	 * Formats the result template for select2.
	 *
	 * @since 6.0.0
	 *
	 * @param {object} result Result object.
	 */
	obj.formatResultTemplate = function ( result ) {
		if ( ! result.element ) {
			return result.text;
		}

		if ( result.selected ) {
			return;
		}

		var template = $( obj.selectors.resultTemplate ).text();
		var $template = $( template );

		$template
			.find( obj.selectors.resultLabelTitle )
			.text( result.text );

		var $resultLabelCountEvents = $template.find( obj.selectors.resultLabelCountEvents )
		var $resultDate = $template.find( obj.selectors.resultDate );

		if ( '1' === result.element.dataset.recurring ) {
			$resultLabelCountEvents.text( result.element.dataset.recurrenceCount );
			$resultDate.text( result.element.dataset.recurrenceFirstStartDate + ' - ' + result.element.dataset.recurrenceLastEndDate );
		} else {
			$template.find( 'svg' ).remove();
			$resultLabelCountEvents.remove();
			$resultDate.text( result.element.dataset.startDate );
		}

		var $resultLabelStatus = $template.find( obj.selectors.resultLabelStatus )

		if ( $.inArray( result.element.dataset.status, [ 'draft', 'pending' ] ) >= 0 ) {
			$resultLabelStatus.text( '– ' + result.element.dataset.statusLabel );
		} else {
			$resultLabelStatus.remove();
		}

		return $template;
	};

	/**
	 * Formats the selection template for select2.
	 *
	 * @since 6.0.0
	 *
	 * @param {object} selection Selection object.
	 */
	obj.formatSelectionTemplate = function ( selection ) {
		if ( ! selection.element ) {
			return selection.text;
		}

		var template = $( obj.selectors.selectionTemplate ).text();
		var $template = $( template );

		$template.find( obj.selectors.selectionTitle ).text( selection.text );

		var $selectionCountEvents = $template.find( obj.selectors.selectionCountEvents );

		if ( '1' === selection.element.dataset.recurring ) {
			$selectionCountEvents.text( selection.element.dataset.recurrenceCount );
			selection.title = selection.element.dataset.recurrenceFirstStartDate + ' - ' + selection.element.dataset.recurrenceLastStartDate;
		} else {
			$template.find( 'svg' ).remove();
			$selectionCountEvents.remove();
			selection.title = selection.element.dataset.startDate;
		}

		return $template;
	};

	/**
	 * Move selections to another container.
	 *
	 * @since 6.0.0
	 */
	obj.moveSelections = function () {
		var $container = obj.el.$instance.parent();
		var $list = $container.find( obj.selectors.selectionsList );

		$list.empty();

		var $selections = $container.find( obj.selectors.selection );

		$selections.each( function ( index, selection ) {
			var $selection = $( selection ).addClass( obj.classes.hidden );
			$selection
				.clone()
				.appendTo( $list )
				.find( obj.selectors.selectionRemove ).on( 'click', function () {
					$selection.find( obj.selectors.selectionRemove ).trigger( 'click' );
				} );
		} );

		var $selectionsLabel = $container.find( obj.selectors.selectionsLabel );

		if ( $selections.length ) {
			$selectionsLabel.removeClass( obj.classes.hidden );
		} else {
			$selectionsLabel.addClass( obj.classes.hidden );
		}
	};

	/**
	 * Set placeholder for select2 dropdown search field.
	 *
	 * @since 6.0.0
	 */
	obj.setPlaceholder = function () {
		var placeholder = obj.el.$instance.data( 'placeholder' );
		obj.el.$instance
			.next()
			.find( obj.selectors.searchField )
			.attr( 'placeholder', placeholder );
	};

	/**
	 * Handles select event for select2 dropdown.
	 *
	 * @since 6.0.0
	 */
	obj.handleSelect = function () {
		obj.moveSelections();
		obj.setPlaceholder();
		obj.el.$instance.select2TEC( 'close' );
	};

	/**
	 * Handles unselect event for select2 dropdown.
	 *
	 * @since 6.0.0
	 */
	obj.handleUnselect = function () {
		obj.moveSelections();
		obj.setPlaceholder();
	};

	/**
	 * Handles unselecting event for select2 dropdown.
	 *
	 * @since 6.0.0
	 */
	obj.handleUnselecting = function () {
		obj.el.$instance.data( 'unselecting.tecEventsPro', true );
	};

	/**
	 * Handles opening event for select2 dropdown.
	 *
	 * @since 6.0.0
	 *
	 * @param {Event} event
	 */
	obj.handleOpening = function ( event ) {
		if ( ! obj.el.$instance.data( 'unselecting.tecEventsPro' ) ) {
			return;
		}

		obj.el.$instance.removeData( 'unselecting.tecEventsPro' );
		event.preventDefault();
	};

	/**
	 * Binds events for the dropdown.
	 *
	 * @since 6.0.0
	 */
	obj.bindEvents = function () {
		obj.el.$instance
			.on( 'select2:select', obj.handleSelect )
			.on( 'select2:unselect', obj.handleUnselect )
			.on( 'select2:unselecting', obj.handleUnselecting )
			.on( 'select2:opening', obj.handleOpening );
	};

	/**
	 * Set up the dropdown.
	 *
	 * @since 6.0.0
	 */
	obj.setupDropdown = function () {
		var data = obj.el.$instance.data( 'dropdown' );
		data.closeOnSelect = true;
		data.templateResult = obj.formatResultTemplate;
		data.templateSelection = obj.formatSelectionTemplate;
		obj.el.$instance.data( 'dropdown', data );

		obj.el.$instance.select2TEC( data );
	};

	/**
	 * Initializes the add event to series JS.
	 *
	 * @since 6.0.0
	 */
	obj.ready = function () {
		obj.el.$instance = $( obj.selectors.seriesToEventsSelect );

		if ( ! obj.el.$instance.length ) {
			return;
		}

		obj.setupDropdown();
		obj.bindEvents();
		obj.moveSelections();
		obj.setPlaceholder();
	};

	// Run when document is ready.
	$( obj.ready );
} )( jQuery, tec.addEventToSeries );
