/**
 * @file The core file for the pro events calendar plugin javascript.
 * This file must load on all front facing events pages and be the first file loaded after tribe-events.js.
 * @version 3.0
 */

if ( Object.prototype.hasOwnProperty.call( window, 'tribe_ev' ) ) {

	/**
	 * @namespace tribe_ev
	 * @desc tribe_ev.geoloc namespace stores all google maps data used in both map view and for events wide geo search.
	 */

	tribe_ev.geoloc = {
		map     : [],
		geocoder: [],
		geocodes: [],
		bounds  : [],
		markers : [],
		refine  : false
	};
}

(function( window, document, $, te, tf, tg, ts, tt, dbug ) {

	/*
	 * $    = jQuery
	 * td   = tribe_ev.data
	 * te   = tribe_ev.events
	 * tf   = tribe_ev.fn
	 * ts   = tribe_ev.state
	 * tt   = tribe_ev.tests
	 * dbug = tribe_debug
	 */

	$.extend( tribe_ev.fn, {

		/**
		 * @function tribe_ev.fn.has_address
		 * @desc tribe_ev.fn.has_address
		 * @param {String} val The value to compare against the array.
		 * @param {Array} geocodes Tests for an immediate duplicate in the geocodes array.
		 * @returns {Boolean} Returns true if a duplicate is found.
		 */

		has_address: function( val, geocodes ) {
			for ( var i = 0; i < geocodes.length; i++ ) {
				if ( geocodes[i].formatted_address == val ) { // eslint-disable-line eqeqeq
					return true;
				}
			}
			return false;
		},

		/**
		 * @function tribe_ev.fn.pre_ajax
		 * @desc tribe_ev.fn.pre_ajax allows for functions to be executed before ajax begins.
		 * @param {Function} callback The callback function, expected to be an ajax function for one of our views.
		 */

		pre_ajax: function( callback ) {
			if ( $( '#tribe-bar-geoloc' ).length ) {
				var val = $( '#tribe-bar-geoloc' ).val();
				if ( val.length ) {
					tf.process_geocoding( val, function( results ) {
						tg.geocodes = results;
						if ( tg.geocodes.length > 1 && !tf.has_address( val, tg.geocodes ) ) {
							tf.print_geo_options();
						}
						else {
							var lat = results[0].geometry.location.lat();
							var lng = results[0].geometry.location.lng();
							if ( lat ) {
								$( '#tribe-bar-geoloc-lat' ).val( lat );
							}

							if ( lng ) {
								$( '#tribe-bar-geoloc-lng' ).val( lng );
							}

							if ( callback && typeof( callback ) === "function" ) {
								if ( $( "#tribe_events_filter_item_geofence" ).length ) {
									$( "#tribe_events_filter_item_geofence" ).show();
								}
								callback();
							}
						}
					} );
				}
				else {
					$( '#tribe-bar-geoloc-lat, #tribe-bar-geoloc-lng' ).val( '' );
					if ( callback && typeof( callback ) === "function" ) {
						if ( $( "#tribe_events_filter_item_geofence" ).length ) {
							$( '#tribe_events_filter_item_geofence input' ).prop( 'checked', false );
							$( "#tribe_events_filter_item_geofence" )
								.hide()
								.find( 'select' )
								.prop( 'selectedIndex', 0 );
						}
						callback();
					}
				}
			}
			else {

				if ( callback && typeof( callback ) === "function" ) {
					callback();
				}
			}
		},

		/* eslint-disable max-len */
		/**
		 * @function tribe_ev.fn.print_geo_options
		 * @desc tribe_ev.fn.print_geo_options prints out the geolocation options returned by google maps if a geo search term requires refinement.
		 */
		/* eslint-enable max-len */

		print_geo_options: function() {
			$( "#tribe-geo-links" ).empty();
			$( "#tribe-geo-options" ).show();
			var dupe_test = [];
			tg.refine = true;
			for ( var i = 0; i < tg.geocodes.length; i++ ) {
				var address = tg.geocodes[i].formatted_address;
				if ( !dupe_test[address] ) {
					dupe_test[address] = true;
					$( "<a/>" )
						.text( address )
						.attr( "href", "#" )
						.addClass( 'tribe-geo-option-link' )
						.data( 'index', i )
						.appendTo( "#tribe-geo-links" );
					if ( tt.map_view() ) {
						tf.map_add_marker(
							tg.geocodes[i].geometry.location.lat(),
							tg.geocodes[i].geometry.location.lng(),
							address
						);
					}
				}
			}
			tg.refine = false;
		},

		/* eslint-disable max-len */
		/**
		 * @function tribe_ev.fn.pro_tooltips
		 * @desc tribe_ev.fn.pro_tooltips supplies additional tooltip functions for view use on top of the ones defined in core, especially for week view.
		 */
		/* eslint-enable max-len */

		pro_tooltips: function() {
			var $container = $( '#tribe-events' ),
				$body = $( 'body' ),
				is_week_view = $container.hasClass( 'view-week' ) || $body.hasClass( 'tribe-events-week' );

			$container.on( 'mouseenter', 'div[id*="tribe-events-event-"], div[id*="tribe-events-daynum-"]:has(a), div.event-is-recurring', function() { // eslint-disable-line max-len

				var bottomPad = 0;
				var $this = $( this );

				if ( is_week_view ) {

					if ( $this.tribe_has_attr( 'data-tribejson' ) ) {

						if ( ! $this.parents( '.tribe-grid-allday' ).length ) {

							var $tip = $this.find( '.tribe-events-tooltip' );

							if ( ! $tip.length ) {
								var data = $this.data( 'tribejson' );
								var tooltip_template = $this.hasClass( 'tribe-event-featured' )
									? 'tribe_tmpl_tooltip_featured'
									: 'tribe_tmpl_tooltip';

								$this.append( tribe_tmpl( tooltip_template, data ) );

								$tip = $this.find( '.tribe-events-tooltip' );
							}

							var $wrapper = $( '.tribe-week-grid-wrapper .scroller-content' );
							var $parent = $this.parent();
							var $container = $parent.parent();

							var pwidth = Math.ceil( $container.width() );
							var cwidth = Math.ceil( $this.width() );
							var twidth = Math.ceil( $tip.outerWidth() );
							var gheight = $wrapper.height();

							var scroll = $wrapper.scrollTop();
							var coffset = $parent.position();
							var poffset = $this.position();
							var ptop = Math.ceil( poffset.top );
							var toffset = scroll - ptop;

							var isright = $parent.hasClass( 'tribe-events-right' );
							var wcheck;
							var theight;
							var available;
							var cssmap = {};

							if ( !$tip.hasClass( 'hovered' ) ) {
								$tip.data( 'ow', twidth ).addClass( 'hovered' );
							}

							if ( isright ) {
								wcheck = Math.ceil( coffset.left ) - 20;
							} else {
								wcheck = pwidth - cwidth - Math.ceil( coffset.left );
							}

							if ( twidth >= wcheck ) {
								twidth = wcheck;
							} else if ( $tip.data( 'ow' ) > wcheck ) {
								twidth = wcheck;
							} else {
								twidth = $tip.data( 'ow' );
							}

							if ( isright ) {
								cssmap = { "right": cwidth + 20, "bottom": "auto", "width": twidth + "px" };
							} else {
								cssmap = { "left": cwidth + 20, "bottom": "auto", "width": twidth + "px" };
							}

							$tip.css( cssmap );

							theight = $tip.height();

							if ( toffset >= 0 ) {
								toffset = toffset + 5;
							} else {
								available = toffset + gheight;
								if ( theight > available ) {
									toffset = available - theight - 8;
								} else {
									toffset = 5;
								}
							}

							$tip.css( "top", toffset ).show();

						} else {
							var $tip = $this.find( '.tribe-events-tooltip' ); // eslint-disable-line no-redeclare

							if ( !$tip.length ) {
								var data = $this.data( 'tribejson' ); // eslint-disable-line no-redeclare
								var tooltip_template = $this.hasClass( 'tribe-event-featured' ) // eslint-disable-line no-redeclare,max-len
									? 'tribe_tmpl_tooltip_featured'
									: 'tribe_tmpl_tooltip';

								$this.append( tribe_tmpl( tooltip_template, data ) );

								$tip = $this.find( '.tribe-events-tooltip' );
							}

							bottomPad = $this.outerHeight() + 6;
							$tip.css( 'bottom', bottomPad ).show();
						}
					}
				}
			} );
		},

		/**
		 * @function tribe_ev.fn.process_geocoding
		 * @desc tribe_ev.fn.process_geocoding middle mans the geolocation request to google with its callback.
		 * @param {String} location The location value, generally from the event bar.
		 * @param {Function} callback The callback function.
		 */

		process_geocoding: function( location, callback ) {

			var request = {
				address: location,
				bounds : new google.maps.LatLngBounds(
					new google.maps.LatLng( TribeEventsPro.geocenter.min_lat, TribeEventsPro.geocenter.min_lng ), // eslint-disable-line max-len
					new google.maps.LatLng( TribeEventsPro.geocenter.max_lat, TribeEventsPro.geocenter.max_lng ) // eslint-disable-line max-len
				)
			};

			tg.geocoder.geocode( request, function( results, status ) {
				if ( status == google.maps.GeocoderStatus.OK ) { // eslint-disable-line eqeqeq
					callback( results );
					return results;
				}


				if ( status == google.maps.GeocoderStatus.ZERO_RESULTS ) { // eslint-disable-line eqeqeq
					if ( GeoLoc.map_view ) {
						spin_end(); // eslint-disable-line no-undef
					}
					return status;
				}

				return status;
			} );
		},

		/* eslint-disable max-len */
		/**
		 * @function tribe_ev.fn.set_recurrence
		 * @desc tribe_ev.fn.set_recurrence uses local storage to store the user front end setting for the hiding of subsequent recurrences of a recurring event.
		 * @param {Boolean} recurrence_on Bool sent to set appropriate recurrence storage option.
		 */
		/* eslint-enable max-len */

		set_recurrence: function( recurrence_on ) {
			if ( recurrence_on ) {
				ts.recurrence = true;
				if ( tribe_storage ) {
					tribe_storage.setItem( 'tribeHideRecurrence', '1' );
				}
			}
			else {
				ts.recurrence = false;
				if ( tribe_storage ) {
					tribe_storage.setItem( 'tribeHideRecurrence', '0' );
				}
			}
		}
	} );

	$.extend( tribe_ev.tests, {

		/* eslint-disable max-len */
		/**
		 * @function tribe_ev.tests.hide_recurrence
		 * @desc tribe_ev.tests.hide_recurrence uses local storage to store the user front end setting for the hiding of subsequent recurrences of a recurring event.
		 */
		/* eslint-enable max-len */

		hide_recurrence: function() {
			return $( '#tribeHideRecurrence:checked' ).length ? true : false;
		}
	} );

	$( function() {

		if ( $( '.tribe-bar-geoloc-filter' ).length ) {
			$( ".tribe-bar-geoloc-filter" )
				.append( '<div id="tribe-geo-options"><div id="tribe-geo-links"></div></div>' );
		}

		var $tribe_container = $( '#tribe-events' ),
			$geo_bar_input = $( '#tribe-bar-geoloc' ),
			$geo_options = $( "#tribe-geo-options" ),
			recurrence_on = false;

		tf.pro_tooltips();

		if ( tt.hide_recurrence() ) {
			tf.set_recurrence( true );
		}

		ts.recurrence = tt.hide_recurrence();

		$tribe_container.on( 'click', '#tribeHideRecurrence', function() {
			ts.popping = false;
			ts.do_string = true;
			ts.paged = 1;
			recurrence_on = ($( this ).is( ':checked' ) ? true : false);

			tf.set_recurrence( recurrence_on );

			/**
			 * DEPRECATED: tribe_ev_updatingRecurrence and tribe_ev_runAjax have been deprecated in 4.0.
			 *             Use updating-recurrence.tribe and run-ajax.tribe instead
			 */
			$( te ).trigger( 'tribe_ev_updatingRecurrence' ).trigger( 'tribe_ev_runAjax' );
			$( te ).trigger( 'updating-recurrence.tribe' ).trigger( 'run-ajax.tribe' );
		} );

		$( te ).on( 'pre-collect-bar-params.tribe', function() {
			if ( $geo_bar_input.length ) {
				var tribe_map_val = $geo_bar_input.val();
				if ( !tribe_map_val.length ) {
					$( '#tribe-bar-geoloc-lat, #tribe-bar-geoloc-lng' ).val( '' );
				}
				else {
					if ( ts.view_target === 'map' ) {
						ts.url_params['action'] = 'tribe_geosearch';
					}
				}
			}

			if ( tribe_storage ) {
				if (
					tribe_storage.getItem( 'tribeHideRecurrence' ) === '1' &&
					( ts.view_target !== 'month' && ts.view_target !== 'week' )
				) {
					ts.url_params['tribeHideRecurrence'] = '1';
				}
			}
		} );

		if ( ! tt.map_view() ) {

			if ( $geo_options.length ) {

				$tribe_container.on( 'click', '.tribe-geo-option-link', function( e ) {
					e.preventDefault();
					e.stopPropagation();
					var $this = $( this );

					$( '.tribe-geo-option-link' ).removeClass( 'tribe-option-loaded' );
					$this.addClass( 'tribe-option-loaded' );

					$geo_bar_input.val( $this.text() );

					$( '#tribe-bar-geoloc-lat' ).val( tg.geocodes[$this.data( 'index' )].geometry.location.lat() ); // eslint-disable-line max-len
					$( '#tribe-bar-geoloc-lng' ).val( tg.geocodes[$this.data( 'index' )].geometry.location.lng() ); // eslint-disable-line max-len

					tf.pre_ajax( function() {
						/**
						 * DEPRECATED: tribe_ev_runAjax has been deprecated in 4.0. Use run-ajax.tribe instead
						 */
						$( te ).trigger( 'tribe_ev_runAjax' );
						$( te ).trigger( 'run-ajax.tribe' );
						$geo_options.hide();
					} );

				} );

				$( document ).on( 'click', function( e ) { // eslint-disable-line no-unused-vars
					$geo_options.hide();
				} );

			}

			tf.snap( '#tribe-geo-wrapper', '#tribe-geo-wrapper', '#tribe-events-footer .tribe-events-nav-previous a, #tribe-events-footer .tribe-events-nav-next a' ); // eslint-disable-line max-len

		}

		$( '#wp-toolbar' ).on( 'click', '.tribe-split-single a, .tribe-split-all a', function() {
			var message = '';
			if ( $( this ).parent().hasClass( 'tribe-split-all' ) ) {
				message = TribeEventsPro.recurrence.splitAllMessage;
			}
			else {
				message = TribeEventsPro.recurrence.splitSingleMessage;
			}
			if ( !window.confirm( message ) ) {
				return false;
			}
		} );

		/**
		 * Transform from a string into an object with key / value pairs. Transforms string from type:
		 * key=value&ket_2=value2  into an object of type { key: value, key_2: value2 }
		 *
		 * @since 4.4.31
		 *
		 * @param params
		 * @returns {object}
		 */
		function deserialize( params ) {
			return params
				.split( '&' )
				.map( function ( item ) {
					return item.split( '=' );
				} )
				.filter( function ( item ) {
					return item.length === 2;
				} ).reduce( function ( obj, current ) {
					// If the object key already exists
					if ( obj[ current[ 0 ] ] ) {
						// Add multi value fields to an array
						if( ! obj[ current[ 0 ] ].push ) {
							obj[ current[ 0 ] ] = [ obj[ current[ 0 ] ] ];
						}
						obj[ current[ 0 ] ].push( current[ 1 ] );
					} else {
						// Else, assign obj.key = value
						obj[ current[ 0 ] ] = current[ 1 ];
					}
					return obj;
				}, {} );
		}

		/**
		 * Add a new parameter before the data is serialized and the request to be fired.
		 *
		 * @since 4.2.26
		 */
		var isRecurrence = $tribe_container.data( 'recurrence-list' ) === 1;

		// only deserialize on /all/ page to prevent conflicts with shortcode navigation
		if ( isRecurrence ) {
			$( te ).on( 'tribe_ev_ajaxStart', function () {
				if ( typeof ts.params === 'string' ) {
					ts.params = deserialize( decodeURIComponent( ts.params.replace( /\+/g, '%20' ) ) );
				}
				ts.params.is_recurrence_list = isRecurrence;
				var value = $tribe_container.data( 'tribe_post_parent' );
				if ( value ) {
					ts.params.tribe_post_parent = value;
				}
				ts.params = $.param( ts.params );

			} );
		}
		// @ifdef DEBUG
		dbug && tec_debug.info( 'TEC Debug: tribe-events-pro.js successfully loaded' );
		// @endif

	} );

})( window, document, jQuery, tribe_ev.events, tribe_ev.fn, tribe_ev.geoloc, tribe_ev.state, tribe_ev.tests, tribe_debug ); // eslint-disable-line max-len
