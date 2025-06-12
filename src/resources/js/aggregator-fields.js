/* eslint-disable */
window.tribe_aggregator = window.tribe_aggregator || {};

( function( $, _, ea ) {
	'use strict';

	ea.localized = window.tribe_aggregator_data || {};

	// Setup the global Variable
	ea.fields = {
		// Store the Required Selectors
		selector: {
			container               : '.tribe-ea',
			form                    : '.tribe-ea-form',
			help                    : '.tribe-ea-help',
			fields                  : '.tribe-ea-field',
			dropdown                : '.tribe-ea-dropdown',
			origin_field            : '#tribe-ea-field-origin',
			field_url_source        : '#tribe-ea-field-url_source',
			eventbrite_url_source   : '#tribe-ea-field-eventbrite_source',
			post_status             : '.tribe-ea-field-post_status',
			import_type_field       : '.tribe-import-type',
			media_button            : '.tribe-ea-media_button',
			datepicker              : '.tribe-datepicker',
			save_credentials_button : '.enter-credentials .tribe-save',
			preview_container       : '.tribe-preview-container',
			preview_button          : '.tribe-preview:visible',
			refine_filters          : '.tribe-refine-filters',
			clear_filters_button    : '.tribe-clear-filters',
			finalize_button         : '.tribe-finalize',
			cancel_button           : '.tribe-cancel',
			schedule_delete_link    : '.tribe-ea-tab-scheduled a.submitdelete',
			tab_new                 : '.tribe-ea-tab-new',
			action                  : '#tribe-action',
			view_filters            : '.tribe-view-filters'
		},

		media: {},

		// Store the jQuery elements
		$: {},

		// Store the methods for creating the fields
		construct: {},

		// Store the methods that will act as event handlers
		events: {},

		// store the current import_id
		import_id: null,

		// track how many result fetches have been executed via polling
		result_fetch_count: 0,

		// the maximum number of result fetches that can be done per frequency before erroring out
		max_result_fetch_count: 15,

		// frequency at which we will poll for results
		polling_frequency_index: 0,

		polling_frequencies: [
			500,
			1000,
			5000,
			20000
		],

		// A "module" of sorts related to Eventbrite only imports.
		eventbrite: {
			refineControls: '.tribe-refine-filters.eventbrite, .tribe-refine-filters.eventbrite .tribe-refine',
			refineControlsHideMap: {
				'event': 'tr.tribe-refine-filters',
				'organizer': ''
			},
			detect_type: function ( url ) {
				if ( ! ea.localized.source_origin_regexp.eventbrite ) {
					return null;
				}

				var baseRegex = ea.localized.source_origin_regexp.eventbrite;
				var type_regexps = {
					// E.g. https://www.eventbrite.fr/e/some-event
					'event': baseRegex + 'e\/[A-z0-9_-]+',
					// E.g. https://www.eventbrite.fr/o/some-organizer
					'organizer': baseRegex + 'o\/[A-z0-9_-]+'
				};
				var type = undefined;

				_.each( type_regexps, function ( regularExpression, key ) {
					var exp = new RegExp( regularExpression, 'g' );
					var match = exp.exec( url );

					if ( null === match ) {
						return;
					}

					type = key;
				} );

				return type;
			}
		}
	};
	/**
	 * Sets up the fields for EA pages
	 *
	 * @return void
	 */
	ea.fields.init = function() {
		ea.fields.$.container = $( ea.fields.selector.container );

		ea.fields.$.form = $( ea.fields.selector.form );

		ea.fields.$.action = $( ea.fields.selector.action );

		// Update what fields we currently have to setup
		ea.fields.$.fields = ea.fields.$.container.find( ea.fields.selector.fields );

		// Setup the preview container
		ea.fields.$.preview_container = $( ea.fields.selector.preview_container );

		// setup some variables we might reuse
		ea.fields.origin = $( '#tribe-ea-field-origin' );
		ea.fields.importType = $( '#tribe-ea-field-url_import_type' );
		ea.fields.urlImport = {
			startDate: $( '#tribe-ea-field-url_start' ),
			originalMinDate: function() {
				return $( '#tribe-ea-field-url_start' ).datepicker( 'option', 'minDate' ) || '';
			},
		};

		// Setup each type of field
		$.each( ea.fields.construct, function( key, callback ){
			callback( ea.fields.$.fields );
		} );

		// @TODO: I don't think this is necessary any more?
		// `tribe_ev` is only available on the front end and this script only loads in the admin
		if (
			typeof tribe_ev !== undefined ||
			typeof tribe_ev.state !== undefined
		) {
			var $tribe_events = $( document.getElementById( 'eventDetails' ) );
			if ( $tribe_events.data( 'datepicker_format' ) ) {
				tribe_ev.state.datepicker_format = $tribe_events.data( 'datepicker_format' );
			}
		}

		$( document )
			.on( 'keypress'   , ea.fields.selector.fields                    , ea.fields.events.trigger_field_change )
			.on( 'click'      , ea.fields.selector.save_credentials_button   , ea.fields.events.trigger_save_credentials )
			.on( 'click'      , ea.fields.selector.clear_filters_button      , ea.fields.clear_filters )
			.on( 'click'      , ea.fields.selector.finalize_button           , ea.fields.finalize_manual_import )
			.on( 'click'      , ea.fields.selector.preview_button            , ea.fields.preview_import )
			.on( 'click'      , ea.fields.selector.cancel_button             , ea.fields.events.cancel_edit )
			.on( 'click'      , ea.fields.selector.schedule_delete_link      , ea.fields.events.verify_schedule_delete )
			.on( 'click'      , ea.fields.selector.view_filters              , ea.fields.events.toggle_view_filters )
			.on( 'blur'       , ea.fields.selector.datepicker                , ea.fields.date_helper )
			.on( 'submit'     , ea.fields.selector.tab_new                   , ea.fields.events.suppress_submission )
			.on( 'change'     , ea.fields.selector.import_type_field         , function() {
				// Resets the Preview
				ea.fields.reset_preview()

				// Every time you change Type of import we reset the frequency field
				var $this = $( this ),
				    $frequency = $( this ).next( ea.fields.selector.fields );

				var importType = $this.val();

				$frequency.val( ( 'schedule' === importType ? 'daily' : '' ) ).trigger( 'change' );

				// set a data attribute on the form indicating the schedule type
				ea.fields.$.form.attr( 'data-type', importType );

				ea.fields.maybeLimitUrlStartDate()
			} )
			.on( 'change'     , ea.fields.selector.origin_field              , function() {
				var $field = $( this );
				var selectData = $( this ).data( 'select2' );
				var origin  = $field.val();
				ea.fields.$.form.attr( 'data-origin', origin );
				ea.fields.reset_preview();

				// reset all bumpdowns
				$( '.tribe-bumpdown-active' ).removeClass( 'tribe-bumpdown-active' );
				$( '.tribe-bumpdown:visible' ).hide();

				if ( 'redirect' === $( this ).val() ) {
					window.open( 'https://theeventscalendar.com/wordpress-event-aggregator/?utm_source=importoptions&utm_medium=plugin-tec&utm_campaign=in-app', '_blank' );
					location.reload();
				}

				// A "reset" of the Post Status select2 selector when an origin is selected.
				if ( '' !== origin ) {
					$( ea.fields.selector.post_status )
						.val( ea.localized.default_settings[ origin ][ 'post_status' ] )
						.trigger( 'change' );
				}

				ea.fields.maybeLimitUrlStartDate()
			} )
			.on( 'change', ea.fields.selector.eventbrite_url_source, function ( e ) {
				// Show all UI controls at first, even if we bail the user will have a full UI.
				$( ea.fields.eventbrite.refineControls ).show();

				var type = ea.fields.eventbrite.detect_type( $( '#tribe-ea-field-eventbrite_source' ).val() );

				if ( ! type ) {
					return;
				}

				// And then hide the ones that should be hidden for this import type if there are any.
				var controlsToHide = ea.fields.eventbrite.refineControlsHideMap[ type ];
				if ( controlsToHide ) {
					$( controlsToHide ).hide();
				}
			} )
			.on( 'change', ea.fields.selector.field_url_source, function( e ) {
				var $field = $( this );
				var selectData = $( this ).data( 'select2' );
				var value  = $field.val();
				var origin = null;

				if ( ! value ) {
					return;
				}

				_.each( ea.localized.source_origin_regexp, function( regularExpression, key ) {
					var exp = new RegExp( regularExpression, 'g' );
					var match = exp.exec( value );

					if ( null === match ) {
						return;
					}

					origin = key;
				} );

				if ( null == origin ) {
					return;
				}

				var $origin = $( ea.fields.selector.origin_field );

				// Prevent Changing when dealing with Non-Existent Origin
				if ( ! $origin.find( 'option[value="' + origin + '"]' ).length ) {
					return;
				}

				var $type = $( '#tribe-ea-field-url_import_type' );
				var typeValue = $type.val();
				var frequencyValue = null;
				if ( 'schedule' === typeValue ) {
					frequencyValue = $( '#tribe-ea-field-url_import_frequency' ).val();
				}

				// Reset type value to avoid bugs
				$type.val( '' );

				// Change the Origin to what ever matched
				$origin.val( origin ).trigger( 'change' );

				// Change the frequency accordingly
				$( '#tribe-ea-field-' + origin + '_import_type' ).val( typeValue ).trigger( 'change' );
				if ( 'schedule' === typeValue ) {
					$( '#tribe-ea-field-' + origin + '_import_frequency' ).val( frequencyValue ).trigger( 'change' );
				}

				if ( 'eventbrite' === origin ) {
					$( '#tribe-ea-field-' + origin + '_source_type_url' ).trigger( 'click' );
					$( '#tribe-ea-field-' + origin + '_import_source' ).val( 'source_type_url' ).trigger( 'change' );
				}

				// Change the Source URL accordingly
				$( '#tribe-ea-field-' + origin + '_source' ).val( value ).trigger( 'change' );
			} );

		$( '.tribe-dependency' ).trigger( 'change' );

		// Configure TimePickers
		tribe_timepickers.setup_timepickers( $( tribe_timepickers.selector.timepicker ) );

		if ( 'edit' === ea.fields.$.action.val() ) {
			ea.fields.$.form.addClass( 'edit-form' );
			$( ea.fields.selector.finalize_button ).html( ea.localized.l10n.edit_save );
		}

		if ( 'object' === typeof window.tribe_aggregator_save ) {
			$(document).trigger( 'tribe_aggregator_init_notice' );
		}
	};

	/**
	 * Send an Ajax request to preview the import
	 */
	ea.fields.preview_import = function( event ) {
		event.preventDefault();

		var $form = $( '.tribe-ea-form.tribe-validation' );

		ea.fields.reset_post_status();

		// Makes sure we have validation
		$form.trigger( 'validation.tribe' );

		// Prevent anything from happening when there are errors
		if ( tribe.validation.hasErrors( $form ) ) {
			return;
		}

		ea.fields.reset_polling_counter();

		// clear the warning area
		var $message_container = $( '.tribe-fetch-warning-message' ).html( '' );

		// when generating data for previews, temporarily remove the post ID and import ID values from their fields
		var $post_id = $( '#tribe-post_id' );
		$post_id.data( 'value', $post_id.val() );
		$post_id.val( '' );

		var $import_id = $( '#tribe-import_id' );
		$import_id.data( 'value', $import_id.val() );
		$import_id.val( '' );

		var $preview = $( ea.fields.selector.preview_button );
		var $form = $preview.closest( 'form' );
		var data = $form.serialize();

		// add the post_id value back into the field now that we've generated the serialized form data
		$post_id.val( $post_id.data( 'value' ) );
		$import_id.val( $post_id.data( 'value' ) );

		ea.fields.$.preview_container
			.addClass( 'tribe-fetching' )
			.removeClass( 'tribe-fetch-error' );

		ea.fields.$.form.removeClass( 'show-data' );

		$preview.prop( 'disabled', true );

		var table = $( '.dataTable' ).data( 'table' );
		if ( 'undefined' !== typeof table ) {
			table.clear().draw();
		}

		if ( 'edit' === ea.fields.$.action.val() ) {
			// preview the import
			ea.fields.preview_save_import( data );
		} else {
			// create the import
			ea.fields.create_import( data );
		}
	};

	/**
	 * Reset the post status to the default state when a new import is taking place
	 */
	ea.fields.reset_post_status = function() {
		var $origin = $( ea.fields.selector.origin_field ); // eslint-disable-line no-var
		var origin = $origin.length === 0 ? '' : $origin.val(); // eslint-disable-line no-var

		if ( origin === '' ) {
			return;
		}

		// Set the default state of the post_status
		$( ea.fields.selector.post_status )
			.val( ea.localized.default_settings[ origin ].post_status )
			.trigger( 'change' );
	};

	ea.fields.reset_polling_counter = function() {
		ea.fields.polling_frequency_index = 0;
		ea.fields.result_fetch_count = 0;
	};

	/**
	 * Clears the refine filters
	 */
	ea.fields.reset_form = function() {
		ea.fields.$.fields.val( '' ).trigger( 'change' );
		$( '[id$="import_frequency"]' ).val( 'daily' ).trigger( 'change' );
		ea.fields.$.form.removeClass( 'show-data' );
	};

	/**
	 * Resets the preview area of a form
	 */
	ea.fields.reset_preview = function() {
		ea.fields.$.form.removeClass( 'show-data' );
		$( '.tribe-fetched, .tribe-fetching, .tribe-fetch-error' ).removeClass( 'tribe-fetched tribe-fetching tribe-fetch-error' );
	};

	/**
	 * Clears the refine filters
	 */
	ea.fields.clear_filters = function() {
		$( ea.fields.selector.refine_filters )
			.find( 'input, select' )
			.val( '' )
			.trigger( 'change' );
	};

	/**
	 * Edits an import and polls for results
	 */
	ea.fields.preview_save_import = function( data ) {
		var jqxhr = $.ajax( {
			type: 'POST',
			url: ajaxurl + '?action=tribe_aggregator_preview_import',
			data: data,
			dataType: 'json'
		} );

		jqxhr.done( ea.fields.handle_preview_create_results );
	};

	/**
	 * Creates an import and polls for results
	 *
	 * @param object data Form data for the import
	 */
	ea.fields.create_import = function( data ) {
		var jqxhr = $.ajax( {
			type: 'POST',
			url: ajaxurl + '?action=tribe_aggregator_create_import',
			data: data,
			dataType: 'json'
		} );

		jqxhr.done( ea.fields.handle_preview_create_results );
	};

	/**
	 * Handles the create/edit results
	 */
	ea.fields.handle_preview_create_results = function( response ) {
		if ( ! response.success ) {
			var error = response.data;

			if ( ! _.isString( error ) ) {
				error = error.message;
			}

			ea.fields.display_fetch_error( [
				'<b>',
					ea.localized.l10n.preview_fetch_error_prefix,
				'</b>',
				' ' + error
			].join( ' ' ) );
			return;
		}

		// set the import id of the page
		ea.fields.import_id = response.data.data.import_id;
		$( '#tribe-import_id' ).val( ea.fields.import_id );

		if ( 'undefined' !== typeof response.data.data.items ) {
			ea.fields.init_datatable( response.data.data );
			ea.fields.$.preview_container.removeClass( 'tribe-fetching' ).addClass( 'tribe-fetched' );
			return;
		}

		ea.fields.$.container.find( '.spinner-message' ).html( ea.localized.l10n.preview_polling[0] );
		setTimeout( ea.fields.poll_for_results, ea.fields.polling_frequencies[ ea.fields.polling_frequency_index ] );
	};

	/**
	 * Poll for results from an import
	 */
	ea.fields.poll_for_results = function() {
		ea.fields.result_fetch_count++;

		const urlParts = [
			'action=tribe_aggregator_fetch_import',
			`import_id=${ ea.fields.import_id }`,
			`tribe_aggregator_nonce=${ ea.localized.nonce }`
		];

		var jqxhr = $.ajax( {
			type: 'GET',
			url: `${ ajaxurl }?` + urlParts.join( '&' ),
			dataType: 'json'
		} );

		jqxhr.done( function( response ) {
			if ( 'undefined' !== typeof response.data.warning && response.data.warning ) {
				var warning_message = response.data.warning;

				ea.fields.display_fetch_warning( [
					'<b>',
					ea.localized.l10n.preview_fetch_warning_prefix,
					'</b>',
					' ' + warning_message
				].join( ' ' ) );
			}

			if ( ! response.success ) {
				var error_message;

				if ( 'undefined' !== typeof response.data.message ) {
					error_message = response.data.message;
				} else if ( 'undefined' !== typeof response.data[0].message ) {
					error_message = response.data[0].message;
				}

				ea.fields.display_fetch_error( [
					'<b>',
						ea.localized.l10n.preview_fetch_error_prefix,
					'</b>',
					' ' + error_message
				].join( ' ' ) );
				return;
			}

			if ( 'error' === response.data.status ) {
				ea.fields.display_fetch_error( response.data.message );
			} else if ( 'success' !== response.data.status ) {
				if ( ea.fields.result_fetch_count > ea.fields.max_result_fetch_count ) {
					ea.fields.polling_frequency_index++;
					ea.fields.$.container.find( '.spinner-message' ).html( ea.localized.l10n.preview_polling[ ea.fields.polling_frequency_index ] );
					ea.fields.result_fetch_count = 0;
				}

				if ( 'undefined' === typeof ea.fields.polling_frequencies[ ea.fields.polling_frequency_index ] ) {
					ea.fields.display_fetch_error( ea.localized.l10n.preview_timeout );
				} else {
					setTimeout( ea.fields.poll_for_results, ea.fields.polling_frequencies[ ea.fields.polling_frequency_index ] );
				}
			} else {
				response.data.data.items = response.data.data.events;
				ea.fields.init_datatable( response.data.data );
				ea.fields.$.preview_container.removeClass( 'tribe-fetching' ).addClass( 'tribe-fetched' );
				$( ea.fields.selector.preview_button ).prop( 'disabled', false );
			}
		} );
	};

	/**
	 * Initializes the datatable
	 *
	 * @param array data Array of events to display in the table
	 */
	ea.fields.init_datatable = function( data ) {
		var display_checkboxes = false;

		var origin = $( ea.fields.selector.origin_field ).val();
		var is_csv = 'csv' === origin;
		var is_eventbrite = 'eventbrite' === origin;

		var $import_type = $( '[id$="import_type"]:visible' );
		var import_type = 'manual';

		// set the default settings
		if ( 'undefined' !== typeof ea.localized.default_settings[ origin ] ) {
			for ( var settings_key in ea.localized.default_settings[ origin ] ) {
				if ( ! ea.localized.default_settings[ origin ].hasOwnProperty( settings_key ) ) {
					continue;
				}

				var $setting_field = $( '#tribe-ea-field-' + settings_key );

				$setting_field
					.val( ea.localized.default_settings[ origin ][ settings_key ] )
					.trigger( 'change' );
			}
		}

		if ( $import_type.length ) {
			import_type = $( '#' + $import_type.first().attr( 'id' ).replace( 's2id_', '' ) ).val();
		}

        if ( 'manual' === import_type && !data.items.length ) {
			var origin = data.origin;
			var origin_specific_no_results_msg = (
				'undefined' !== typeof ea.localized.l10n[ origin ]
				&& 'undefined' !== typeof ea.localized.l10n[ origin ].no_results
			);

			var message = origin_specific_no_results_msg ?
				ea.localized.l10n[ origin ].no_results
				: ea.localized.l10n.no_results;

			ea.fields.display_fetch_error(message);
			return;
		}

		if ( ! $import_type.length || 'manual' === import_type ) {
			display_checkboxes = true;
		}

		var $table = ea.fields.$.preview_container.find( '.data-container table' );

		var rows = [];
		for ( var i in data.items ) {
			var row = data.items[ i ];
			row.checkbox = display_checkboxes ? '<input type="checkbox">' : '';
			if ( row.all_day ) {
				row.start_time = ea.localized.l10n.all_day;
			} else {
				if ( 'undefined' === typeof row.start_meridian || ! row.start_meridian ) {
					if ( parseInt( row.start_hour, 10 ) > 11 ) {
						row.start_meridian = ea.localized.l10n.pm;
					} else {
						row.start_meridian = ea.localized.l10n.am;
					}
				}

				if ( row.start_hour > 12 ) {
					row.start_hour = row.start_hour - 12;
				}

				row.start_time = ( 0 === parseInt( row.start_hour, 10 ) ? 12 : row.start_hour ) + ':' + ( '00' + row.start_minute ).slice( -2 );
				row.start_time += ' ' + row.start_meridian;
			}
			rows.push( row );
		}

		if ( display_checkboxes && ! is_csv ) {
			$table.addClass( 'display-checkboxes' );
		} else {
			$table.removeClass( 'display-checkboxes' );
		}

		ea.fields.$.form.addClass( 'show-data' );

		var args = {
			lengthMenu: [
				[ 5, 10, 25, 50, -1 ],
				[ 5, 10, 25, 50, tribe_l10n_datatables.pagination.all ]
			],
			order: [
				[ 1, 'asc' ]
			],
			columnDefs: [
				{
					cellType: 'th',
					className: 'check-column',
					orderable: false,
					targets: 0
				}
			],
			data: rows
		};

		if ( 'undefined' !== typeof data.columns ) {
			args.columns = [
				{ data: 'checkbox' }
			];

			var $head = $table.find( 'thead tr' );
			var $foot = $table.find( 'tfoot tr' );
			var $map_row = $({});
			var column_map = '';
			var content_type = '';
			$head.find( 'th:first' ).nextAll().remove();
			$foot.find( 'th:first' ).nextAll().remove();

			if ( is_csv ) {
				var $data_container = $table.closest( '.data-container' );
				$table.closest( '.data-container' ).addClass( 'csv-data' );

				$data_container.find( '.tribe-preview-message .tribe-csv-filename' ).html( $( '#tribe-ea-field-csv_file_name' ).text() );
				$head.closest( 'thead' ).prepend( '<tr class="tribe-column-map"><th scope="row" class="check-column column-cb"></th></tr>' );
				$map_row = $( '.tribe-column-map' );
				content_type = $( '#tribe-ea-field-csv_content_type' ).val();
				content_type = content_type.replace( 'tribe_', '' );

				var $mapper_template = $( '#tribe-csv-column-map-' + content_type );
				column_map = $mapper_template.html();
			}

			var column = 0;
			for ( i in data.columns ) {
				args.columns.push( { data: data.columns[ i ] } );
				$head.append( '<th scope="col">' + data.columns[ i ] + '</th>' );
				$foot.append( '<th scope="col">' + data.columns[ i ] + '</th>' );

				// if this is a CSV import, add the column map headers and default-select where possible
				if ( is_csv ) {
					var column_slug = data.columns[ i ].toLowerCase()
						.replace( /^\s+|\s+$/g, '' ) // Remove left / right spaces before the word starts
						.replace( /\s/g, '_' )    // change all spaces inside of words to underscores
						.replace( /[^a-z0-9_]/g, '' ); // Change all character that are not letter, numbers or underscore.
					$map_row.append( '<th scope="col">' + column_map.replace( 'name="column_map[]"', 'name="aggregator[column_map][' + column + ']" id="column-' + column + '"' ) + '</th>' );

					var $map_select = $map_row.find( '#column-' + column );

					if ( 'undefined' !== typeof ea.localized.csv_column_mapping[ content_type ][ column ] ) {
						column_slug = ea.localized.csv_column_mapping[ content_type ][ column ];
					}
					$map_select.find( 'option[value="' + column_slug + '"]' ).prop( 'selected', true );
				}

				column++;
			}

			args.scrollX = true;
		} else {
			args.columns = [
				{ data: 'checkbox' },
				{ data: 'start_date' },
				{ data: 'start_time' },
				{ data: 'end_date' },
				{ data: 'title' }
			];
			args.autoWidth = false;
		}

		$table.tribeDataTable( args );
		ea.fields.wrap_cell_content();

		$table
			.on( 'select.dt'  , ea.fields.events.twiddle_finalize_button_text )
			.on( 'deselect.dt', ea.fields.events.twiddle_finalize_button_text )
			.on( 'draw.dt', ea.fields.wrap_cell_content );

		var text;

		if ( 'new' === ea.fields.$.action.val() ) {
			if ( 'manual' === import_type && is_csv ) {
				text = ea.localized.l10n.import_all_no_number;
			} else if ( 'manual' === import_type ) {
				text = ea.localized.l10n.import_all.replace( '%d', rows.length );
			} else {
				text = ea.localized.l10n.create_schedule;
			}
		}

		$( ea.fields.selector.finalize_button ).html( text );
	};

	ea.fields.wrap_cell_content = function() {
		$( '.dataTable' ).find( 'tbody td' ).each( function() {
			var $cell = $( this );
			$cell.html( '<div class="tribe-td-height-limit">' + $cell.html() + '</div>' );
		} );
	};

	/**
	 * Displays a fetch error
	 */
	ea.fields.display_fetch_error = function( message ) {
		var $message_container = $( '.tribe-fetch-error-message' );
		ea.fields.$.preview_container.removeClass( 'tribe-fetching' ).addClass( 'tribe-fetch-error' );

		// clear out the error message area
		$message_container.html('');

		ea.fields.display_error( $message_container, message );
		$( ea.fields.selector.preview_button ).prop( 'disabled', false );
	};

	/**
	 * Displays a fetch warning
	 */
	ea.fields.display_fetch_warning = function( message ) {
		var $message_container = $( '.tribe-fetch-warning-message' );
		ea.fields.$.preview_container.removeClass( 'tribe-fetching' ).addClass( 'tribe-fetch-warning' );

		// clear out the error message area
		$message_container.html('');

		ea.fields.display_warning( $message_container, message );
	};

	/**
	 * Displays an error to a container on the page
	 */
	ea.fields.display_error = function( $container, message ) {
		$container.prepend(
			[
				'<div class="notice notice-error">',
					'<p>',
						message,
					'</p>',
				'</div>'
			].join( '' )
		);
	};

	/**
	 * Displays a warning to a container on the page
	 */
	ea.fields.display_warning = function( $container, message ) {
		$container.prepend(
			[
				'<div class="notice notice-warning">',
				'<p>',
				message,
				'</p>',
				'</div>'
			].join( '' )
		);
	};

	/**
	 * displays a success message to a container on the page
	 */
	ea.fields.display_success = function( $container, message ) {
		$container.prepend(
			[
				'<div class="notice notice-success">',
					'<p>',
						message,
					'</p>',
				'</div>'
			].join( '' )
		);
	};

	/**
	 * Saves credential form
	 */
	ea.fields.save_credentials = function( $credentials_form ) {
		var data = $credentials_form.find( '.tribe-fieldset' ).find( 'input' ).serialize();
		data += `&tribe_aggregator_nonce=${ ea.localized.nonce }`;

		var url = ajaxurl + '?action=tribe_aggregator_save_credentials';

		var jqxhr = $.post( url, data );
		jqxhr.done( function( response ) {
			if ( response.success ) {
				$credentials_form.addClass( 'credentials-entered' );
				$credentials_form.find( '[name="has-credentials"]' ).val( 1 ).trigger( 'change' );
			}
		} );
	};

	/**
	 * Submits the final version of the import for saving events
	 */
	ea.fields.finalize_manual_import = function() {
		var origin = $( '#tribe-ea-field-origin' ).val();
		var $table = $( '.dataTable' );
		var table  = window.tribe_data_table;

		if ( $table.hasClass( 'display-checkboxes' ) ) {
			var row_selection = table.rows( { selected: true } );
			if ( ! row_selection[0].length ) {
				row_selection = table.rows();
			}

			if ( ! row_selection[0].length ) {
				ea.fields.display_error( $( '.tribe-finalize-container' ), ea.localized.l10n.events_required_for_manual_submit );
				return;
			}

			var data  = row_selection.data();
			var items = [];
			var unique_id_field = null;

			if ( 'meetup' === origin ) {
				unique_id_field = 'meetup_id';
			} else if ( 'eventbrite' === origin ) {
				unique_id_field = 'eventbrite_id';
			} else if ( 'ical' === origin || 'ics' === origin || 'gcal' === origin ) {
				unique_id_field = 'uid';
			} else if ( 'url' === origin ) {
				unique_id_field = 'id';
			}

			if ( null !== unique_id_field ) {
				for ( var i in data ) {
					if ( isNaN( i ) ) {
						continue;
					}

					if ( 'undefined' === typeof data[ i ][ unique_id_field ] ) {
						continue;
					}

					items.push( data[ i ][ unique_id_field ] );
				}

				$( '#tribe-selected-rows' ).text( JSON.stringify( items ) );
			} else {
				$( '#tribe-selected-rows' ).text( 'all' );
			}
		} else {
			$( '#tribe-selected-rows' ).text( 'all' );
		}

		$( '.dataTables_scrollBody' ).find( '[name^="aggregator[column_map]"]' ).remove();

		ea.fields.$.form.trigger( 'submit' );
	};

	/**
	 * Better Search ID for Select2, compatible with WordPress ID from WP_Query
	 *
	 * @param  {object|string} e Searched object or the actual ID
	 * @return {string}   ID of the object
	 */
	ea.fields.search_id = function ( e ) {
		var id = null;

		if ( 'undefined' !== typeof e.id ){
			id = e.id;
		} else if ( 'undefined' !== typeof e.ID ){
			id = e.ID;
		} else if ( 'undefined' !== typeof e.value ){
			id = e.value;
		}
		return e == undefined ? null : id;
	};

	/**
	 * Configure the Drop Down Fields
	 *
	 * @param  {jQuery} $fields All the fields from the page
	 *
	 * @return {jQuery}         Affected fields
	 */
	ea.fields.construct.dropdown = function( $fields ) {
		var upsellFormatter = function( option ) {
			var $option = $( option.element );

			if ( 'string' === typeof $option.data( 'subtitle' ) ) {
				option.text = option.text + '<br><span class="tribe-dropdown-subtitle">' + $option.data( 'subtitle' ) + '</span>';
			}

			return option.text;
		};
		var args = {
			formatResult: upsellFormatter,
			formatSelection: upsellFormatter,
		};

		tribe_dropdowns.dropdown( $fields.filter( '.tribe-ea-dropdown' ), args );

		// return to be able to chain jQuery calls
		return $fields;
	};

	/**
	 * Configures the Media Button
	 *
	 * @param  {jQuery} $fields All the fields from the page
	 *
	 * @return {jQuery}         Affected fields
	 */
	ea.fields.construct.media_button = function( $fields ) {
		var $elements = $fields.filter( ea.fields.selector.media_button );

		if ( typeof wp === 'undefined' || ! wp.media || ! wp.media.editor ) {
			return $elements;
		}

		$elements.each( function(){
			var $button = $( this ),
				input = $button.data( 'input' ),
				$field = $( '#' + input ),
				$name = $( '#' + input + '_name' );

			// Setup the WP Media for this slug
			var media = ea.fields.media[ input ] = wp.media( {
				title: $button.data( 'mediaTitle' ),
				library: {
					type: $button.data( 'mimeType' )
				},
				multiple: false
			} );

			// On select send to Select2
			media.on( 'select', function (){
				var state = media.state(),
					selection = state.get('selection');

				if ( ! selection ) {
					return;
				}

				selection.each( function( attachment ) {
					$field.data( { id: attachment.attributes.id, text: attachment.attributes.title } );
					$field.val( attachment.attributes.id );
					$field.trigger( 'change' );
					$name.html( attachment.attributes.filename );
					$name.attr( 'title', attachment.attributes.filename );
				} );
			} );

			// We don't need the Media Library button
			/*
			media.on( 'open', function () {
				$( '.media-router .media-menu-item' ).first().trigger( 'click' );
			} );
			*/
		} );

		ea.fields.$.container.on( 'click', ea.fields.selector.media_button, function( e ) {
			e.preventDefault();

			if ( ! $( this ).is( ':visible' ) ) {
				return;
			}

			var input = $( this ).data( 'input' );
			ea.fields.media[ input ].open( input );
			return false;
		} );

		return $elements;
	};

	/**
	 * Triggers a change event on the given field
	 */
	ea.fields.events.trigger_field_change = function() {
		$( this ).trigger( 'change' );
	};

	/**
	 * Triggers the saving of credentials
	 */
	ea.fields.events.trigger_save_credentials = function() {
		ea.fields.save_credentials( $( this ).closest( '.enter-credentials' ) );
	};

	/**
	 * Suppress form submissions
	 */
	ea.fields.events.suppress_submission = function( e ) {
		var origin = $( '#tribe-ea-field-origin' ).val();

		if ( $( '#tribe-selected-rows' ).val().length ) {
			return true;
		}

		e.preventDefault();
	};

	/**
	 * Adjusts the "Import" button to have contextual text based on selected records to import
	 */
	ea.fields.events.twiddle_finalize_button_text = function( e, dt ) {
		if ( 'new' !== ea.fields.$.action.val() ) {
			return;
		}

		var selected_rows = dt.rows({ selected: true })[0].length;
		var text = ea.localized.l10n.import_checked;

		if ( ! selected_rows ) {
			text = ea.localized.l10n.import_all;
			selected_rows = dt.rows()[0].length;
		}

		text = text.replace( '%d', selected_rows );
		$( ea.fields.selector.finalize_button ).html( text );
	};

	ea.fields.events.cancel_edit = function( e ) {
		e.preventDefault();
		var url = window.location.href;
		url = url.replace( 'tab=edit', 'tab=scheduled' );
		url = url.replace( /id=\d+/, '' );
		window.location.href = url;
	};

	ea.fields.events.verify_schedule_delete = function() {
		return confirm( ea.localized.l10n.verify_schedule_delete );
	};

	/**
	 * Toggles the View Filters link on the Scheduled Imports/History page
	 */
	ea.fields.events.toggle_view_filters = function( e ) {
		e.preventDefault();
		var $el = $( this );

		$el.toggleClass( 'tribe-active' );
		if ( $el.is( '.tribe-active' ) ) {
			$el.html( ea.localized.l10n.hide_filters );
		} else {
			$el.html( ea.localized.l10n.view_filters );
		}
	};

	/**
	 * helper text for date select
	 */
	ea.fields.date_helper = function() {
		var $picker;

		$picker = $( this );

		if ( ! $picker.hasClass( 'tribe-datepicker' ) ) {
			return;
		}

		var selected_date = $picker.val();
		if ( '' === selected_date || null === selected_date ) {
			return;
		}

		var tmp = $picker.attr( 'id' ).match( 'tribe-ea-field-(.*)_start' );
		var origin = tmp[1];
		if ( '' === origin || null === origin ) {
			return;
		}

		jQuery( '#tribe-date-helper-date-' + origin ).html( selected_date );
	};

	ea.fields.maybeLimitUrlStartDate = function() {
		if( 'url' !== ea.fields.origin.val() ){
			return;
		}

		if( 'schedule' === ea.fields.importType.val() ){
			ea.fields.urlImport.startDate.data( 'datepicker-min-date', 'today' );

			return;
		}

		ea.fields.urlImport.startDate.data( 'datepicker-min-date', null );
	};

	// Run Init on Document Ready
	$( ea.fields.init );
} )( jQuery, _, window.tribe_aggregator );
