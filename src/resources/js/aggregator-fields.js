var tribe_ea = tribe_ea || {};

// Setup the global Variable
tribe_ea.fields = {
	// Store the Required Selectors
	slct: {
		container: '.tribe-ea',
		help: '.tribe-ea-help',
		dependent: '.tribe-ea-dependent',
		active: '.tribe-ea-active',
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
	l10n: window.tribe_l10n_ea_fields
};

( function( $, _, my ) {
	'use strict';

	/**
	 * Sets up the fields for EA pages
	 *
	 * @return void
	 */
	my.init = function() {
		my.$.container = $( my.slct.container );

		// Fetch All the Help Icons
		my.$.help = my.$.container.find( my.slct.help );
		my.$.help.bumpdown();

		// Update what fields we currently have to setup
		my.$.fields = my.$.container.find( my.slct.fields );

		// Setup each type of field
		$.each( my.construct, function( key, callback ){
			callback( my.$.fields );
		} );

		my.$.fields.on( 'change', function( e ) {
			var $field = $( this ),
				ID = $field.attr( 'id' ),
				value = $field.val();

			// We need an ID to make something depend on this
			if ( ! ID ) {
				return;
			}

			// Fetch dependent elements
			var $dependents = my.$.container.find( my.slct.dependent ).filter( '[data-depends="' + ID + '"]' );

			$dependents.each( function( k, dependent ) {
				var $dependent = $( dependent ),
					condition = $dependent.data( 'condition' ),
					isNotEmpty = $dependent.data( 'conditionNotEmpty' ) || $dependent.is( '[data-condition-not-empty]' ),
					isEmpty = $dependent.data( 'conditionEmpty' ) || $dependent.is( '[data-condition-empty]' );

				if ( isEmpty && '' == value ) {
					$dependent.addClass( my.slct.active.replace( '.', '' ) );
				} else if ( isNotEmpty && '' != value ) {
					$dependent.addClass( my.slct.active.replace( '.', '' ) );
				} else if ( _.isArray( condition ) && -1 !== _.findIndex( condition, value ) ) {
					$dependent.addClass( my.slct.active.replace( '.', '' ) );
				} else if ( value == condition ) {
					$dependent.addClass( my.slct.active.replace( '.', '' ) );
				} else {
					$dependent.removeClass( my.slct.active.replace( '.', '' ) );
				}
			} );
		} ).trigger( 'change' );
	};

	/**
	 * Better Search ID for Select2, compatible with WordPress ID from WP_Query
	 *
	 * @param  {object|string} e Searched object or the actual ID
	 * @return {string}   ID of the object
	 */
	my.searchId = function ( e ) {
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
		var $elements = $fields.filter( my.slct.dropdown ).not( '.select2-offscreen, .select2-container' );

		$elements.each(function(){
			var $select = $(this),
				args = {};

			// Better Method for finding the ID
			args.id = my.searchId;

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

				args.regexSeparatorString = args.regexSeparatorElements.join( '' )
				args.regexSplitString = args.regexSplitElements.join( '' )

				args.regexToken = new RegExp( args.regexSeparatorString, 'ig');
				args.regexSplit = new RegExp( args.regexSplitString, 'ig');
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
						var test_value = my.searchId( possible[0] );
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
						return data;
					}
				};

				// By default only sent the source
				args.ajax.data = function( search, page ) {
					return {
						action: 'tribe_ea_dropdown_' + source,
					};
				}

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
	my.construct.media_button = function( $fields ) {
		var $elements = $fields.filter( my.slct.media_button );

		if ( typeof wp === 'undefined' || ! wp.media || ! wp.media.editor ) {
			return $elements;
		}

		$elements.each( function(){
			var $button = $( this ),
				input = $button.data( 'input' ),
				$field = $( '#' + input );

			if ( my.media[ input ] ) {
				my.media[ input ].open();
				return;
			}

			// Setup the WP Media for this slug
			var media = my.media[ input ] = wp.media({
				title:$button.data( 'mediaTitle' ),
				library: { type: $button.data( 'mimeType' ) },
				multiple: false
			});

			// On select send to Select2
			media.on( 'select', function (){
				var state = media.state(),
					selection = state.get('selection');

				if ( ! selection ) {
					return;
				}

				selection.each( function( attachment ) {
					$field.select2( 'data', { id: attachment.attributes.id, text: attachment.attributes.title } );
				} );
			} );

			// We don't need the Media Library button
			media.on( 'open', function (){
				$( '.media-router .media-menu-item' ).first().trigger('click')
			} );

			my.$.container.on( 'click', my.slct.media_button, function(e) {
				e.preventDefault();
				media.open( $( this ) );
				return false;
			} );
		} );

		return $elements;
	}
	// Run Init on Document Ready
	$( document ).ready( my.init );
} )( jQuery, _, tribe_ea.fields );
