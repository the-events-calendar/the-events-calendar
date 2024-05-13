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
				d    : d,
				dd   : pad( d ),
				ddd  : dF.i18n.dayNames[D],
				dddd : dF.i18n.dayNames[D + 7],
				m    : m + 1,
				mm   : pad( m + 1 ),
				mmm  : dF.i18n.monthNames[m],
				mmmm : dF.i18n.monthNames[m + 12],
				yy   : String( y ).slice( 2 ),
				yyyy : y,
				h    : H % 12 || 12,
				hh   : pad( H % 12 || 12 ),
				H    : H,
				HH   : pad( H ),
				M    : M,
				MM   : pad( M ),
				s    : s,
				ss   : pad( s ),
				l    : pad( L, 3 ),
				L    : pad( L > 99 ? Math.round( L / 10 ) : L ),
				t    : H < 12 ? "a" : "p",
				tt   : H < 12 ? "am" : "pm",
				T    : H < 12 ? "A" : "P",
				TT   : H < 12 ? "AM" : "PM",
				Z    : utc ? "UTC" : (String( date ).match( timezone ) || [""]).pop().replace( timezoneClip, "" ),
				o    : (o > 0 ? "-" : "+") + pad( Math.floor( Math.abs( o ) / 60 ) * 100 + Math.abs( o ) % 60, 4 ),
				S    : ["th", "st", "nd", "rd"][d % 10 > 3 ? 0 : (d % 100 - d % 10 != 10) * d % 10]
			};

		return mask.replace( token, function( $0 ) {
			return $0 in flags ? flags[$0] : $0.slice( 1, $0.length - 1 );
		} );
	};
}();

tribeDateFormat.masks = {
	'default'         : 'ddd mmm dd yyyy HH:MM:ss',
	'tribeQuery'      : 'yyyy-mm-dd',
	'tribeMonthQuery' : 'yyyy-mm',
	'0'               : 'yyyy-mm-dd',
	'1'               : 'm/d/yyyy',
	'2'               : 'mm/dd/yyyy',
	'3'               : 'd/m/yyyy',
	'4'               : 'dd/mm/yyyy',
	'5'               : 'm-d-yyyy',
	'6'               : 'mm-dd-yyyy',
	'7'               : 'd-m-yyyy',
	'8'               : 'dd-mm-yyyy',
	'9'               : 'yyyy.mm.dd',
	'10'              : 'mm.dd.yyyy',
	'11'              : 'dd.mm.yyyy',
	'm0'              : 'yyyy-mm',
	'm1'              : 'm/yyyy',
	'm2'              : 'mm/yyyy',
	'm3'              : 'm/yyyy',
	'm4'              : 'mm/yyyy',
	'm5'              : 'm-yyyy',
	'm6'              : 'mm-yyyy',
	'm7'              : 'm-yyyy',
	'm8'              : 'mm-yyyy'
};

tribeDateFormat.i18n = {
	dayNames  : [
		'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat',
		'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'
	],
	monthNames: [
		'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec',
		'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'
	]
};

Date.prototype.format = function( mask, utc ) {
	return tribeDateFormat( this, mask, utc );
};

var tribe_datepicker_opts = {};

jQuery( function( $ ) {

	$( '.bumpdown-trigger' ).bumpdown();

	/**
	 * Setup Datepicker
	 */
	var $date_format      = $( '[data-datepicker_format]' ),
		$view_select      = $( '.tribe-field-dropdown_select2 select' ),
		viewCalLinkHTML   = $( document.getElementById( 'view-calendar-link-div' ) ).html(),
		$template_select  = $( 'select[name="tribeEventsTemplate"]' ),
		$event_pickers    = $( document.getElementById( 'tribe-event-datepickers' ) ),
		is_community_edit = $( 'body' ).is( '.tribe_community_edit' ),
		datepicker_format = 0;

	// Modified from tribe_ev.data to match jQuery UI formatting.
	var datepicker_formats = {
		'main' : [
			'yy-mm-dd',
			'm/d/yy',
			'mm/dd/yy',
			'd/m/yy',
			'dd/mm/yy',
			'm-d-yy',
			'mm-dd-yy',
			'd-m-yy',
			'dd-mm-yy',
			'yy.mm.dd',
			'mm.dd.yy',
			'dd.mm.yy'
		],
		'month': ['yy-mm', 'm/yy', 'mm/yy', 'm/yy', 'mm/yy', 'm-yy', 'mm-yy', 'm-yy', 'mm-yy']
	};

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

	/**
	 * Returns the number of months to display in
	 * the datepicker based on the viewport width
	 *
	 * @returns {number}
	 */
	function get_datepicker_num_months() {
		var window_width = $( window ).width();

		if ( window_width < 800 ) {
			return 1;
		}

		if ( window_width <= 1100 ) {
			return 2;
		} else {
			return 3;
		}
	}

	var setup_linked_post_fields = function( post_type ) {
		var saved_template  = $( document.getElementById( 'tmpl-tribe-select-' + post_type ) ).length ? wp.template( 'tribe-select-' + post_type ) : null;
		var create_template = $( document.getElementById( 'tmpl-tribe-create-' + post_type ) ).length ? wp.template( 'tribe-create-' + post_type ) : null;
		var section         = $( document.getElementById( 'event_' + post_type ) );
		var rows            = section.find( '.saved-linked-post' );

		section.on( 'click', '.tribe-add-post', function(e) {
			e.preventDefault();
			var dropdown = $({});
			var fields = $({});

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

			// The final <tbody> contains the add new post link, we should add this new selector before that
			section.find( 'tfoot:first' ).before( fields );

			fields.prepend( dropdown );

			if ( section.find( 'tbody' ).length > 1 ) {
				section.find( '.move-linked-post-group' ).show();
			} else {
				section.find( '.move-linked-post-group' ).hide();
			}

			fields.find( '.tribe-dropdown' ).tribe_dropdowns();

			dropdown.find( 'select.linked-post-dropdown' ).trigger( 'change' );

			// Determine if we should hide all the trash buttons and move buttons.
			if ( section.find( 'tbody' ).length > 1 ) {
				section.find( '.tribe-delete-this' ).show();
				section.find( '.move-linked-post-group' ).show();
			} else {
				section.find( '.tribe-delete-this' ).hide();
				section.find( '.move-linked-post-group' ).hide();
			}

			/**
			 * Fires when a new linked post is added to the event.
			 *
			 * @since 6.2.0
			 *
			 * @param {string} post_type The post type of the linked post.
			 * @param {jQuery} section   The current Section of Linked Post.
			 * @param {jQuery} fields    The fields for the new linked post.
			 * @param {jQuery} dropdown  The Dropdown for the new linked post.
			 **/
			window.wp.hooks.doAction( 'tec.events.admin.linked_posts.add_post', post_type, section, fields, dropdown );
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
			if ( 'undefined' === typeof window['tribe_sticky_' + post_type + '_fields'] || ! Array.isArray( window['tribe_sticky_' + post_type + '_fields'] ) ) {
				return;
			}

			var $fields = $( fields );
			var $rows = $fields.filter( 'tr.linked-post.' + container );

			// bail if the fields are not about this container
			if ( $rows.length === 0 ) {
				return;
			}

			// The linked post type fields also need sticky field behaviour: populate
			// them if we've been provided with the necessary data to do so
			var sticky_data = window['tribe_sticky_' + post_type + '_fields'].shift();
			var sticky_data_added = false;

			if ( 'object' === typeof sticky_data ) {
				for ( var key in sticky_data ) {
					// Check to see if we have a field of this name
					var $field = $fields.find( 'input[name="' + container + '[' + key + '][]"]' );

					// If no field or an empty value, skip.
					if ( ! $field.length || _.isEmpty( sticky_data[ key ] ) ) {
						continue;
					}

					// Set the value accordingly
					$field.val( sticky_data[ key ] );
					sticky_data_added = true;
				}
			}

			if ( sticky_data_added ) {
				$rows.show();
			}
		}

		rows.each( function () {
			var row   = $( this );
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
			for ( var post_type in tribe_events_linked_posts.post_types ) {
				if ( ! tribe_events_linked_posts.post_types.hasOwnProperty( post_type ) ) {
					continue;
				}

				add_sticky_linked_post_data( post_type, tribe_events_linked_posts.post_types[ post_type ], fields );
			}

			fields.find( '.tribe-dropdown' ).tribe_dropdowns();
			group.append( fields );
		} );

		$( document ).on( 'click', `#event_${post_type} .tribe-delete-this`, function(e) {
			e.preventDefault();
			var $group = $( this ).closest( 'tbody' );

			$group.parents( '.tribe-section' ).removeClass( 'tribe-is-creating-linked-post' );

			$group.fadeOut( 500, function() {
				$( this ).remove();

				$group.find( 'select.linked-post-dropdown' ).trigger( 'change' );

				// Determine if we should hide all the trash buttons and move buttons.
				if ( section.find( 'tbody' ).length > 1 ) {
					section.find( '.tribe-delete-this' ).show();
					section.find( '.move-linked-post-group' ).show();
				} else {
					section.find( '.tribe-delete-this' ).hide();
					section.find( '.move-linked-post-group' ).hide();
				}

				showOrHideAddPostButton( section );
			} );
		});

		var sortable_items = '> tbody';

		if ( ! $( 'body' ).hasClass( 'wp-admin' ) ) {
			sortable_items = 'table ' + sortable_items;
		}

		section.sortable( {
			items       : sortable_items,
			handle      : '.move-linked-post-group',
			containment : 'parent',
			axis        : 'y',
			delay       : 100
		} );

		// Determine if we should hide all the trash buttons and move buttons.
		if ( section.find( 'tbody' ).length > 1 ) {
			section.find( '.tribe-delete-this' ).show();
			section.find( '.move-linked-post-group' ).show();
		} else {
			section.find( '.tribe-delete-this' ).hide();
			section.find( '.move-linked-post-group' ).hide();
		}

		section.find( 'select.linked-post-dropdown' ).trigger( 'change' );
	};

	var toggle_linked_post_fields = function( event ) {
		const $select = $( this );
		const postType = $select.data( 'postType' );
		const $wrapper = $select.parents( `#event_${postType}` ).eq( 0 );
		const $groups = $wrapper.find( 'tbody' );
		const linkedPostCount = $groups.length;
		const $group = $select.closest( 'tbody' );
		const currentGroupPosition = $groups.index( $group ) + 1;
		const $edit = $group.find( '.edit-linked-post-link a' );
		const value = $select.val();
		const $selected = $select.find( ':selected' );
		const selectedVal = $selected.val();
		let editLink = '';
		let existingPost = false;

		if ( selectedVal === value ) {
			editLink = $selected.data( 'editLink' );
			existingPost = !! $selected.data( 'existingPost' );
		}

		// Always hide the edit link unless we have an edit link to show (handled below).
		$edit.hide();
		$wrapper.find( 'tfoot .tribe-add-post' ).show();

		if ( ! existingPost && value !== '-1' && value ) {
			// Apply the New Given Title to the Correct Field
			$group.find( '.linked-post-name' ).val( value ).parents( '.linked-post' ).eq( 0 ).attr( 'data-hidden', true );

			$select.val( '-1' );

			// Display the Fields
			$group
				.find( '.linked-post' ).not( '[data-hidden]' ).show()
				.find( '.tribe-dropdown' );

			$group.parents( '.tribe-section' ).addClass( 'tribe-is-creating-linked-post' );
		} else {
			// Hide all fields and remove their values
			$group.find( '.linked-post' ).hide().find( 'input, select' ).val( '' );

			$group.parents( '.tribe-section' ).removeClass( 'tribe-is-creating-linked-post' );

			// Modify and Show edit link
			if ( ! _.isEmpty( editLink ) ) {
				$edit.attr( 'href', editLink ).show();
			}
		}

		showOrHideAddPostButton( $wrapper );
	};

	/**
	 * Shows or hides the Add <Post> button.
	 *
	 * @since 6.2.0
	 * @param {Object} $wrapper The jQuery object for the wrapper of the linked post fields.
	 */
	function showOrHideAddPostButton( $wrapper ) {
		const $groups = $wrapper.find( 'tbody' );
		const linkedPostCount = $groups.length;

		const $select = $wrapper.find( 'tbody' ).last().find( 'select' );
		const value = $select.val();
		const $selected = $select.find( ':selected' );
		const selectedVal = $selected.val();
		const $group = $select.closest( 'tbody' );

		const currentGroupPosition = $groups.index( $group ) + 1;
		let existingPost = false;

		if ( selectedVal === value ) {
			existingPost = !! $selected.data( 'existingPost' );
		}

		if (
			! existingPost &&
			( ! value || value === '-1' ) &&
			currentGroupPosition === linkedPostCount
		) {
			$wrapper.find( 'tfoot .tribe-add-post' ).hide();
		} else {
			$wrapper.find( 'tfoot .tribe-add-post' ).show();
		}
	}

	$( '.hide-if-js' ).hide();

	if ( typeof( tribe_l10n_datatables.datepicker ) !== 'undefined' ) {

		var _MS_PER_DAY = 1000 * 60 * 60 * 24;

		var date_format = 'yy-mm-dd';

		if ( $date_format.length && $date_format.attr( 'data-datepicker_format' ).length >= 1 ) {
			datepicker_format = $date_format.attr( 'data-datepicker_format' );
			date_format       = datepicker_formats.main[ datepicker_format ];
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

		var $start_date       = $( document.getElementById( 'EventStartDate' ) );
		var $end_date         = $( document.getElementById( 'EventEndDate' ) );
		var $event_details    = $( document.getElementById( 'tribe_events_event_details' ) );

		tribe_datepicker_opts = {
			dateFormat      : date_format,
			showAnim        : 'fadeIn',
			changeMonth     : true,
			changeYear      : true,
			numberOfMonths  : get_datepicker_num_months(),
			showButtonPanel : false,
			beforeShow      : function( element, object ) {
				object.input.datepicker( 'option', 'numberOfMonths', get_datepicker_num_months() );
				object.input.data( 'prevDate', object.input.datepicker( 'getDate' ) );

				// allow single datepicker fields to specify a min or max date
				// using the `data-datapicker-(min|max)Date` attribute
				if ( undefined !== object.input.data( 'datepicker-min-date' ) ) {
					object.input.datepicker( 'option', 'minDate', object.input.data( 'datepicker-min-date' ) );
				}

				if ( undefined !== object.input.data( 'datepicker-max-date' ) ) {
					object.input.datepicker( 'option', 'maxDate', object.input.data( 'datepicker-max-date' ) );
				}

				// Capture the datepicker div here; it's dynamically generated so best to grab here instead
				// of elsewhere.
				$dpDiv = $( object.dpDiv );

				// "Namespace" our CSS a bit so that our custom jquery-ui-datepicker styles don't interfere
				// with other plugins'/themes'.
				$dpDiv.addClass( 'tribe-ui-datepicker' );

				$event_details.trigger( 'tribe.ui-datepicker-div-beforeshow', [ object ] );

				$dpDiv.attrchange( {
					trackValues: true,
					callback: function( attr ) {
						// This is a non-ideal, but very reliable way to look for the closing of the ui-datepicker box,
						// since onClose method is often occluded by other plugins, including Events Calender PRO.
						if (
							'string' === typeof attr.newValue &&
							(
								attr.newValue.indexOf( 'display: none' ) >= 0 ||
								attr.newValue.indexOf( 'display:none' ) >= 0
							)
						) {
							$dpDiv.removeClass( 'tribe-ui-datepicker' );
							$event_details.trigger( 'tribe.ui-datepicker-div-closed', [ object ] );
						}
					},
				} );
			},
			onSelect: function( selected_date, object ) {

				var instance = $( this ).data( 'datepicker' );
				var date     = $.datepicker.parseDate(
					instance.settings.dateFormat || $.datepicker._defaults.dateFormat,
					selected_date,
					instance.settings
				);

				// If the start date was adjusted, then let's modify the minimum acceptable end date
				if ( this.id === 'EventStartDate' ) {
					var start_date = $( document.getElementById( 'EventStartDate' ) ).data( 'prevDate' );
					var date_diff  = null == start_date ? 0 : date_diff_in_days( start_date, $end_date.datepicker( 'getDate' ) );
					var end_date   = new Date( date.setDate( date.getDate() + date_diff ) );

					$end_date
						.datepicker( 'option', 'minDate', $start_date.datepicker( 'getDate' ) )
						.datepicker( 'setDate', end_date )
						.datepicker_format;
				}

				// fire the change and blur handlers on the field
				$( this ).trigger( 'change' );
				$( this ).trigger( 'blur' );
			}
		};

		$.extend( tribe_datepicker_opts, tribe_l10n_datatables.datepicker );

		var dates            = $( '.tribe-datepicker' ).datepicker( tribe_datepicker_opts );
		var $start_end_month = $( 'select[name="EventStartMonth"], select[name="EventEndMonth"]' );
		var $start_month     = $( 'select[name="EventStartMonth"]' );
		var $end_month       = $( 'select[name="EventEndMonth"]' );
		var selectObject;

		if ( is_community_edit ) {
			var $els = {
				start : $event_pickers.find( '#EventStartDate' ),
				end   : $event_pickers.next( 'tr' ).find( '#EventEndDate' ),
			};

			$.each( $els, function( i, el ) {
				var $el = $( el );
				( '' !== $el.val() ) && $el.val( tribeDateFormat( $el.val(), datepicker_format ) );
			} )
		}

		var tribeDaysPerMonth = [29, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

		// start and end date select sections
		var tribeStartDays = [
			$( document.getElementById( '28StartDays' ) ),
			$( document.getElementById( '29StartDays' ) ),
			$( document.getElementById( '30StartDays' ) ),
			$( document.getElementById( '31StartDays' ) )
		];

		var tribeEndDays = [
			$( document.getElementById( '28EndDays' ) ),
			$( document.getElementById( '29EndDays' ) ),
			$( document.getElementById( '30EndDays' ) ),
			$( document.getElementById( '31EndDays' ) )
		];

		$start_end_month.on( 'change', function() {
			var t = $( this );
			var startEnd = t.attr( 'name' );
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
			var remainder = $( 'select[name="Event' + startEnd + 'Year"]' ).attr( 'value' ) % 4;
			if ( chosenMonth == 2 && remainder == 0 ) {
				chosenMonth = 0;
			}
			// preserve selected option
			var currentDateField = $( 'select[name="Event' + startEnd + 'Day"]' );

			$( '.event' + startEnd + 'DateField' ).remove();
			if ( startEnd == 'Start' ) {
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

		$start_end_month.trigger( 'change' );

		$( 'select[name="EventStartYear"]' ).on( 'change', function() {
			$start_month.trigger( 'change' );
		} );

		$( 'select[name="EventEndYear"]' ).on( 'change', function() {
			$end_month.trigger( 'change' );
		} );

		for ( var i in tribe_events_linked_posts.post_types ) {
			if ( ! tribe_events_linked_posts.post_types.hasOwnProperty( i ) ) {
				continue;
			}

			setup_linked_post_fields( i );
		}
	}

	window.wp.hooks.addAction( 'tec.events.admin.linked_posts.add_post', 'tec', ( post_type, template_section, new_section ) => {
		if ( 'tribe_venue' !== post_type ) {
			return;
		}

		new_section.find( '#EventCountry' ).trigger( 'change' );
	} );

	//show state/province input based on first option in countries list, or based on user input of country
	$( document ).on( 'change', '[id="EventCountry"]', function () {
		var $country        = $( this );
		var $container      = $country.parents( 'tbody' ).eq( 0 );
		var $state_select   = $container.find( '#StateProvinceSelect' );
		var $state_dropdown = $state_select.next( '.select2-container' );
		var $state_text     = $container.find( '#StateProvinceText' );
		var country         = $( this ).val();

		if ( country == 'US' || country == 'United States' ) {
			$state_text.hide();
			$state_select.hide();
			$state_dropdown.show();
		} else {
			$state_text.show();
			$state_select.hide();
			$state_dropdown.hide();
		}
	} ).find( '#EventCountry' ).trigger( 'change' );

	// EventCoordinates
	var overwriteCoordinates = {
		$container : $( document.getElementById( 'overwrite_coordinates' ) )
	};

	overwriteCoordinates.$lat = overwriteCoordinates.$container.find( '#VenueLatitude' );
	overwriteCoordinates.$lng = overwriteCoordinates.$container.find( '#VenueLongitude' );

	overwriteCoordinates.$fields = $('').add( overwriteCoordinates.$lat ).add( overwriteCoordinates.$lng );
	overwriteCoordinates.$toggle = overwriteCoordinates.$container.find( '#VenueOverwriteCoords' ).on( 'change', function( event ) {
		if ( overwriteCoordinates.$toggle.is(':checked') ) {
			overwriteCoordinates.$fields.prop( 'disabled', false ).removeClass( 'hidden' );
		} else {
			overwriteCoordinates.$fields.prop( 'disabled', true ).addClass( 'hidden' );
		}
	} );
	overwriteCoordinates.$toggle.trigger( 'change' );

	$( '#EventInfo input, #EventInfo select' ).on( 'change', function() {
		$( '.rec-error' ).hide();
	} );

	var eventSubmitButton = $( '.wp-admin.events-cal #post #publishing-action input[type="submit"]' );

	eventSubmitButton.on(
		'click',
		function() {
			$( this ).data( 'clicked', true );
		}
	);

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

	var $tribe_views = $( document.getElementById( 'tribe-field-tribeEnableViews' ) );

	if ( $tribe_views.length ) {

		var $default_view_select        = $( 'select[name="viewOption"]' );
		var $default_mobile_view_select = $( 'select[name="mobile_default_view"]' );
		var $view_inputs                = $tribe_views.find( 'input:checkbox' );
		var $view_desc                  = $( '#tribe-field-tribeEnableViews .tribe-field-wrap p.description' );
		var view_options                = {};

		function create_view_array() {

			$default_view_select
				.find( 'option' )
				.each( function() {
					var $this = $( this );
					view_options[$this.attr( 'value' )] = $this.text();
				} );

			$default_mobile_view_select
				.find( 'option' )
				.each( function() {
					var $this = $( this );
					view_options[$this.attr( 'value' )] = $this.text();
				} );

		}

		function set_selected_views( $this ) {
			// Store the default view chosen prior to this change
			var prev_default_view = $default_view_select
				.find( "option:selected" )
				.first()
				.val();

			var prev_default_mobile_view = $default_mobile_view_select
				.find( "option:selected" )
				.first()
				.val();

			$default_view_select
				.find( 'option' )
				.remove();

			$default_mobile_view_select
				.find( 'option' )
				.remove();

			$view_inputs
				.each( function() {
					var $this = $( this );

					if ( $this.is( ':checked' ) ) {
						var value = $this.val();
						var label = value.substr( 0, 1 ).toUpperCase() + value.substr( 1 );
						$default_view_select
							.append( '<option value="' + value + '">' + label + '</option>' );
						$default_mobile_view_select
							.append( '<option value="' + value + '">' + label + '</option>' );
					}
				} );

			// Test to see if the previous default view is still available...
			var $prev_default_option = $default_view_select.find( "option[value='" + prev_default_view + "']" );
			var $prev_default_mobile_option = $default_mobile_view_select.find( "option[value='" + prev_default_mobile_view + "']" );

			// ...if it is, keep it as the default (else switch to the first available remaining option)
			if ( $prev_default_option.val() == $this.val() ) {
				$prev_default_option .attr( 'selected', 'selected' );
			} else {
				var $default_reset = $tribe_views.find( 'checkbox:checked' ).first().val();
				$default_view_select.find( 'option' ).find( "option[value='" + $default_reset + "']" ).attr( 'selected', 'selected' );
			}

			if ( $prev_default_mobile_option.val() == $this.val() ) {
				$prev_default_mobile_option .attr( 'selected', 'selected' );
			} else {
				var $default_reset = $tribe_views.find( 'checkbox:checked' ).first().val();
				$default_mobile_view_select.find( 'option' ).find( "option[value='" + $default_reset + "']" ).attr( 'selected', 'selected' );
			}

			$default_view_select
				.select2( 'destroy' )
				.select2( { width: 'auto' } );

			$default_mobile_view_select
				.select2( 'destroy' )
				.select2( { width: 'auto' } );
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

				set_selected_views( $this );

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
} );
