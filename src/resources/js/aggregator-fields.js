var tribe_ea = tribe_ea || {};

// Setup the global Variable
tribe_ea.fields = {
	// Store the Required Selectors
	selector: {
		container: '.tribe-ea',
		form: '.tribe-ea-form',
		help: '.tribe-ea-help',
		fields: '.tribe-ea-field',
		dropdown: '.tribe-ea-dropdown',
		media_button: '.tribe-ea-media_button',
		datepicker: '.tribe-ea-datepicker',
		save_credentials_button: '.enter-credentials .tribe-save',
		preview_container: '.tribe-preview-container',
		preview_button: '.tribe-preview:visible',
		refine_filters: '.tribe-refine-filters',
		clear_filters_button: '.tribe-clear-filters',
		finalize_button: '.tribe-finalize'
	},

	media: {},

	// Store the jQuery elements
	$: {},

	// Store the methods for creating the fields
	construct: {},

	// Store the methods that will act as event handlers
	events: {},

	// Store L10N strings
	l10n: window.tribe_l10n_ea_fields,

	// store the current import_id
	import_id: null,

	// track how many result fetches have been executed via polling
	result_fetch_count: 0,

	// the maximum number of result fetches that can be done before erroring out
	max_result_fetch_count: 20,

	// frequency at which we will poll for results
	polling_frequency: 500
};

( function( $, _, obj ) {
	'use strict';

	/**
	 * Sets up the fields for EA pages
	 *
	 * @return void
	 */
	obj.init = function() {
		obj.$.container = $( obj.selector.container );

		// Update what fields we currently have to setup
		obj.$.fields = obj.$.container.find( obj.selector.fields );

		// Setup the preview container
		obj.$.preview_container = $( obj.selector.preview_container );

		// Setup each type of field
		$.each( obj.construct, function( key, callback ){
			callback( obj.$.fields );
		} );

		$( document )
			.on( 'keypress'   , obj.selector.fields                  , obj.events.trigger_field_change )
			.on( 'click'      , obj.selector.save_credentials_button , obj.events.trigger_save_credentials )
			.on( 'click'      , obj.selector.clear_filters_button    , obj.clear_filters )
			.on( 'click'      , obj.selector.finalize_button         , obj.finalize_import )
			.on( 'click'      , '.tribe-preview'                     , obj.preview_import )
			.on( 'submit'     , '.tribe-ea-tab-new'                  , obj.events.suppress_submission );
	};

	obj.twiddle_finalize_button_text = function( e, dt ) {
		var selected_rows = dt.rows({ selected: true })[0].length;
		var text = tribe_l10n_ea_fields.import_checked;

		if ( ! selected_rows ) {
			text = tribe_l10n_ea_fields.import_all;
			selected_rows = dt.rows()[0].length;
		}

		text = text.replace( '%d', selected_rows );
		$( obj.selector.finalize_button ).html( text );
	};

	/**
	 * Send an Ajax request to preview the import
	 */
	obj.preview_import = function() {
		var $preview = $( obj.selector.preview_button );
		var $form = $preview.closest( 'form' );
		var data = $form.serialize();
		obj.$.preview_container
			.addClass( 'tribe-fetching' )
			.removeClass( 'tribe-fetch-error' )
			.removeClass( 'show-data' );

		$preview.prop( 'disabled', true );

		var table = $( '.dataTable' ).data( 'table' );
		if ( 'undefined' !== typeof table ) {
			table.clear().draw();
		}

		// create the import
		obj.create_import( data );
	};

	/**
	 * Clears the refine filters
	 */
	obj.reset_form = function() {
		obj.$.fields.val( '' ).trigger( 'change' );
		$( '.tribe-ea-dropdown' ).select2( 'data', null );
		$( '[id$="import_frequency"]' ).val( 'daily' ).trigger( 'change' );
		obj.$.preview_container.removeClass( 'show-data' );
	};

	/**
	 * Clears the refine filters
	 */
	obj.clear_filters = function() {
		$( obj.selector.refine_filters )
			.find( 'input, select' )
			.val( '' )
			.trigger( 'change' );
	};

	/**
	 * Creates an import and polls for results
	 *
	 * @param object data Form data for the import
	 */
	obj.create_import = function( data ) {
		var jqxhr = $.ajax( {
			type: 'POST',
			url: ajaxurl + '?action=tribe_create_import',
			data: data,
			dataType: 'json'
		} );

		jqxhr.done( function( response ) {
			if ( ! response.success ) {
				obj.display_fetch_error( [
					'<b>',
						tribe_l10n_ea_fields.preview_fetch_error_prefix,
					'</b>',
					' ' + response.data.message
				].join( ' ' ) );
				return;
			}

			// set the import id of the page
			obj.import_id = response.data.data.import_id;

			setTimeout( obj.poll_for_results, obj.polling_frequency );
		} );
	};

	/**
	 * Poll for results from an import
	 */
	obj.poll_for_results = function() {
		obj.result_fetch_count++;

		var jqxhr = $.ajax( {
			type: 'GET',
			url: ajaxurl + '?action=tribe_fetch_import&import_id=' + obj.import_id,
			dataType: 'json'
		} );

		jqxhr.done( function( response ) {
			if ( ! response.success ) {
				obj.display_fetch_error( [
					'<b>',
						tribe_l10n_ea_fields.preview_fetch_error_prefix,
					'</b>',
					' ' + response.data.message
				].join( ' ' ) );
				return;
			}

			if ( 'success' !== response.data.status ) {
				if ( obj.result_fetch_count > obj.max_result_fetch_count ) {
					obj.display_fetch_error( [
						'The preview is taking longer than expected. Please try again in a moment.'
					].join( '' ) );
				} else {
					setTimeout( obj.poll_for_results, obj.polling_frequency );
				}
			} else {
				obj.init_datatable( response.data.data.events );
				obj.$.preview_container.removeClass( 'tribe-fetching' ).addClass( 'tribe-fetched' );
				$( obj.selector.preview_button ).prop( 'disabled', false );
			}
		} );
	};

	/**
	 * Initializes the datatable
	 *
	 * @param array data Array of events to display in the table
	 */
	obj.init_datatable = function( data ) {
		var display_checkboxes = false;

		var $import_type = $( '[id$="import_type"]:visible' );

		if ( ! $import_type.length || 'manual' === $( '#' + $import_type.first().attr( 'id' ).replace( 's2id_', '' ) ).val() ) {
			display_checkboxes = true;
		}

		var $table = obj.$.preview_container.find( '.data-container table' );

		var rows = [];
		for ( var i in data ) {
			var row = data[ i ];
			row.checkbox = display_checkboxes ? '<input type="checkbox">' : '';
			rows.push( row );
		}

		if ( display_checkboxes ) {
			$table.addClass( 'display-checkboxes' );
		} else {
			$table.removeClass( 'display-checkboxes' );
		}

		obj.$.preview_container.addClass( 'show-data' );
		$table.tribeDataTable( {
			lengthMenu: [
				[5, 10, 25, 50, -1],
				[5, 10, 25, 50, tribe_l10n_datatables.pagination.all ]
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
			columns: [
				{ data: 'checkbox' },
				{ data: 'start_date' },
				{ data: 'end_date' },
				{ data: 'title' }
			],
			data: rows
		} );

		$table
			.on( 'select.dt'  , obj.twiddle_finalize_button_text )
			.on( 'deselect.dt', obj.twiddle_finalize_button_text );

		var text = tribe_l10n_ea_fields.import_all.replace( '%d', rows.length );
		$( obj.selector.finalize_button ).html( text );
	};

	/**
	 * Displays a fetch error
	 */
	obj.display_fetch_error = function( message ) {
		obj.$.preview_container.removeClass( 'tribe-fetching' ).addClass( 'tribe-fetch-error' );
		$( '.tribe-fetch-error-message' ).html(
			[
				'<div class="notice notice-error">',
					'<p>',
						message,
					'</p>',
				'</div>'
			].join( '' )
		);

		$( obj.selector.preview_button ).prop( 'disabled', false );
	};

	/**
	 * Saves credential form
	 */
	obj.save_credentials = function( $credentials_form ) {
		var data = $( this ).closest( '.tribe-fieldset' ).find( 'input' ).serialize();

		var url = ajaxurl + '?action=tribe_save_credentials&which=facebook';

		var jqxhr = $.post( url, data );
		jqxhr.done( function( response ) {
			if ( response.success ) {
				$credentials_form.addClass( 'credentials-entered' );
				$credentials_form.find( '#tribe-has-credentials' ).val( 1 ).change();
			}
		} );
	};

	/**
	 * Better Search ID for Select2, compatible with WordPress ID from WP_Query
	 *
	 * @param  {object|string} e Searched object or the actual ID
	 * @return {string}   ID of the object
	 */
	obj.search_id = function ( e ) {
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
	obj.construct.dropdown = function( $fields ) {
		var $elements = $fields.filter( obj.selector.dropdown ).not( '.select2-offscreen, .select2-container' );

		$elements.each(function(){
			var $select = $(this),
				args = {};

			if ( ! $select.is( 'select' ) ) {
				// Better Method for finding the ID
				args.id = obj.search_id;
			}

			// By default we allow The field to be cleared
			args.allowClear = true;
			if ( $select.data( 'preventClear' ) ) {
				args.allowClear = false;
			}

			// If we are dealing with a Input Hidden we need to set the Data for it to work
			if ( $select.is( '[data-options]' ) ) {
				args.data = $select.data( 'options' );
			}

			// Prevents the Search box to show
			if ( $select.is( '[data-hide-search]' ) ) {
				args.minimumResultsForSearch = Infinity;
			}

			if ( $select.is( '[multiple]' ) ) {
				args.multiple = true;

				if ( ! $select.is( '[data-tags]' ) ) {
					args.data = function(){
						return { 'results': $select.data( 'options' ) };
					};
				}

				if ( ! _.isArray( $select.data( 'separator' ) ) ) {
					args.tokenSeparators = [ $select.data( 'separator' ) ];
				} else {
					args.tokenSeparators = $select.data( 'separator' );
				}
				args.separator = $select.data( 'separator' );

				// Define the regular Exp based on
				args.regexSeparatorElements = [ '^(' ];
				args.regexSplitElements = [ '(?:' ];
				$.each( args.tokenSeparators, function ( i, token ) {
					args.regexSeparatorElements.push( '[^' + token + ']+' );
					args.regexSplitElements.push( '[' + token + ']' );
				} );
				args.regexSeparatorElements.push( ')$' );
				args.regexSplitElements.push( ')' );

				args.regexSeparatorString = args.regexSeparatorElements.join( '' );
				args.regexSplitString = args.regexSplitElements.join( '' );

				args.regexToken = new RegExp( args.regexSeparatorString, 'ig' );
				args.regexSplit = new RegExp( args.regexSplitString, 'ig' );
			}

			/**
			 * Better way of matching results
			 *
			 * @param  {string} term Which term we are searching for
			 * @param  {string} text Search here
			 * @return {boolean}
			 */
			args.matcher = function( term, text ) {
				var result = text.toUpperCase().indexOf( term.toUpperCase() ) == 0;

				if ( ! result && 'undefined' !== typeof args.tags ){
					var possible = _.where( args.tags, { text: text } );
					if ( args.tags.length > 0  && _.isObject( possible ) ){
						var test_value = obj.search_id( possible[0] );
						result = test_value.toUpperCase().indexOf( term.toUpperCase() ) == 0;
					}
				}

				return result;
			};

			// Select also allows Tags, se we go with that too
			if ( $select.is( '[data-tags]' ) ){
				args.tags = $select.data( 'options' );

				args.initSelection = function ( element, callback ) {
					var data = [];
					$( element.val().split( args.regexSplit ) ).each( function () {
						var obj = { id: this, text: this };
						if ( args.tags.length > 0  && _.isObject( args.tags[0] ) ) {
							var _obj = _.where( args.tags, { value: this } );
							if ( _obj.length > 0 ){
								obj = _obj[0];
								obj = {
									id: obj.value,
									text: obj.text,
								};
							}
						}

						data.push( obj );

					} );
					callback( data );
				};

				args.createSearchChoice = function(term, data) {
					if ( term.match( args.regexToken ) ) {
						return { id: term, text: term };
					}
				};

				if ( 0 === args.tags.length ){
					args.formatNoMatches = function(){
						return $select.attr( 'placeholder' );
					};
				}
			}

			// When we have a source, we do an AJAX call
			if ( $select.is( '[data-source]' ) ) {
				var source = $select.data( 'source' );

				// For AJAX we reset the data
				args.data = { results: [] };

				// Allows HTML from Select2 AJAX calls
				args.escapeMarkup = function (m) {
					return m;
				};

				args.ajax = { // instead of writing the function to execute the request we use Select2's convenient helper
					dataType: 'json',
					type: 'POST',
					url: window.ajaxurl,
					results: function ( data ) { // parse the results into the format expected by Select2.
						return data.data;
					}
				};

				// By default only sent the source
				args.ajax.data = function( search, page ) {
					return {
						action: 'tribe_ea_dropdown_' + source,
					};
				};

				// If you want to create a diferent type of data for your AJAX call based on the source
				if ( 'Source Name' === source ){
				}
			}

			$select.select2( args );
		})
		.on( 'change', function( event ) {
			var $select = $(this),
				data = $( this ).data( 'value' );

			if ( ! $select.is( '[multiple]' ) ){
				return;
			}
			if ( ! $select.is( '[data-source]' ) ){
				return;
			}

			if ( event.added ){
				if ( _.isArray( data ) ) {
					data.push( event.added );
				} else {
					data = [ event.added ];
				}
			} else {
				if ( _.isArray( data ) ) {
					data = _.without( data, event.removed );
				} else {
					data = [];
				}
			}
			$select.data( 'value', data ).attr( 'data-value', JSON.stringify( data ) );
		} );

		// return to be able to chain jQuery calls
		return $elements;
	};

	/**
	 * Configures the Media Button
	 *
	 * @param  {jQuery} $fields All the fields from the page
	 *
	 * @return {jQuery}         Affected fields
	 */
	obj.construct.media_button = function( $fields ) {
		var $elements = $fields.filter( obj.selector.media_button );

		if ( typeof wp === 'undefined' || ! wp.media || ! wp.media.editor ) {
			return $elements;
		}

		$elements.each( function(){
			var $button = $( this ),
				input = $button.data( 'input' ),
				$field = $( '#' + input ),
				$name = $( '#' + input + '_name' );

			// Setup the WP Media for this slug
			var media = obj.media[ input ] = wp.media( {
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
					$field.change();
					$name.html( attachment.attributes.title );
					$name.attr( 'title', attachment.attributes.title );
				} );
			} );

			// We don't need the Media Library button
			media.on( 'open', function () {
				$( '.media-router .media-menu-item' ).first().trigger( 'click' );
			} );
		} );

		obj.$.container.on( 'click', obj.selector.media_button, function( e ) {
			e.preventDefault();
			var input = $( this ).data( 'input' );
			obj.media[ input ].open( input );
			return false;
		} );

		return $elements;
	};

	/**
	 * Triggers a change event on the given field
	 */
	obj.events.trigger_field_change = function() {
		$( this ).change();
	};

	/**
	 * Triggers the saving of credentials
	 */
	obj.events.trigger_save_credentials = function() {
		obj.save_credentials( $( this ).closest( '.enter-credentials' ) );
	};

	/**
	 * Suppress form submissions
	 */
	obj.events.suppress_submission = function( e ) {
		e.preventDefault();
	};

	// Run Init on Document Ready
	$( document ).ready( obj.init );
} )( jQuery, _, tribe_ea.fields );
