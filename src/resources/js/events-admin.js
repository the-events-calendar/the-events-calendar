/*
 * Date Format 1.2.3
 * (c) 2007-2009 Steven Levithan <stevenlevithan.com>
 * MIT license
 *
 * Includes enhancements by Scott Trenda <scott.trenda.net>
 * and Kris Kowal <cixar.com/~kris.kowal/>
 *
 * Accepts a date, a mask, or a date and a mask.
 * Returns a formatted version of the given date.
 * The date defaults to the current date/time.
 * The mask defaults to dateFormat.masks.default.
 * todo: now used in multiple places, lets consolidate. Also, should events-admin really be powering community fe form?
 */

var tribeDateFormat = function() {
	var token = /d{1,4}|m{1,4}|yy(?:yy)?|([HhMsTt])\1?|[LloSZ]|"[^"]*"|'[^']*'/g,
		timezone = /\b(?:[PMCEA][SDP]T|(?:Pacific|Mountain|Central|Eastern|Atlantic) (?:Standard|Daylight|Prevailing) Time|(?:GMT|UTC)(?:[-+]\d{4})?)\b/g,
		timezoneClip = /[^-+\dA-Z]/g,
		pad = function( val, len ) {
			val = String( val );
			len = len || 2;
			while ( val.length < len ) {
				val = "0" + val;
			}
			return val;
		};

	// Regexes and supporting functions are cached through closure
	return function( date, mask, utc ) {
		var dF = tribeDateFormat;

		// You can't provide utc if you skip other args (use the "UTC:" mask prefix)
		if ( arguments.length == 1 && Object.prototype.toString.call( date ) == "[object String]" && !/\d/.test( date ) ) {
			mask = date;
			date = undefined;
		}

		if ( typeof date === 'string' ) {
			date = date.replace( /-/g, "/" );
		}

		// Passing date through Date applies Date.parse, if necessary
		date = date ? new Date( date ) : new Date;
		if ( isNaN( date ) ) {
			return;
		}

		mask = String( dF.masks[mask] || mask || dF.masks["default"] );

		// Allow setting the utc argument via the mask
		if ( mask.slice( 0, 4 ) == "UTC:" ) {
			mask = mask.slice( 4 );
			utc = true;
		}

		var _ = utc ? "getUTC" : "get",
			d = date[_ + "Date"](),
			D = date[_ + "Day"](),
			m = date[_ + "Month"](),
			y = date[_ + "FullYear"](),
			H = date[_ + "Hours"](),
			M = date[_ + "Minutes"](),
			s = date[_ + "Seconds"](),
			L = date[_ + "Milliseconds"](),
			o = utc ? 0 : date.getTimezoneOffset(),
			flags = {
				d   : d,
				dd  : pad( d ),
				ddd : dF.i18n.dayNames[D],
				dddd: dF.i18n.dayNames[D + 7],
				m   : m + 1,
				mm  : pad( m + 1 ),
				mmm : dF.i18n.monthNames[m],
				mmmm: dF.i18n.monthNames[m + 12],
				yy  : String( y ).slice( 2 ),
				yyyy: y,
				h   : H % 12 || 12,
				hh  : pad( H % 12 || 12 ),
				H   : H,
				HH  : pad( H ),
				M   : M,
				MM  : pad( M ),
				s   : s,
				ss  : pad( s ),
				l   : pad( L, 3 ),
				L   : pad( L > 99 ? Math.round( L / 10 ) : L ),
				t   : H < 12 ? "a" : "p",
				tt  : H < 12 ? "am" : "pm",
				T   : H < 12 ? "A" : "P",
				TT  : H < 12 ? "AM" : "PM",
				Z   : utc ? "UTC" : (String( date ).match( timezone ) || [""]).pop().replace( timezoneClip, "" ),
				o   : (o > 0 ? "-" : "+") + pad( Math.floor( Math.abs( o ) / 60 ) * 100 + Math.abs( o ) % 60, 4 ),
				S   : ["th", "st", "nd", "rd"][d % 10 > 3 ? 0 : (d % 100 - d % 10 != 10) * d % 10]
			};

		return mask.replace( token, function( $0 ) {
			return $0 in flags ? flags[$0] : $0.slice( 1, $0.length - 1 );
		} );
	};
}();

tribeDateFormat.masks = {
	"default"        : "ddd mmm dd yyyy HH:MM:ss",
	"tribeQuery"     : "yyyy-mm-dd",
	"tribeMonthQuery": "yyyy-mm",
	"0"              : 'yyyy-mm-dd',
	"1"              : 'm/d/yyyy',
	"2"              : 'mm/dd/yyyy',
	"3"              : 'd/m/yyyy',
	"4"              : 'dd/mm/yyyy',
	"5"              : 'm-d-yyyy',
	"6"              : 'mm-dd-yyyy',
	"7"              : 'd-m-yyyy',
	"8"              : 'dd-mm-yyyy',
	"m0"             : 'yyyy-mm',
	"m1"             : 'm/yyyy',
	"m2"             : 'mm/yyyy',
	"m3"             : 'm/yyyy',
	"m4"             : 'mm/yyyy',
	"m5"             : 'm-yyyy',
	"m6"             : 'mm-yyyy',
	"m7"             : 'm-yyyy',
	"m8"             : 'mm-yyyy'

};

tribeDateFormat.i18n = {
	dayNames  : [
		"Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat",
		"Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"
	],
	monthNames: [
		"Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec",
		"January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"
	]
};

Date.prototype.format = function( mask, utc ) {
	return tribeDateFormat( this, mask, utc );
};

var tribe_datepicker_opts = {};

jQuery( document ).ready( function( $ ) {

	$( '.bumpdown-trigger' ).bumpdown();

	var $date_format      = $( '[data-datepicker_format]' ),
		$view_select      = $( '.tribe-field-dropdown_select2 select' ),
		viewCalLinkHTML   = $( '#view-calendar-link-div' ).html(),
		$template_select  = $( 'select[name="tribeEventsTemplate"]' ),
		$event_pickers    = $( '#tribe-event-datepickers' ),
		is_community_edit   = $( 'body' ).is( '.tribe_community_edit' ),
		datepicker_format = 0;

	// Modified from tribe_ev.data to match jQuery UI formatting.
	var datepicker_formats = {
		'main' : ['yy-mm-dd', 'm/d/yy', 'mm/dd/yy', 'd/m/yy', 'dd/mm/yy', 'm-d-yy', 'mm-dd-yy', 'd-m-yy', 'dd-mm-yy'],
		'month': ['yy-mm', 'm/yy', 'mm/yy', 'm/yy', 'mm/yy', 'm-yy', 'mm-yy', 'm-yy', 'mm-yy']
	};

	// Initialize Chosen and Select2.
	$( '.chosen, .tribe-field-dropdown_chosen select' ).chosen();
	$( '.select2' ).select2( {width: '250px'} );
	$view_select.select2( {width: '250px'} );

	// initialize the category hierarchy checkbox - scroll to closest checked checkbox
	$( '[data-wp-lists="list:tribe_events_cat"]' ).each( function() {
		var $list = $( this );
		var $first = $list.find( ':checkbox:checked' ).first();

		if ( ! $first.length ) {
			return;
		}

		var top_position = $list.find( ':checkbox' ).position().top;
		var checked_position = $first.position().top;

		$list.closest( '.tabs-panel' ).scrollTop( checked_position - top_position + 5 );
	} );

	// Grab HTML from hidden Calendar link and append to Header on Event Listing Page
	$( viewCalLinkHTML )
		.insertAfter( '.edit-php.post-type-tribe_events #wpbody-content .wrap h2:eq(0) a' );

	if ( $template_select.length && $template_select.val() === '' ) {

		var t_name = $template_select.find( "option:selected" ).text();

		$template_select
			.prev( '.select2-container' )
			.children()
			.children( 'span' )
			.text( t_name );
	}

	//not done by default on front end
	function get_datepicker_num_months() {
		return ( is_community_edit && $(window).width() < 768 ) ? 1 : 3;
	}


	var setup_linked_post_fields = function( post_type ) {
		var saved_template = $( document.getElementById( 'tmpl-tribe-select-' + post_type ) ).length ? wp.template( 'tribe-select-' + post_type ) : null;
		var create_template = $( document.getElementById( 'tmpl-tribe-create-' + post_type ) ).length ? wp.template( 'tribe-create-' + post_type ) : null;
		var section = $( document.getElementById( 'event_' + post_type ) );
		var rows = section.find( '.saved-linked-post' );

		section.on( 'click', '.tribe-add-post', function(e) {
			e.preventDefault();
			var dropdown = $({}), fields = $({});

			if ( saved_template ) {
				dropdown = $( saved_template({}) );
			}

			if ( dropdown.find( '.nosaved' ).length ) {
				var label = dropdown.find( 'label' );
				label.text( label.data( 'l10n-create-' + post_type ) );
				dropdown.find( '.nosaved' ).remove();
			}

			if ( create_template ) {
				fields = $( create_template({}) );
			}

			section.find( 'tfoot' ).before( fields );
			fields.prepend( dropdown );
			fields.find( '.chosen' ).chosen().trigger( 'change' );
		});

		section.on( 'change', '.linked-post-dropdown', toggle_linked_post_fields );

		/**
		 * Populates the linked post type fields with previously submitted data to
		 * give them sticky form qualities.
		 *
		 * @param fields
		 */
		function add_sticky_linked_post_data( post_type, container, fields ) {
			// Bail if expected global sticky data array is not set
			if ( 'undefined' === typeof window['tribe_sticky_' + post_type + '_fields'] || ! $.isArray( window['tribe_sticky_' + post_type + '_fields'] ) ) {
				return;
			}

			var $fields = $( fields );

			// bail if the fields are not about this container
			if ( $fields.filter( 'tr.linked-post.' + container ).length === 0 ) {
				return;
			}

			// The linked post type fields also need sticky field behaviour: populate
			// them if we've been provided with the necessary data to do so
			var sticky_data = window['tribe_sticky_' + post_type + '_fields'].shift();

			if ( 'object' === typeof sticky_data ) {
				for ( var key in sticky_data ) {
					// Check to see if we have a field of this name
					var $field = $( fields ).find( 'input[name="' + container + '[' + key + '][]"]' );

					if ( ! $field.length ) {
						continue;
					}

					// Set the value accordingly
					$field.val( sticky_data[ key ] );
				}
			}
		}

		rows.each( function () {
			var row = $( this );
			var group = row.closest( 'tbody' );
			var fields;

			if ( create_template ) {
				fields = $( create_template( {} ) ).find( 'tr' ); // we already have our tbody
			} else {
				fields = group.find( 'tr' ).slice( 2 );
			}

			var dropdown = row.find( '.linked-post-dropdown' );
			if ( dropdown.length ) {
				var value = dropdown.val();
				if ( 0 !== parseInt( value, 10 ) ) {
					//hide all fields, but those with not-linked class i.e. Google Map Settings
					fields.not( '.remain-visible' ).hide();
				}
			} else if ( row.find( '.nosaved' ).length ) {
				var label = row.find( 'label' );
				label.text( label.data( 'l10n-create-' + post_type ) );
				row.find( '.nosaved' ).remove();
			}

			// Populate the fields with any sticky data then add them to the page
			for ( var i in tribe_events_linked_posts.post_types ) {
				if ( ! tribe_events_linked_posts.post_types.hasOwnProperty( i ) ) {
					continue;
				}

				add_sticky_linked_post_data( i, tribe_events_linked_posts.post_types[ i ], fields );
			}

			group.append( fields );
		} );

		section.on( 'click', '.delete-linked-post-group', function(e) {
			e.preventDefault();
			var group = $(this).closest( 'tbody' );
			group.fadeOut( 500, function() { $(this).remove(); } );
		});

		var sortable_items = '> tbody';

		if ( ! $( 'body' ).hasClass( 'wp-admin' ) ) {
			sortable_items = 'table ' + sortable_items;
		}

		section.sortable({
			items: sortable_items,
			handle: '.move-linked-post-group',
			axis: 'y',
			delay: 100
		});

	};

	var toggle_linked_post_fields = function() {
		var dropdown           = $( this );
		var selected_id        = dropdown.val();
		var group              = dropdown.closest( 'tbody' );
		var edit_link          = group.find( '.edit-linked-post-link a' );
		var edit_link_base_url = edit_link.attr( 'data-admin-url' );

		if ( selected_id != '0' ) {
			group.find( '.linked-post' ).fadeOut().find( 'input' ).val( '' );
			edit_link.attr( 'href', edit_link_base_url + selected_id ).show();
		} else {
			group.find( '.linked-post' ).fadeIn();
			edit_link.hide();
		}
	};

	$( '.hide-if-js' )
		.hide();

	if ( typeof(TEC) !== 'undefined' ) {

		var _MS_PER_DAY = 1000 * 60 * 60 * 24;

		var date_format = 'yy-mm-dd';

		if ( $date_format.length && $date_format.attr( 'data-datepicker_format' ).length === 1 ) {
			datepicker_format = $date_format.attr( 'data-datepicker_format' );
			date_format = datepicker_formats.main[ datepicker_format ];
		}

		function date_diff_in_days( a, b ) {

			var utc1 = Date.UTC( a.getFullYear(), a.getMonth(), a.getDate() );
			var utc2 = Date.UTC( b.getFullYear(), b.getMonth(), b.getDate() );

			return Math.floor( (utc2 - utc1) / _MS_PER_DAY );
		}

		var startofweek = 0;

		if ( $event_pickers.length ) {
			startofweek = $event_pickers.data( 'startofweek' );
		}

		var $end_date = $( '#EventEndDate' );

		tribe_datepicker_opts = {
			dateFormat     : date_format,
			showAnim       : 'fadeIn',
			changeMonth    : true,
			changeYear     : true,
			numberOfMonths : get_datepicker_num_months(),
			firstDay       : startofweek,
			showButtonPanel: true,
			beforeShow     : function( element, object ) {
				object.input.datepicker( 'option', 'numberOfMonths', get_datepicker_num_months() );
				object.input.data( 'prevDate', object.input.datepicker( "getDate" ) );
			},
			onSelect       : function( selectedDate ) {
				var option = this.id == 'EventStartDate' ? 'minDate' : 'maxDate';
				var instance = $( this ).data( "datepicker" );
				var date = $.datepicker.parseDate( instance.settings.dateFormat || $.datepicker._defaults.dateFormat, selectedDate, instance.settings );

				if ( this.id === 'EventStartDate' ) {
					var startDate = $( '#EventStartDate' ).data( 'prevDate' );
					var dateDif = null == startDate ? 0 : date_diff_in_days( startDate, $end_date.datepicker( 'getDate' ) );
					var endDate = new Date( date.setDate( date.getDate() + dateDif ) );

					$end_date
						.datepicker( 'option', option, endDate )
						.datepicker( 'setDate', endDate );
				} else {

					dates
						.not( this )
						.not( '.tribe-no-end-date-update' )
						.datepicker( 'option', option, date );
				}

				// fire the change and blur handlers on the field
				$( this ).change();
				$( this ).blur();
			}
		};

		$.extend( tribe_datepicker_opts, TEC );

		var dates = $( '.tribe-datepicker' ).datepicker( tribe_datepicker_opts );
		var $all_day_check = $( '#allDayCheckbox' );
		var $tod_options = $( ".timeofdayoptions" );
		var $time_format = $( "#EventTimeFormatDiv" );
		var $start_end_month = $( "select[name='EventStartMonth'], select[name='EventEndMonth']" );
		var $start_month = $( "select[name='EventStartMonth']" );
		var $end_month = $( 'select[name="EventEndMonth"]' );
		var selectObject;

		if ( is_community_edit ) {
			var $els = {
				start : $event_pickers.find( '#EventStartDate' ),
				end   : $event_pickers.next( 'tr' ).find( '#EventEndDate' ),
			};

			$.each( $els, function( i, el ) {
				var $el = $(el);
				( '' !== $el.val() ) && $el.val( tribeDateFormat( $el.val(), datepicker_format ) );
			})
		}

		// toggle time input

		function toggleDayTimeDisplay() {
			if ( $all_day_check.prop( 'checked' ) === true ) {
				$tod_options.hide();
				$time_format.hide();
			}
			else {
				$tod_options.show();
				$time_format.show();
			}
		}

		$all_day_check
			.click( function() {
				toggleDayTimeDisplay();
			} );

		toggleDayTimeDisplay();

		var tribeDaysPerMonth = [29, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

		// start and end date select sections
		var tribeStartDays = [ $( '#28StartDays' ), $( '#29StartDays' ), $( '#30StartDays' ), $( '#31StartDays' ) ],
			tribeEndDays = [ $( '#28EndDays' ), $( '#29EndDays' ), $( '#30EndDays' ), $( '#31EndDays' ) ];

		$start_end_month.change( function() {
			var t = $( this );
			var startEnd = t.attr( "name" );
			// get changed select field
			if ( startEnd == 'EventStartMonth' ) {
				startEnd = 'Start';
			}
			else {
				startEnd = 'End';
			}
			// show/hide date lists according to month
			var chosenMonth = t.attr( "value" );
			if ( chosenMonth.charAt( 0 ) == '0' ) {
				chosenMonth = chosenMonth.replace( '0', '' );
			}
			// leap year
			var remainder = $( "select[name='Event" + startEnd + "Year']" ).attr( "value" ) % 4;
			if ( chosenMonth == 2 && remainder == 0 ) {
				chosenMonth = 0;
			}
			// preserve selected option
			var currentDateField = $( "select[name='Event" + startEnd + "Day']" );

			$( '.event' + startEnd + 'DateField' ).remove();
			if ( startEnd == "Start" ) {
				selectObject = tribeStartDays[ tribeDaysPerMonth[ chosenMonth ] - 28 ];
				selectObject.val( currentDateField.val() );
				$start_month.after( selectObject );
			}
			else {
				selectObject = tribeEndDays[ tribeDaysPerMonth[ chosenMonth ] - 28 ];
				selectObject.val( currentDateField.val() );
				$end_month.after( selectObject );
			}
		} );

		$start_end_month.change();

		$( "select[name='EventStartYear']" ).change( function() {
			$start_month.change();
		} );

		$( "select[name='EventEndYear']" ).change( function() {
			$end_month.change();
		} );

		for ( var i in tribe_events_linked_posts.post_types ) {
			if ( ! tribe_events_linked_posts.post_types.hasOwnProperty( i ) ) {
				continue;
			}

			setup_linked_post_fields( i );
		}
	}

	//show state/province input based on first option in countries list, or based on user input of country

	var $state_prov_chzn = $( "#StateProvinceSelect_chosen" ),
		$state_prov_select = $( "#StateProvinceSelect" ),
		$state_prov_text = $( "#StateProvinceText" );


	function tribeShowHideCorrectStateProvinceInput( country ) {
		if ( country == 'US' || country == 'United States' ) {
			$state_prov_chzn.show();
			if ( $state_prov_chzn.length < 1 ) {
				$state_prov_select.show();
			}
			$state_prov_text.hide();
		}
		else if ( country != '' ) {
			$state_prov_text.show();
			$state_prov_chzn.hide();
			$state_prov_select.hide();
		}
		else {
			$state_prov_text.show();
			$state_prov_chzn.hide();
			$state_prov_select.hide();
		}
	}

	tribeShowHideCorrectStateProvinceInput( $( "#EventCountry > option:selected" ).val() );

	$( "#EventCountry" ).change( function() {
		var countryLabel = $( this ).find( 'option:selected' ).val();
		tribeShowHideCorrectStateProvinceInput( countryLabel );
	} );

	// EventCoordinates
	var overwriteCoordinates = {
		$container: $( '#overwrite_coordinates' )
	};

	overwriteCoordinates.$lat = overwriteCoordinates.$container.find( '#VenueLatitude' );
	overwriteCoordinates.$lng = overwriteCoordinates.$container.find( '#VenueLongitude' );

	overwriteCoordinates.$fields = $('').add( overwriteCoordinates.$lat ).add( overwriteCoordinates.$lng );
	overwriteCoordinates.$toggle = overwriteCoordinates.$container.find( '#VenueOverwriteCoords' ).on( 'change', function( event ){
		if ( overwriteCoordinates.$toggle.is(':checked') ) {
			overwriteCoordinates.$fields.prop( 'disabled', false ).removeClass( 'hidden' );
		} else {
			overwriteCoordinates.$fields.prop( 'disabled', true ).addClass( 'hidden' );
		}
	} );
	overwriteCoordinates.$toggle.trigger( 'change' );

	$( '#EventInfo input, #EventInfo select' ).change( function() {
		$( '.rec-error' ).hide();
	} );

	var eventSubmitButton = $( '.wp-admin.events-cal #post #publishing-action input[type="submit"]' );

	eventSubmitButton.click( function() {
		$( this ).data( 'clicked', true );
	} );

	// Workaround for venue & organizer post types when editing or adding
	// so events parent menu stays open and active
	if ( $( 'body' ).hasClass( 'post-type-tribe_venue' ) ) {
		$( '#menu-posts-tribe_events, #menu-posts-tribe_events a.wp-has-submenu' )
			.addClass( 'wp-menu-open wp-has-current-submenu wp-has-submenu' )
			.removeClass( 'wp-not-current-submenu' )
			.find( "li a[href='edit.php?post_type=tribe_venue']" )
			.parent()
			.addClass( 'current' );
	}
	if ( $( 'body' ).hasClass( 'post-type-tribe_organizer' ) ) {
		$( '#menu-posts-tribe_events, #menu-posts-tribe_events a.wp-has-submenu' )
			.addClass( 'wp-menu-open wp-has-current-submenu wp-has-submenu' )
			.removeClass( 'wp-not-current-submenu' )
			.find( "li a[href='edit.php?post_type=tribe_organizer']" )
			.parent()
			.addClass( 'current' );
	}

	// Default Layout Settings
	// shows / hides proper views that are to be used on front-end

	var $tribe_views = $( '#tribe-field-tribeEnableViews' );

	if ( $tribe_views.length ) {

		var $default_view_select = $( '.tribe-field-dropdown_select2 select[name="viewOption"]' ),
			$view_inputs = $tribe_views.find( 'input:checkbox' ),
			$view_desc = $( '#tribe-field-tribeEnableViews .tribe-field-wrap p.description' ),
			view_options = {};

		function create_view_array() {

			$default_view_select
				.find( 'option' )
				.each( function() {

					var $this = $( this );

					view_options[$this.attr( 'value' )] = $this.text();

				} );

		}

		function set_selected_views() {
			// Store the default view chosen prior to this change
			var prev_default_view = $default_view_select
				.find( "option:selected" )
				.first()
				.val();

			$default_view_select
				.find( 'option' )
				.remove();

			$view_inputs
				.each( function() {
					var $this = $( this );

					if ( $this.is( ':checked' ) ) {
						var value = $this.val();
						$default_view_select
							.append( '<option value="' + value + '">' + view_options[value] + '</option>' );
					}
				} );

			// Test to see if the previous default view is still available...
			var $prev_default_option = $default_view_select.find( "option[value='" + prev_default_view + "']" );

			// ...if it is, keep it as the default (else switch to the first available remaining option)
			if ( 1 === $prev_default_option.length ) {
				$prev_default_option
					.attr( 'selected', 'selected' );
			} else {
				$default_view_select
					.find( 'option' )
					.first()
					.attr( 'selected', 'selected' );
			}

			$default_view_select
				.select2( 'destroy' )
				.select2( {width: '250px'} );
		}

		create_view_array();

		$tribe_views
			.on( 'change', 'input:checkbox', function() {

				var $this = $( this );

				if ( $( '[name="tribeEnableViews[]"]:checked' ).length < 1 ) {
					$this.attr( 'checked', true );
					$view_desc.css( 'color', 'red' );
				}
				else {
					$view_desc.removeAttr( 'style' );
				}

				set_selected_views();

			} );
	}

	/**
	 * Capture the community "Add" form on submit to ensure safe date format.
	 */
	$( '#tribe-community-events.form form' ).on( 'submit', function() {

		var $els = {
			start: $event_pickers.find( '#EventStartDate' ),
			end  : $event_pickers.next( 'tr' ).find( '#EventEndDate' )
		};

		$els.start.val( tribeDateFormat( $els.start.datepicker( 'getDate' ), 'tribeQuery' ) );
		$els.end.val( tribeDateFormat( $els.end.datepicker( 'getDate' ), 'tribeQuery' ) );

		$event_pickers.parent().find( '.tribe-no-end-date-update' ).each( function() {
			$el = $( this );

			if ( ! $el.is( ':visible' ) ) {
				return;
			}

			$el.val( tribeDateFormat( $el.datepicker( 'getDate' ), 'tribeQuery' ) );
		} );
	} );

});

( function ( $ ) {
	'use strict';

	var widget_update = function ( e, $widget ) {
		if ( 'undefined' === typeof $widget ) {
			var $target = $( e.target ),
				$widget;

			// Prevent weird non avaiable widgets to go any further
			if ( ! $target.parents('.widget-top').length || $target.parents('#available-widgets').length ) {
				return;
			}

			$widget = $target.closest( 'div.widget' );
		}

		// If we are not dealing with one of the Tribe Widgets
		if (
			! $widget.is( '[id*="tribe-events-adv-list"]' ) &&
			! $widget.is( '[id*="tribe-mini-calendar"]' ) &&
			! $widget.is( '[id*="tribe-this-week-events"]' )
		) {
			return;
		}

		// Bail when it was not off screen
		if ( $widget.find( '.select2-container' ).length !== 0 && ! $widget.find( '.select2-container' ).hasClass( 'select2-offscreen' ) ) {
			return;
		}

		$widget.find( 'select.calendar-widget-add-filter' ).removeClass( 'select2-offscreen' ).select2();
	};

	// Open the Widget
	$( document.body ).on( 'click.widgets-toggle', widget_update );

	// When Updated Re-Structure the Select2
	$( document ).on( 'widget-updated', widget_update );
} )( jQuery );

/**
 * Manage the timezone selector user interface.
 */
jQuery( document ).ready( function( $ ) {
	var $row           = $( "#EventInfo" ).find( "tr.event-timezone" );
	var $label         = $row.find( "label" );
	var $selector      = $row.find( "select" );
	var $dropdown      = $row.find( ".chosen-container" );
	var $selector_cell = $selector.parent( "td" );

	var label_text  = $label.html();
	var selected_tz = $selector.find( "option:selected").html();
	var tz_link     = "<a href='#' class='change_tz'>" + label_text + " " + selected_tz + "</a>";

	$label.hide();
	$dropdown.hide();

	$selector_cell.append( tz_link );
	$selector_cell.find( "a.change_tz" ).click( function( event ) {
		event.stopImmediatePropagation();
		$( this ).hide();
		$dropdown.show();
		return false;
	} );
} );
