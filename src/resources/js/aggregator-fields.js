var tribe_aggregator = tribe_aggregator || {};

// Setup the global Variable
tribe_aggregator.fields = {
	// Store the Required Selectors
	selector: {
		container: '.tribe-ea',
		help: '.tribe-ea-help',
		fields: '.tribe-ea-field',
		dropdown: '.tribe-ea-dropdown',
		media_button: '.tribe-ea-media_button',
		datepicker: '.tribe-ea-datepicker'
	},

	media: {},

	// Store the jQuery elements
	$: {},

	// Store the methods for creating the fields
	construct: {},

	// Store L10N strings
	l10n: window.tribe_l10n_aggregator_fields
};

( function( $, _, my ) {
	'use strict';

	/**
	 * Sets up the fields for EA pages
	 *
	 * @return void
	 */
	my.init = function() {
		my.$.container = $( my.selector.container );

		// Update what fields we currently have to setup
		my.$.fields = my.$.container.find( my.selector.fields );

		// Setup each type of field
		$.each( my.construct, function( key, callback ){
			callback( my.$.fields );
		} );

		$( document ).on( 'click', '.enter-credentials .tribe-save', function() {
			var $container = $( this ).closest( '.enter-credentials' );
			var data = $( this ).closest( '.tribe-fieldset' ).find( 'input' ).serialize();

			var url = ajaxurl + '?action=tribe_aggregator_save_credentials&which=facebook';

			var jqxhr = $.post( url, data );
			jqxhr.done( function( response ) {
				if ( response.success ) {
					$container.addClass( 'credentials-entered' );
					$container.find( '#tribe-has-credentials' ).val( 1 ).change();
				}
			} );
		} );

		$( document ).on( 'submit', '.tribe-ea-tab-new', function( e ) {
			e.preventDefault();
		} );

		$( document ).on( 'click', '.tribe-preview', function( e ) {
			var $preview = $( this );
			var $form = $preview.closest( 'form' );
			var data = $form.serialize();
			var $preview_container = $( '.tribe-preview-container' );
			$preview_container.addClass( 'tribe-fetching' ).removeClass( 'tribe-fetch-error' );

			$preview.prop( 'disabled', true );

			var jqxhr = $.ajax( {
				type: 'POST',
				url: ajaxurl + '?action=tribe_aggregator_create_import',
				data: data,
				dataType: 'json'
			} );

			jqxhr.done( function( response ) {
				if ( ! response.success ) {
					$preview_container.removeClass( 'tribe-fetching').addClass( 'tribe-fetch-error' );
					$( '.tribe-fetch-error-message' ).html(
						[
							'<div class="notice notice-error">',
								'<p>',
									'<b>',
										tribe_l10n_aggregator_fields.preview_fetch_error_prefix,
									'</b>',
									' ' + response.data.message,
								'</p>',
							'</div>'
						].join( '' )
					);
					$preview.prop( 'disabled', false );
					return;
				}

				my.import_id = response.data.data.import_id;

				setTimeout( my.poll_for_results, 300 );
			} );
		} );
	};

	my.poll_for_results = function() {
		var jqxhr = $.ajax( {
			type: 'GET',
			url: ajaxurl + '?action=tribe_aggregator_fetch_import&import_id=' + my.import_id,
			dataType: 'json'
		} );

		jqxhr.done( function( response ) {
			if ( ! response.success ) {
				// @todo: output error
				return;
			}

			if ( 'success' !== response.data.status ) {
				setTimeout( my.poll_for_results, 300 );
			} else {
				var template = wp.template( 'preview' );
				$( '.tribe-ea-table-container' ).append( template( response.data.data ) );
				$( '.tribe-ea-table-container table').tribeDataTable( {
					lengthMenu: [
						[5, 10, 25, 50, -1],
						[5, 10, 25, 50, tribe_l10n_datatables.pagination.all ]
					],
					order: [
						[ 1, 'asc' ]
					],
					columnDefs: [
						{
							orderable: false,
							targets: 0
						}
					]
				} );

				var $preview_container = $( '.tribe-preview-container' );
				$preview_container.removeClass( 'tribe-fetching' ).addClass( 'tribe-fetched' );
			}
		} );
	};

	/**
	 * Better Search ID for Select2, compatible with WordPress ID from WP_Query
	 *
	 * @param  {object|string} e Searched object or the actual ID
	 * @return {string}   ID of the object
	 */
	my.search_id = function ( e ) {
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
	my.construct.dropdown = function( $fields ) {
		var $elements = $fields.filter( my.selector.dropdown ).not( '.select2-offscreen, .select2-container' );

		$elements.each(function(){
			var $select = $(this),
				args = {};

			if ( ! $select.is( 'select' ) ) {
				// Better Method for finding the ID
				args.id = my.search_id;
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
						var test_value = my.search_id( possible[0] );
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
						action: 'tribe_aggregator_dropdown_' + source,
					};
				};

				// If you want to create a diferent type of data for your AJAX call based on the source
				if ( 'Source Name' === source ){
				}
			}

			console.log( args );
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
	my.construct.media_button = function( $fields ) {
		var $elements = $fields.filter( my.selector.media_button );

		if ( typeof wp === 'undefined' || ! wp.media || ! wp.media.editor ) {
			return $elements;
		}

		$elements.each( function(){
			var $button = $( this ),
				input = $button.data( 'input' ),
				$field = $( '#' + input ),
				$name = $( '#' + input + '_name' );

			// Setup the WP Media for this slug
			var media = my.media[ input ] = wp.media( {
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
					$name.html( attachment.attributes.title );
					$name.attr( 'title', attachment.attributes.title );
				} );
			} );

			// We don't need the Media Library button
			media.on( 'open', function () {
				$( '.media-router .media-menu-item' ).first().trigger( 'click' );
			} );
		} );

		my.$.container.on( 'click', my.selector.media_button, function( e ) {
			e.preventDefault();
			var input = $( this ).data( 'input' );
			my.media[ input ].open( input );
			return false;
		} );

		return $elements;
	};

	// Run Init on Document Ready
	$( document ).ready( my.init );
} )( jQuery, _, tribe_aggregator.fields );
