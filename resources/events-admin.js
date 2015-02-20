/**
 * @todo contains a number of recurrence-related functions which should be moved to PRO
 */
jQuery( document ).ready( function( $ ) {

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

		var $recurrence_type = $( '[name="recurrence[type]"]' ),
			$end_date = $( '#EventEndDate' );

		var datepickerOpts = {
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
				var option = this.id == "EventStartDate" ? "minDate" : "maxDate",
					instance = $( this ).data( "datepicker" ),
					date = $.datepicker.parseDate( instance.settings.dateFormat || $.datepicker._defaults.dateFormat, selectedDate, instance.settings );

				if ( this.id === "EventStartDate" && ( $recurrence_type.length && $recurrence_type.val() !== 'None' ) ) {

					var startDate = $( '#EventStartDate' ).data( 'prevDate' ),
						dateDif = date_diff_in_days( startDate, $end_date.datepicker( 'getDate' ) ),
						endDate = new Date( date.setDate( date.getDate() + dateDif ) );

					$end_date
						.datepicker( "option", option, endDate )
						.datepicker( "setDate", endDate );

				}
				else {
					dates
						.not( this )
						.not( '#recurrence_end' )
						.datepicker( "option", option, date );
				}
			}
		};

		$.extend( datepickerOpts, TEC );

		var dates = $( "#EventStartDate, #EventEndDate, .tribe-datepicker" ).datepicker( datepickerOpts ).datepicker( 'setDate', new Date( date_format ) ),
			$all_day_check = $( '#allDayCheckbox' ),
			$tod_options = $( ".timeofdayoptions" ),
			$time_format = $( "#EventTimeFormatDiv" ),
			$start_end_month = $( "select[name='EventStartMonth'], select[name='EventEndMonth']" ),
			$start_month = $( "select[name='EventStartMonth']" ),
			$end_month = $( 'select[name="EventEndMonth"]' ),
			selectObject;

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

		// hide unnecessary fields
		var venueFields = $( ".venue" ),
			savedVenue = $( "#saved_venue" );

		if ( savedVenue.length > 0 && savedVenue.val() != '0' ) {
			venueFields.hide();
			$( '[name="venue[Venue]"]' ).val( '' );
		}

		savedVenue.change( function() {
			var selected_venue_id = $(this).val(),
				current_edit_link = $('.edit-venue-link a').attr( 'data-admin-url' );

			if ( selected_venue_id == '0' ) {
				venueFields.fadeIn();
				$( "#EventCountry" ).val( 0 ).trigger( "chosen:updated" );
				$( "#StateProvinceSelect" ).val( 0 ).trigger( "chosen:updated" );
				tribeShowHideCorrectStateProvinceInput( '' );
				$('.edit-venue-link').hide();
			}
			else {
				venueFields.fadeOut();
				$('.edit-venue-link').show();

				// Change edit link
				
				$('.edit-venue-link a').attr( 'href', current_edit_link + selected_venue_id );
			}
		} );
		// hide unnecessary fields
		var organizerFields = $( ".organizer" ),
			savedorganizer = $( "#saved_organizer" );

		if ( savedorganizer.length > 0 && savedorganizer.val() != '0' ) {
			organizerFields.hide();
			$( 'input', organizerFields ).val( '' );
		}

		savedorganizer.change( function() {
			var selected_organizer_id = $(this).val(),
				current_edit_link = $('.edit-organizer-link a').attr( 'data-admin-url' );

			if ( selected_organizer_id == '0' ) {
				organizerFields.fadeIn();
				$('.edit-organizer-link').hide();
			}
			else {
				organizerFields.fadeOut();
				$('.edit-organizer-link').show();

				// Change edit link
				$('.edit-organizer-link a').attr( 'href', current_edit_link + selected_organizer_id );
			}
		} );
	}

	//show state/province input based on first option in countries list, or based on user input of country

	var $state_prov_chzn = $( "#StateProvinceSelect_chosen" ),
		$state_prov_text = $( "#StateProvinceText" );


	function tribeShowHideCorrectStateProvinceInput( country ) {
		if ( country == 'US' || country == 'United States' ) {
			$state_prov_chzn.show();
			$state_prov_text.hide();
		}
		else if ( country != '' ) {
			$state_prov_text.show();
			$state_prov_chzn.hide();
		}
		else {
			$state_prov_text.hide();
			$state_prov_chzn.hide();
		}
	}

	tribeShowHideCorrectStateProvinceInput( $( "#EventCountry > option:selected" ).val() );

	var $hidesub = $( '[name="hideSubsequentRecurrencesDefault"]' ),
		$userhide = $( '[name="userToggleSubsequentRecurrences"]' );

	if ( $hidesub.length && $userhide.length ) {

		var $userwrap = $( '#tribe-field-userToggleSubsequentRecurrences' );

		if ( $hidesub.is( ':checked' ) ) {
			$userhide.prop( 'checked', false );
			$userwrap.hide();
		}

		$hidesub
			.on( 'click', function() {
				var $this = $( this );

				if ( ! $this.is( ':checked' ) ) {
					$userwrap.show();
				}
				else {
					$userhide.prop( 'checked', false );
					$userwrap.hide();
				}

			} );


	}

	var $picker_recur_end = $( '[name="recurrence[end]"]' ),
		$is_recurring = $( '[name="is_recurring"]' );

	$( "#EventCountry" ).change( function() {
		var countryLabel = $( this ).find( 'option:selected' ).val();
		tribeShowHideCorrectStateProvinceInput( countryLabel );
	} );

	// If recurrence changes on a recurring event, then show warning
	if ( $is_recurring.val() == "true" ) {
		function recurrenceChanged() {
			$( '#recurrence-changed-row' ).show();
		}

		$( '.recurrence-row input, .custom-recurrence-row input,.recurrence-row select, .custom-recurrence-row select' ).change( recurrenceChanged );
		$picker_recur_end.bind( 'recurrenceEndChanged', recurrenceChanged );
	}

	$picker_recur_end.datepicker( 'option', 'onSelect', function() {
		$picker_recur_end.removeClass( 'placeholder' );
		$( this ).trigger( 'recurrenceEndChanged' );
	} );

	function isExistingRecurringEvent() {
		return $is_recurring.val() == "true";
	}

	$( '#EventInfo input, #EventInfo select' ).change( function() {
		$( '.rec-error' ).hide();
	} );

	var eventSubmitButton = $( '.wp-admin.events-cal #post #publishing-action input[type="submit"]' );

	eventSubmitButton.click( function() {
		$( this ).data( 'clicked', true );
	} );

	// recurrence ui
	$( '[name="recurrence[type]"]' ).change( function() {
		var curOption = $( this ).find( "option:selected" ).val();
		$( '.custom-recurrence-row' ).hide();

		if ( curOption == "Custom" ) {
			$( '#recurrence-end' ).show();
			$( '#custom-recurrence-frequency' ).show();
			$( '[name="recurrence[custom-type]"]' ).change();
		}
		else if ( curOption == "None" ) {
			$( '#recurrence-end' ).hide();
			$( '#custom-recurrence-frequency' ).hide();
		}
		else {
			$( '#recurrence-end' ).show();
			$( '#custom-recurrence-frequency' ).hide();
		}
	} );

	$( '[name="recurrence[end-type]"]' ).change( function() {
		var val = $( this ).find( 'option:selected' ).val();

		if ( val == "On" ) {
			$( '#rec-count' ).hide();
			$( '#recurrence_end' ).show();
		}
		else if ( val == "Never" ) {
			$( '#rec-count, #recurrence_end' ).hide();
		}
		else {
			$( '#recurrence_end' ).hide();
			$( '#rec-count' ).show();
		}
	} );

	$( '[name="recurrence[custom-type]"]' ).change( function() {
		$( '.custom-recurrence-row' ).hide();
		var option = $( this ).find( 'option:selected' ), customSelector = option.data( 'tablerow' );
		$( customSelector ).show()
		$( '#recurrence-interval-type' ).text( option.data( 'plural' ) );
		$( '[name="recurrence[custom-type-text]"]' ).val( option.data( 'plural' ) );
	} );

	$( '#recurrence_end_count' ).change( function() {
		$( '[name="recurrence[type]"]' ).change();
	} );

	$( '[name="recurrence[type]"]' ).change( function() {
		var option = $( this ).find( 'option:selected' ), numOccurrences = $( '#recurrence_end_count' ).val();
		$( '#occurence-count-text' ).text( 1 == numOccurrences ? $( this ).data( 'single' ) : $( this ).data( 'plural' ) );
		$( '[name="recurrence[occurrence-count-text]"]' ).val( $( '#occurence-count-text' ).text() );
	} );

	$( '[name="recurrence[custom-month-number]"]' ).change( function() {
		var option = $( this ).find( 'option:selected' ), dayselect = $( '[name="recurrence[custom-month-day]"]' );

		if ( isNaN( option.val() ) ) {
			dayselect.show();
		}
		else {
			dayselect.hide();
		}
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

			$default_view_select
				.find( 'option' )
				.first()
				.attr( 'selected', 'selected' );

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

} );

/**
 * Re-initialize chosen on widgets when moved
 * credits: http://www.johngadbois.com/adding-your-own-callbacks-to-wordpress-ajax-requests/
 */
jQuery( document ).ajaxSuccess( function( e, xhr, settings ) {
	if ( typeof settings !== 'undefined' && typeof settings.data !== 'undefined' && settings.data.search( 'action=save-widget' ) != - 1 ) {
		jQuery( "#widgets-right .chosen" ).chosen();
	}
} );