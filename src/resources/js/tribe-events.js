/**
 * @file The core file for the events calendar plugin javascript.
 * This file must load on all front facing events pages and be the first file loaded after vendor dependencies.
 * @version 3.0
 */

/**
 * @namespace tribe_ev
 * @desc The tribe_ev namespace that stores all custom functions, data, application state and an empty events object to bind custom events to.
 * This Object Literal namespace loads for all tribe events pages and is by design fully public so that themers can hook in and/or extend anything they want from their own files.
 * @example <caption>Test for tribe_ev in your own js and then run one of our functions.</caption>
 * jQuery(document).ready(function ($) {
 *      if (Object.prototype.hasOwnProperty.call(window, 'tribe_ev')) {
 *          if(tribe_ev.fn.get_category() === 'Cats'){
 *              alert('Meow!');
 *          }
 *      }
 * });
 */

var tribe_ev = window.tribe_ev || {};

/**
 * @define {boolean} tribe_debug
 * @global tribe_debug is used both by closure compiler to strip debug code on min and as a failsafe short circuit if compiler fails to strip all debug strings.
 * @desc Setup safe enhanced console logging. See the link to get the available methods, then prefix with this short circuit: 'tribe_debug && '. tribe_debug is aliased in all tribe js doc readys as 'dbug'.
 * @link http://benalman.com/code/projects/javascript-debug/docs/files/ba-debug-js.html
 * @example <caption>EG: Place this at the very bottom of the doc ready for tribe-events.js. ALWAYS short circuit with 'tribe_debug && ' or 'dbug &&' if aliased as such.</caption> *
 *        tribe_debug && tec_debug.info('tribe-events.js successfully loaded');
 */

var tribe_debug = tribe_js_config.debug;

/*!
 * this debug code is stripped out by closure compiler so it is not present in the .min versions.
 */

// @ifdef DEBUG

/*!
 * JavaScript Debug - v0.4 - 6/22/2010
 * http://benalman.com/projects/javascript-debug-console-log/
 *
 * Copyright (c) 2010 "Cowboy" Ben Alman
 * Dual licensed under the MIT and GPL licenses.
 * http://benalman.com/about/license/
 *
 * With lots of help from Paul Irish!
 * http://paulirish.com/
 */

window.tec_debug = (function() {
	var window = this,
		aps = Array.prototype.slice,
		con = window.console,
		that = {},
		callback_func,
		callback_force,
		log_level = 9,
		log_methods = [ 'error', 'warn', 'info', 'debug', 'log' ],
		pass_methods = 'assert clear count dir dirxml exception group groupCollapsed groupEnd profile profileEnd table time timeEnd trace'.split( ' ' ),
		idx = pass_methods.length,
		logs = [];

	while ( --idx >= 0 ) {
		(function( method ) {

			that[ method ] = function() {
				log_level !== 0 && con && con[ method ]
				&& con[ method ].apply( con, arguments );
			}

		})( pass_methods[idx] );
	}

	idx = log_methods.length;
	while ( --idx >= 0 ) {
		(function( idx, level ) {

			that[ level ] = function() {
				var args = aps.call( arguments ),
					log_arr = [ level ].concat( args );

				logs.push( log_arr );
				exec_callback( log_arr );

				if ( !con || !is_level( idx ) ) {
					return;
				}

				con.firebug ? con[ level ].apply( window, args )
					: con[ level ] ? con[ level ]( args )
					: con.log( args );
			};

		})( idx, log_methods[idx] );
	}

	function exec_callback( args ) {
		if ( callback_func && (callback_force || !con || !con.log) ) {
			callback_func.apply( window, args );
		}
	}

	that.setLevel = function( level ) {
		log_level = typeof level === 'number' ? level : 9;
	};

	function is_level( level ) {
		return log_level > 0
			? log_level > level
			: log_methods.length + log_level <= level;
	}

	that.setCallback = function() {
		var args = aps.call( arguments ),
			max = logs.length,
			i = max;

		callback_func = args.shift() || null;
		callback_force = typeof args[0] === 'boolean' ? args.shift() : false;

		i -= typeof args[0] === 'number' ? args.shift() : max;

		while ( i < max ) {
			exec_callback( logs[i++] );
		}
	};

	return that;
})();

if ( Object.prototype.hasOwnProperty.call( window, 'tribe_ev' ) ) {
	tribe_ev.diagnostics = {
		init: []
	};
}
// @endif

/**
 * @global
 * @desc Test for localstorage support. Returns false if not available and tribe_storage as a method if true.
 * @example
 * if (tribe_storage) {
 *      tribe_storage.setItem('cats', 'hairball');
 *      tribe_storage.getItem('cats');
 * }
 */

var tribe_storage, t_fail, t_uid;
try {
	t_uid = new Date;
	(tribe_storage = window.localStorage).setItem( t_uid, t_uid );
	t_fail = tribe_storage.getItem( t_uid ) != t_uid;
	tribe_storage.removeItem( t_uid );
	t_fail && (tribe_storage = false);
} catch ( e ) {
	tribe_storage = false;
}

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
	"default":         "ddd mmm dd yyyy HH:MM:ss",
	"tribeQuery":      "yyyy-mm-dd",
	"tribeMonthQuery": "yyyy-mm",
	"0":               'yyyy-mm-dd',
	"1":               'm/d/yyyy',
	"2":               'mm/dd/yyyy',
	"3":               'd/m/yyyy',
	"4":               'dd/mm/yyyy',
	"5":               'm-d-yyyy',
	"6":               'mm-dd-yyyy',
	"7":               'd-m-yyyy',
	"8":               'dd-mm-yyyy',
	"9":               'yyyy.mm.dd',
	"10":              'mm.dd.yyyy',
	"11":              'dd.mm.yyyy',
	"m0":              'yyyy-mm',
	"m1":              'm/yyyy',
	"m2":              'mm/yyyy',
	"m3":              'm/yyyy',
	"m4":              'mm/yyyy',
	"m5":              'm-yyyy',
	"m6":              'mm-yyyy',
	"m7":              'm-yyyy',
	"m8":              'mm-yyyy',
	"m9":              'yyyy.mm',
	"m10":             'mm.yyyy',
	"m11":             'mm.yyyy'
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

/**
 * A collection of utility functions.
 *
 * @type {{getQueryVars: tribeUtils.getQueryVars}}
 */
var tribeUtils = {
	/**
	 * Searches a URL, or the current page URL, for query vars and returns an object listing
	 * them where each query var name is a property associated to its value(s).
	 *
	 * @param string location Def. to `location`, an optional URL to scan for query vars.
	 *
	 * @returns {{}} An array containing the query vars as properties.
	 */
	'getQueryVars': function ( url ) {
		var queryVars = {},
			url = url || location;
		url.search.substr( 1 ).split( '&' ).forEach( function ( queryVar ) {
			queryVars[ queryVar.split( '=' )[0] ] = queryVar.split( '=' )[1];
		} );

		return queryVars;
	}
};

Date.prototype.format = function( mask, utc ) {
	return tribeDateFormat( this, mask, utc );
};

(function() {

	/**
	 * @function tribe_tmpl
	 * @desc Javascript templating function based on John Resigs micro-templating approach and expanded upon by cho45. Tags and function name changed here to avoid conflicts.
	 * @param id The javascript template
	 * @param data The data object
	 */

	/**
	 * https://github.com/cho45/micro-template.js
	 * (c) cho45 http://cho45.github.com/mit-license
	 */
	function tribe_tmpl( id, data ) {
		var me = arguments.callee;
		if ( !me.cache[id] ) {
			me.cache[id] = (function() {
				var name = id, string = /^[\w\-]+$/.test( id ) ? me.get( id ) : (name = 'template(string)', id); // no warnings
				var line = 1, body = (
					"try { " +
					(me.variable ? "var " + me.variable + " = this.stash;" : "with (this.stash) { ") +
					"this.ret += '" +
					string.
					replace( /\[\[/g, '\x11' ).replace( /\]\]/g, '\x13' ). // if you want other tag, just edit this line
					replace( /'(?![^\x11\x13]+?\x13)/g, '\\x27' ).
					replace( /^\s*|\s*$/g, '' ).
					replace( /\n/g,function() {
						return "';\nthis.line = " + (++line) + "; this.ret += '\\n"
					} ).
					replace( /\x11=raw(.+?)\x13/g, "' + ($1) + '" ).
					replace( /\x11=(.+?)\x13/g, "' + this.escapeHTML($1) + '" ).
					replace( /\x11(.+?)\x13/g, "'; $1; this.ret += '" ) +
					"'; " + (me.variable ? "" : "}") + "return this.ret;" +
					"} catch (e) { throw 'TemplateError: ' + e + ' (on " + name + "' + ' line ' + this.line + ')'; } " +
					"//@ sourceURL=" + name + "\n" // source map
				).replace( /this\.ret \+= '';/g, '' );
				var func = new Function( body );
				var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '\x22': '&#x22;', '\x27': '&#x27;' };
				var escapeHTML = function( string ) {
					return ('' + string).replace( /[&<>\'\"]/g, function( _ ) {
						return map[_]
					} )
				};
				return function( stash ) {
					return func.call( me.context = { escapeHTML: escapeHTML, line: 1, ret: '', stash: stash } )
				};
			})();
		}
		return data ? me.cache[id]( data ) : me.cache[id];
	}

	tribe_tmpl.cache = {};
	tribe_tmpl.get = function( id ) {
		return document.getElementById( id ).innerHTML
	};

	/**
	 * Extended template function:
	 *   requires: basic template() function
	 *   provides:
	 *     include(id)
	 *     wrapper(id, function () {})
	 */
	function tribe_tmpl_extended( id, data ) {
		var fun = function( data ) {
			data.include = function( name, args ) {
				var stash = {};
				for ( var key in tribe_tmpl.context.stash ) {
					if ( tribe_tmpl.context.stash.hasOwnProperty( key ) ) {
						stash[key] = tribe_tmpl.context.stash[key];
					}
				}
				if ( args ) {
					for ( var key in args ) {
						if ( args.hasOwnProperty( key ) ) {
							stash[key] = args[key];
						}
					}
				}
				var context = tribe_tmpl.context;
				context.ret += tribe_tmpl( name, stash );
				tribe_tmpl.context = context;
			};

			data.wrapper = function( name, fun ) {
				var current = tribe_tmpl.context.ret;
				tribe_tmpl.context.ret = '';
				fun.apply( tribe_tmpl.context );
				var content = tribe_tmpl.context.ret;
				var orig_content = tribe_tmpl.context.stash.content;
				tribe_tmpl.context.stash.content = content;
				tribe_tmpl.context.ret = current + tribe_tmpl( name, tribe_tmpl.context.stash );
				tribe_tmpl.context.stash.content = orig_content;
			};

			return tribe_tmpl( id, data );
		};

		return data ? fun( data ) : fun;
	}

	tribe_tmpl.get = function( id ) {
		var fun = tribe_tmpl_extended.get;
		return fun ? fun( id ) : document.getElementById( id ).innerHTML;
	};

	this.tribe_tmpl = tribe_tmpl;
	this.tribe_tmpl_extended = tribe_tmpl_extended;


})();

/**
 * @external "jQuery.fn"
 * @desc The jQuery plugin namespace.
 */


(function( $, undefined ) {

	/**
	 * @function external:"jQuery.fn".tribe_clear_form
	 * @desc Clear a forms inputs with jquery.
	 * @example <caption>Clear a form with the forms id as a selector.</caption>
	 * $('#myForm').tribe_clear_form();
	 */
	$.fn.tribe_clear_form = function() {
		return this.each( function() {
			var type = this.type, tag = this.tagName.toLowerCase();
			if ( tag == 'form' ) {
				return $( ':input', this ).tribe_clear_form();
			}
			if ( type == 'text' || type == 'password' || tag == 'textarea' ) {
				this.value = '';
			}
			else if ( type == 'checkbox' || type == 'radio' ) {
				this.checked = false;
			}
			else if ( tag == 'select' ) {
				this.selectedIndex = 0;
			}
		} );
	};
	/**
	 * @function external:"jQuery.fn".tribe_has_attr
	 * @desc Check if a given element has an attribute.
	 * @example if($('#myLink').tribe_has_attr('data-cats')) {true} else {false}
	 */
	$.fn.tribe_has_attr = function( name ) {
		return this.attr( name ) !== undefined;
	};
	/**
	 * @function external:"jQuery.fn".tribe_spin
	 * @desc Shows loading spinners for events ajax interactions.
	 * @example $('#myElement').tribe_spin();
	 */
	$.fn.tribe_spin = function() {
		var $loadingImg = $( '.tribe-events-ajax-loading:first' ).clone().addClass( 'tribe-events-active-spinner' );
		$loadingImg.prependTo( document.getElementById( 'tribe-events-content' ) );
		$( this ).addClass( 'tribe-events-loading' ).css( 'opacity', .25 )
	};

	if ( "undefined" !== typeof $.fn.datepicker && "undefined" !== typeof $.fn.datepicker.noConflict ) {
		var datepicker = $.fn.datepicker.noConflict();
		$.fn.bootstrapDatepicker = datepicker;
	}

	if ( "undefined" !== typeof tribe_bootstrap_datepicker_strings && tribe_bootstrap_datepicker_strings.dates != null ) {
		$.fn.bootstrapDatepicker.dates['en'] = tribe_bootstrap_datepicker_strings.dates;
	}

})( jQuery );

(function( window, document, $, dbug, config, undefined ) {
	/**
	 * @namespace tribe_ev.fn
	 * @desc tribe_ev.fn namespace stores all the custom functions used throughout the core events plugin.
	 */
	tribe_ev.fn = {
		/**
		 * @function tribe_ev.fn.current_date
		 * @desc tribe_ev.fn.current_date simply gets the current date in javascript and formats it to yyyy-mm-dd for use were needed.
		 * @example var right_now = tribe_ev.fn.current_date();
		 */
		current_date          : function() {

			var today = new Date(),
				dd = today.getDate(),
				mm = today.getMonth() + 1,
				yyyy = today.getFullYear();

			if ( dd < 10 ) {
				dd = '0' + dd
			}
			if ( mm < 10 ) {
				mm = '0' + mm
			}
			return yyyy + '-' + mm + '-' + dd;
		},
		/**
		 * @function tribe_ev.fn.disable_inputs
		 * @desc tribe_ev.fn.disable_inputs disables all inputs of a specified type inside a parent element, and also disables select2 selects if it discovers any.
		 * @param {String} parent The top level element you would like all child inputs of the specified type to be disabled for.
		 * @param {String} type A single or comma separated string of the type of inputs you would like disabled.
		 * @example <caption>Disable all inputs and selects for #myForm.</caption>
		 * tribe_ev.fn.disable_inputs( '#myForm', 'input, select' );
		 */
		disable_inputs        : function( parent, type ) {
			$( parent ).find( type ).prop( 'disabled', true );
			if ( $( parent ).find( '.select2-container' ).length ) {
				$( parent ).find( '.select2-container' ).each( function() {
					var s2_id = $( this ).attr( 'id' );
					var $this = $( '#' + s2_id );
					$this.select2( "disable" );
				} );
			}
		},
		/**
		 * @function tribe_ev.fn.disable_empty
		 * @desc tribe_ev.fn.disable_empty disables all empty inputs of a specified type inside a parent element.
		 * @param {String} parent The top level element you would like all empty child inputs of the specified type to be disabled for.
		 * @param {String} type A single or comma separated string of the type of empty inputs you would like disabled.
		 * @example <caption>Disable all empty inputs and selects for #myForm.</caption>
		 * tribe_ev.fn.disable_empty( '#myForm', 'input, select' );
		 */
		disable_empty         : function( parent, type ) {
			$( parent ).find( type ).each( function() {
				if ( $( this ).val() === '' ) {
					$( this ).prop( 'disabled', true );
				}
			} );
		},
		/**
		 * @function tribe_ev.fn.enable_inputs
		 * @desc tribe_ev.fn.enable_inputs enables all inputs of a specified type inside a parent element, and also enables select2 selects if it discovers any.
		 * @param {String} parent The top level element you would like all child inputs of the specified type to be disabled for.
		 * @param {String} type A single or comma separated string of the type of inputs you would like enabled.
		 * @example <caption>Enable all inputs and selects for #myForm.</caption>
		 * tribe_ev.fn.enable_inputs( '#myForm', 'input, select' );
		 */
		enable_inputs         : function( parent, type ) {
			$( parent ).find( type ).prop( 'disabled', false );
			if ( $( parent ).find( '.select2-container' ).length ) {
				$( parent ).find( '.select2-container' ).each( function() {
					var s2_id = $( this ).attr( 'id' );
					var $this = $( '#' + s2_id );
					$this.select2( "enable" );
				} );
			}
		},

		/**
		 * @function tribe_ev.fn.ensure_datepicker_i18n
		 * @desc tribe_ev.fn.ensure_datepicker_i18n Ensures some specific strings for Bootstrap Datepicker are translatable. We manually enforce strings in this way because
		 * we do not use locales for the datepicker other than the default 'en' locale, since we provide the strings via PHP and don't use the datepicker library's versions of
		 * non-English locales.
		 * @see https://bootstrap-datepicker.readthedocs.io/en/latest/i18n.html
		 */
		ensure_datepicker_i18n : function() {

			if ( 'undefined' == typeof $.fn.bootstrapDatepicker ) {
				return;
			}
			var tribe_l10n = window.tribe_l10n_datatables || {};
			var datepickeri18n = tribe_l10n.datepicker || {};
			$.fn.bootstrapDatepicker.dates['en'].days        = datepickeri18n.dayNames;
			$.fn.bootstrapDatepicker.dates['en'].daysShort   = datepickeri18n.dayNamesShort;
			$.fn.bootstrapDatepicker.dates['en'].daysMin     = datepickeri18n.dayNamesMin;
			$.fn.bootstrapDatepicker.dates['en'].months      = datepickeri18n.monthNames;
			// Provide a fallback as it might not be always available
			if ( datepickeri18n.monthNamesMin ) {
				$.fn.bootstrapDatepicker.dates[ 'en' ].monthsShort = datepickeri18n.monthNamesMin;
			}
			$.fn.bootstrapDatepicker.dates['en'].today       = datepickeri18n.today;
			$.fn.bootstrapDatepicker.dates['en'].clear       = datepickeri18n.clear;
		},

		/**
		 * @function tribe_ev.fn.execute_resize
		 * @desc tribe_ev.fn.execute_resize groups together functions that should execute at the end of the window resize event.
		 */
		execute_resize        : function() {

			var prev_width = tribe_ev.data.v_width;
			tribe_ev.fn.update_viewport_variables();
			if ( prev_width !== tribe_ev.data.v_width ) {
				tribe_ev.fn.mobile_class();

				/**
				 * DEPRECATED: tribe_ev_resizeComplete has been deprecated in 4.0. Use resize-complete.tribe instead
				 */
				$( tribe_ev.events ).trigger( 'tribe_ev_resizeComplete' );
				$( tribe_ev.events ).trigger( 'resize-complete.tribe' );
			}

		},
		/**
		 * @function tribe_ev.fn.get_base_url
		 * @desc tribe_ev.fn.get_base_url can be used on any events view to get the base_url for that view, even when on a category subset for that view.
		 * @returns {String} Either an empty string or base url if data-baseurl is found on #tribe-events-header.
		 * @example var base_url = tribe_ev.fn.get_base_url();
		 */
		get_base_url          : function() {
			var base_url      = '';
			var $event_header = $( document.getElementById( 'tribe-events-header' ) );
			var $canonical    = $( 'link[rel="canonical"]' );

			if ( $canonical.length ) {
				// use the canonical URL if it is available (it should be)
				base_url = $canonical.attr( 'href' );
			} else if ( $event_header.length ) {
				// failover to the baseurl of the event header
				base_url = $event_header.data( 'baseurl' );
			} else {
				// use the current URL as a last ditch effort
				base_url = window.location.origin + window.location.path;
			}

			return base_url;
		},
		/**
		 * @function tribe_ev.fn.update_base_url
		 * @desc tribe_ev.fn.update_base_url can be used on any events view to update base_url for that view
		 */
		update_base_url: function( url ) {
			var $event_header = $( document.getElementById( 'tribe-events-header' ) );
			var $canonical    = $( 'link[rel="canonical"]' );

			if ( $canonical.length ) {
				// use the canonical URL if it is available (it should be)
				$canonical.attr( 'href', url );
			} else if ( $event_header.length ) {
				// failover to the baseurl of the event header
				$event_header.data( 'baseurl', url ).attr( 'data-baseurl', url );
			}
		},
		/**
		 * @function tribe_ev.fn.get_category
		 * @desc tribe_ev.fn.get_category can be used on any events view to get the category for that view.
		 * @returns {String} Either an empty string or category slug if data-category is found on #tribe-events.
		 * @example var cat = tribe_ev.fn.get_category();
		 */
		get_category          : function() {
			if ( tribe_ev.fn.is_category() ) {
				return $( document.getElementById( 'tribe-events' ) ).data( 'category' );
			}
			else {
				return '';
			}
		},
		/**
		 * @function tribe_ev.fn.get_day
		 * @desc tribe_ev.fn.get_day can be used to check the event bar for a day value that was set by the user when using the datepicker.
		 * @returns {String|Number} Either an empty string or day number if #tribe-bar-date-day has a val() set by user interaction.
		 * @example var day = tribe_ev.fn.get_day();
		 */
		get_day               : function() {
			var dp_day = '';
			if ( $( document.getElementById( 'tribe-bar-date' ) ).length ) {
				dp_day = $( document.getElementById( 'tribe-bar-date-day' ) ).val();
			}
			// @ifdef DEBUG
			dbug && tec_debug.info( 'TEC Debug: tribe_ev.fn.get_day returned this date: "' + dp_day + '".' );
			// @endif
			return dp_day;
		},
		/**
		 * @function tribe_ev.fn.get_params
		 * @desc tribe_ev.fn.get_params returns the params of the current document.url.
		 * @returns {String} any url params sans "?".
		 * @example var params = tribe_ev.fn.get_params();
		 */
		get_params            : function() {
			return location.search.substr( 1 );
		},
		/**
		 * @function tribe_ev.fn.get_url_param
		 * @desc tribe_ev.fn.get_url_param returns the value of a passed param name if set.
		 * @param {String} name The name of the url param value desired.
		 * @returns {String|Null} the value of a parameter if set or null if not.
		 * @example var param = tribe_ev.fn.get_url_param('category');
		 */
		get_url_param         : function( name ) {
			return decodeURIComponent( (new RegExp( '[?|&]' + name + '=' + '([^&;]+?)(&|#|;|$)' ).exec( location.search ) || [, ""])[1].replace( /\+/g, '%20' ) ) || null;
		},
		/**
		 * @function tribe_ev.fn.in_params
		 * @desc tribe_ev.fn.in_params returns the value of a passed param name if set.
		 * @param {String} params The parameter string you would like to search for a term.
		 * @param {String} term The name of the url param value you are checking for.
		 * @returns {Number} Returns index if term is present in params, or -1 if not found.
		 * @example
		 * if (tribe_ev.fn.in_params(tribe_ev.data.params, "tabby") >= 0)){
         *     // tabby is in params
         * } else {
         *     // tabby is not in params
         * }
		 */
		in_params             : function( params, term ) {
			return params.toLowerCase().indexOf( term );
		},
		/**
		 * @function tribe_ev.fn.invalid_date
		 * @desc tribe_ev.fn.invalid_date tests a date object and confirms if it is actually valid by forcing parseDate on it.
		 * @returns {Boolean} Returns true if date is invalid, false if valid.
		 * @example  if(tf.invalid_date(ts.date)) return;
		 */
		invalid_date          : function( date ) {

			date = new Date( date );
			return isNaN( date );

		},
		invalid_date_in_params: function( params ) {

			if ( params.hasOwnProperty( 'tribe-bar-date' ) ) {
				var date = new Date( params['tribe-bar-date'] );
				return isNaN( date );
			}
			return false;

		},
		/**
		 * @function tribe_ev.fn.is_category
		 * @desc tribe_ev.fn.is_category test for whether the view is a category subpage in the pretty permalink system.
		 * @returns {Boolean} Returns true if category page, false if not.
		 * @example if (tribe_ev.fn.is_category()){ true } else { false }
		 */
		is_category           : function() {
			var $tribe_events = $( document.getElementById( 'tribe-events' ) );
			return ($tribe_events.length && $tribe_events.tribe_has_attr( 'data-category' ) && $tribe_events.data( 'category' ) !== '') ? true : false;
		},

		/**
		 * Indicates if the current view is a featured events archive.
		 *
		 * @return {boolean}
		 */
		is_featured: function() {
			return $( document.getElementById( 'tribe-events' ) ).data( 'featured' ) == '1';
		},

		/**
		 * @function tribe_ev.fn.mobile_class
		 * @desc tribe_ev.fn.mobile_class adds or removes a mobile class from the body element based on the mobile breakpoint.
		 */
		mobile_class          : function() {
			var $body = $( 'body' );

			if ( tribe_ev.data.v_width <= tribe_ev.data.mobile_break ) {
				$body.addClass( 'tribe-mobile' );
			} else {
				$body.removeClass( 'tribe-mobile' );
			}

		},

		/**
		 * @function tribe_ev.fn.mobile_view_redirect
		 * @desc tribe_ev.fn.mobile_view_redirect Will redirect mobile users to the correct default view, this will only work if used after events-bar is loaded
		 */
		maybe_default_view_change   : function() {
			// if we don't these we can't do anything
			if (
				// if we already redirected do not do it again to enable user to change views
				tribe_ev.data.redirected_view ||

				// There is no default View set
				! tribe_ev.data.default_view ||

				// There is no default mobile View set
				! tribe_ev.data.default_mobile_view ||

				// The mobile and normal default views are the same
				tribe_ev.data.default_view == tribe_ev.data.default_mobile_view ||

				// There is no View set
				! tribe_ev.state.view ||

				// We are on the default mobile view
				tribe_ev.data.default_mobile_view == tribe_ev.state.view
			) {
				return false;
			}

			var $body = $( 'body' );

			// Not a Mobile Call
			if ( ! $body.hasClass( 'tribe-mobile' ) ) {
				return false;
			}

			var $views             = $( '.tribe-bar-views-option' );
			var view_class_filter  = '#tribe-bar-views-option-' + tribe_ev.data.default_mobile_view;
			var $default_view_link = $views.filter( view_class_filter );
			$( view_class_filter ).data( 'redirected', true );

			// Actually do the Changing View
			$default_view_link.trigger( 'click' );
		},

		/**
		 * @function tribe_ev.fn.parse_string
		 * @desc tribe_ev.fn.parse_string converts a string to an object.
		 * @param {String} string The string to be converted.
		 * @returns {Object} Returns mapped object.
		 * @example if (tribe_ev.fn.is_category()){ true } else { false }
		 */
		parse_string          : function( string ) {
			var map = {};
			string.replace( /([^&=]+)=?([^&]*)(?:&+|$)/g, function( match, key, value ) {
				(map[key] = map[key] || []).push( value );
			} );
			// @ifdef DEBUG
			dbug && tec_debug.info( 'TEC Debug: tribe_ev.fn.parse_string returned this map:', map );
			// @endif
			return map;
		},
		/**
		 * @function tribe_ev.fn.pre_ajax
		 * @desc tribe_ev.fn.pre_ajax allows for functions to be executed before ajax begins.
		 * @param {Function} callback The callback function, expected to be an ajax function for one of our views.
		 */
		pre_ajax              : function( callback ) {
			if ( callback && typeof( callback ) === "function" ) {
				callback();
			}
		},
		/**
		 * @function tribe_ev.fn.scroll_to
		 * @desc tribe_ev.fn.scroll_to animates the body to the target with the passed duration and offset.
		 * @param {String} target the id of the target to scroll the body to.
		 * @param {Number} offset the vertical offset from the target..
		 * @param {Number} duration the duration of the scroll animation.
		 */
		scroll_to             : function( target, offset, duration ) {
			$( 'html, body' ).stop().animate( {scrollTop: $( target ).offset().top - offset}, {duration: duration} );
		},
		/**
		 * @function tribe_ev.fn.serialize
		 * @desc tribe_ev.fn.serialize serializes the passed input types. Enable/disable stack in place to protect inputs during process, especially for live ajax mode.
		 * @param {String} form The form element.
		 * @param {String} type The input types to be serialized.
		 * @returns {String} Returns a param string of populated inputs.
		 * @example tribe_ev.fn.serialize('#myForm', 'input, select');
		 */
		serialize             : function( form, type ) {
			tribe_ev.fn.enable_inputs( form, type );
			tribe_ev.fn.disable_empty( form, type );
			var params = $( form ).serialize();
			tribe_ev.fn.disable_inputs( form, type );
			// @ifdef DEBUG
			dbug && params && tec_debug.info( 'TEC Debug: tribe_ev.fn.serialize returned these params: "' + params );
			// @endif
			return params;
		},
		/**
		 * @function tribe_ev.fn.set_form
		 * @desc tribe_ev.fn.set_form takes a param string and sets a forms inputs to the values received. Extended in the Query Filters plugin.
		 * @param {String} params The params to be looped over and applied to the named input. Needed for back button browser history when forms are outside of the ajax area.
		 * @example <caption>Set all inputs in a form(s) to the values in a param string retrieved from the history object on popstate.</caption>
		 * $(window).on('popstate', function (event) {
		 *		var state = event.originalEvent.state;
		 *		if (state) {
		 *		 	tribe_ev.state.params = state.tribe_params;
		 *		 	// do something magical to restore query state like ajax, then set the forms to match the history state like so:
		 *			tribe_ev.fn.set_form(tribe_ev.state.params);
		 *		}
		 *	});
		 */
		set_form              : function( params ) {
			var $body = $( 'body' ),
				$tribe_bar = $( document.getElementById( 'tribe-bar-form' ) );

			$body.addClass( 'tribe-reset-on' );

			if ( $tribe_bar.length ) {
				$tribe_bar.tribe_clear_form();
			}

			params = tribe_ev.fn.parse_string( params );

			$.each( params, function( key, value ) {
				if ( key !== 'action' ) {
					var name = decodeURIComponent( key ),
						$target = '';
					if ( value.length === 1 ) {
						if ( Array.isArray( value ) ) {
						  value = value[0];
						}
						value = decodeURIComponent( value.replace( /\+/g, '%20' ) );
						if ( $( '[name="' + name + '"]' ).is( 'input[type="text"], input[type="hidden"]' ) ) {
							$( '[name="' + name + '"]' ).val( value );
						}
						else if ( $( '[name="' + name + '"][value="' + value + '"]' ).is( ':checkbox, :radio' ) ) {
							$( '[name="' + name + '"][value="' + value + '"]' ).prop( "checked", true );
						}
						else if ( $( '[name="' + name + '"]' ).is( 'select' ) ) {
							$( 'select[name="' + name + '"] option[value="' + value + '"]' ).attr( 'selected', true );
						}
					}
					else {
						for ( var i = 0; i < value.length; i++ ) {
							$target = $( '[name="' + name + '"][value="' + value[i] + '"]' );
							if ( $target.is( ':checkbox, :radio' ) ) {
								$target.prop( "checked", true );
							}
							else {
								$( 'select[name="' + name + '"] option[value="' + value[i] + '"]' ).attr( 'selected', true );
							}
						}
					}
				}
			} );

			$body.removeClass( 'tribe-reset-on' );
			// @ifdef DEBUG
			dbug && tec_debug.info( 'TEC Debug: tribe_ev.fn.set_form fired these params: "' + params );
			// @endif
		},
		/**
		 * @function tribe_ev.fn.setup_ajax_timer
		 * @desc tribe_ev.fn.setup_ajax_timer is a simple function to add a delay to the execution of a passed callback function, in our case ajax hence the name.
		 * @param {Function} callback Used to delay ajax execution when in live ajax mode.
		 * @example <caption>Run some crazy ajax.</caption>
		 * tribe_ev.fn.setup_ajax_timer( function() {
		 *		run_some_crazy_ajax();
		 * });
		 */
		setup_ajax_timer      : function( callback ) {
			var timer = 500;
			clearTimeout( tribe_ev.state.ajax_timer );
			if ( !tribe_ev.tests.reset_on() ) {
				tribe_ev.state.ajax_timer = setTimeout( function() {
					callback();
				}, timer );
				// @ifdef DEBUG
				dbug && tec_debug.info( 'TEC Debug: tribe_ev.fn.setup_ajax_timer fired with a timeout of "' + timer + '" ms' );
				// @endif
			}
		},
		/**
		 * @function tribe_ev.fn.snap
		 * @desc tribe_ev.fn.snap uses jquery to bind a handler to a trigger_parent which uses bubbling of a click event from the trigger to position the document to the passed container. Has an offset of -120 px to get some breathing room.
		 * @param {String} container the jquery selector to send the document to.
		 * @param {String} trigger_parent the persistent element to bind the handler to.
		 * @param {String} trigger the trigger for the click event
		 * @example <caption>"Snap" the document 120 px above the tribe bar when a footer nav link is clicked.</caption>
		 *        tribe_ev.fn.snap('#tribe-bar-form', '#tribe-events', '#tribe-events-footer a');
		 */
		snap                  : function( container, trigger_parent, trigger ) {
			$( trigger_parent ).on( 'click', trigger, function( e ) {
				e.preventDefault();
				$( 'html, body' ).animate( {scrollTop: $( container ).offset().top - 120}, {duration: 0} );
			} );
		},

		/**
		 * @function tribe_ev.fn.tooltips
		 * @desc tribe_ev.fn.tooltips binds the event handler that covers all tooltip hover events for the various views. Extended in tribe-events-pro.js for the pro views. One of the reasons both these files must load FIRST in the tribe events js stack at all times.
		 * @example <caption>It's really not that hard... Get yourself inside a doc ready and...</caption>
		 *        tribe_ev.fn.tooltips();
		 */
		tooltips                 : function() {
			var $container    = $( document.getElementById( 'tribe-events' ) );
			var $body         = $( 'body' );
			var is_shortcode  = $container.hasClass( 'tribe-events-shortcode' );
			var is_month_view = $container.hasClass( 'view-month' ) || $body.hasClass( 'events-gridview' );
			var is_week_view  = $container.hasClass( 'view-week' ) || $body.hasClass( 'tribe-events-week' );
			var is_photo_view = $container.hasClass( 'view-photo' ) || $body.hasClass( 'tribe-events-photo' );
			var is_day_view   = $container.hasClass( 'view-day' ) || $body.hasClass( 'tribe-events-day' );
			var is_list_view  = $container.hasClass( 'view-list' ) || $body.hasClass( 'events-list' );
			var is_map_view   = $container.hasClass( 'view-map' ) || $body.hasClass( 'tribe-events-map' );
			var is_single     = $body.hasClass( 'single-tribe_events' );

			$container.on( 'mouseenter', 'div[id*="tribe-events-event-"], div.event-is-recurring', function() {
				var bottomPad = 0;
				var $this     = $( this );
				var $tip;

				if ( is_month_view ) { // Cal View Tooltips
					bottomPad = $this.find( 'a' ).outerHeight() + 16;
				} else if ( is_single || is_day_view || is_list_view ) { // Single/List View Recurring Tooltips
					bottomPad = $this.outerHeight() + 12;
				} else if ( is_photo_view ) { // Photo View
					bottomPad = $this.outerHeight() + 10;
				}

				// Widget Tooltips
				if ( $this.parents( '.tribe-events-calendar-widget' ).length ) {
					bottomPad = $this.outerHeight() - 6;
				}

				if ( ! is_week_view || is_shortcode ) {
					if ( is_month_view || is_shortcode ) {
						$tip = $this.find( '.tribe-events-tooltip' );

						if ( ! $tip.length ) {
							var data = $this.data( 'tribejson' );

							if ( typeof data == 'string' ) {
								data = $.parseJSON( data );
							}

							var tooltip_template = $this.hasClass( 'tribe-event-featured' )
									? 'tribe_tmpl_tooltip_featured'
									: 'tribe_tmpl_tooltip';

							$this.append( tribe_tmpl( tooltip_template, data ) );

							$tip = $this.find( '.tribe-events-tooltip' );
						}

						// Look for the distance between top of tooltip and top of visible viewport.
						var dist_to_top = $this.offset().top - ( $( window ).scrollTop() + 50 ); // The +50 is some padding for a more aesthetically-pleasing view.
						var tip_height  = $tip.outerHeight();

						// If true, tooltip is near top of viewport, so tweak some values to keep the tooltip fully in-view.
						if ( dist_to_top < tip_height ) {
							bottomPad = -tip_height;
							$tip.addClass( 'tribe-events-tooltip-flipdown' );
						}

						$tip.css( 'bottom', bottomPad ).stop( true, false ).show();
					} else {
						$this.find( '.tribe-events-tooltip' ).css( 'bottom', bottomPad ).stop( true, false ).show();
					}
				}

			} ).on( 'mouseleave', 'div[id*="tribe-events-event-"], div[id*="tribe-events-daynum-"]:has(a), div.event-is-recurring', function() {

				var $tip = $( this ).find( '.tribe-events-tooltip' );

				$tip.stop( true, false ).fadeOut( 500, function() {
					$tip.removeClass( 'tribe-events-tooltip-flipdown' );
				} );

			} );
		},
		/**
		 * @function tribe_ev.fn.update_picker
		 * @desc tribe_ev.fn.update_picker Updates the custom bootstrapDatepicker if it and the event bar is present, or only the event bar input if it is present.
		 * @param {String} date The date string to update picker or input with.
		 * @example <caption>Bind a handler that updates the datepicker if present with the date, in this case harvested from a data attribute on the link.</caption>
		 * $('#tribe-events').on('click', '.tribe-events-nav-previous a', function (e) {
		 *     e.preventDefault();
		 *     var $this = $(this);
		 *     tribe_ev.state.date = $this.attr("data-day");
		 *     tribe_ev.fn.update_picker(tribe_ev.state.date);
		 * });
		 */
		update_picker            : function( date ) {
			var $bar_date = $( "#tribe-bar-date" );
			if ( $().bootstrapDatepicker && $bar_date.length ) {
				// for ie8 and under
				tribe_ev.state.updating_picker = true;
				if ( window.attachEvent && !window.addEventListener ) {
					$bar_date.bootstrapDatepicker( "remove" );
					$bar_date.val( '' );
					$bar_date.bootstrapDatepicker( tribe_ev.data.datepicker_opts );
				}
				$bar_date.bootstrapDatepicker( "setDate", date );
				tribe_ev.state.updating_picker = false;
				// @ifdef DEBUG
				dbug && tec_debug.info( 'TEC Debug: tribe_ev.fn.update_picker sent "' + date + '" to the boostrapDatepicker' );
				// @endif
			}
			else if ( $bar_date.length ) {
				$bar_date.val( date );
				// @ifdef DEBUG
				dbug && tec_debug.warn( 'TEC Debug: tribe_ev.fn.update_picker sent "' + date + '" to ' + $bar_date );
				// @endif
			}
			else {
				// @ifdef DEBUG
				dbug && tec_debug.warn( 'TEC Debug: tribe_ev.fn.update_picker couldnt send "' + date + '" to any object.' );
				// @endif
			}
		},
		/**
		 * @function tribe_ev.fn.update_viewport_variables
		 * @desc tribe_ev.fn.update_viewport_variables surprisingly updates the viewport variables stored in the tribe_ev.data object.
		 */
		update_viewport_variables: function() {
			tribe_ev.data.v_height = $( window ).height();
			tribe_ev.data.v_width = $( window ).width();
		},
		/**
		 * @function tribe_ev.fn.url_path
		 * @desc tribe_ev.fn.url_path strips query vars from a url passed to it using js split on the ? character.
		 * @param {String} url The url to remove all vars from.
		 * @returns {String} Returns a url devoid of any query vars.
		 * @example <caption>Get the query var free version of an href attribute.</caption>
		 * $('#tribe-events').on('click', '.tribe-events-nav-next', function (e) {
		 *		e.preventDefault();
		 *		tribe_ev.data.cur_url = tribe_ev.fn.url_path($(this).attr('href'));
		 * });
		 */
		url_path                 : function( url ) {
			return url.split( "?" )[0];
		},
		/**
		 * @function tribe_ev.fn.equal_height
		 * @desc tribe_ev.fn.equal_height gets the tallest height of a set of elements and sets them to the same height.
		 * @param {Object} $group The group of elements to get and set tallest height from.
		 * @example <caption>Get and set the height to the tallest of a set of elements.</caption>
		 * $('#tribe-events .columns').tribe_ev.fn.equal_height();
		 */
		equal_height             : function( $group ) {
			var tallest = 0;
			$group.css( 'height', 'auto' );
			$group.each( function() {
				var this_height = $( this ).outerHeight();
				if ( this_height > tallest ) {
					tallest = this_height;
				}
			} );
			setTimeout( function() {
				$group.css( 'height', tallest );
			}, 100 );
		}

	};

	/**
	 * @namespace tribe_ev
	 * @desc tribe_ev.tests namespace stores all the custom tests used throughout the core events plugin.
	 */

	tribe_ev.tests = {
		/**
		 * @function tribe_ev.tests.live_ajax
		 * @desc tribe_ev.tests.live_ajax tests if live ajax is enabled in the events settings tab by checking the data attribute data-live_ajax on #tribe-events in the front end.
		 * @example <caption>Very easy test to use. In a doc ready:</caption>
		 * if (tribe_ev.tests.live_ajax()) {
		 *		// live ajax is on
		 * ) else {
		 *     // live ajax is off
		 * }
		 */
		live_ajax     : function() {
			var $tribe_events = $( document.getElementById( 'tribe-events' ) );
			return ($tribe_events.length && $tribe_events.tribe_has_attr( 'data-live_ajax' ) && $tribe_events.data( 'live_ajax' ) == '1') ? true : false;
		},
		/**
		 * @function tribe_ev.tests.map_view
		 * @desc tribe_ev.tests.map_view test if we are on map view.
		 * @example <caption>Test if we are on map view</caption>
		 * if (tribe_ev.tests.map_view()) {
		 *		// we are on map view
		 * )
		 */
		map_view      : function() {
			return typeof GeoLoc !== 'undefined' && GeoLoc.map_view;
		},
		/**
		 * @function tribe_ev.tests.no_bar
		 * @desc tribe_ev.tests.has_bar tests if the events bar is enabled on the front end.
		 * @example <caption>Very easy test to use. In a doc ready:</caption>
		 * if (tribe_ev.tests.no_bar()) {
		 *		// no event bar
		 * ) else {
		 *     // has event bar
		 * }
		 */
		no_bar        : function() {
			return $( 'body' ).is( '.tribe-bar-is-disabled' );
		},
		/**
		 * @type Boolean tribe_ev.tests.pushstate
		 * @desc tribe_ev.tests.pushstate checks if the history object is available safely and returns true or false.
		 * @example <caption>Execute an if else on the presence of pushstate</caption>
		 * if (tribe_ev.tests.pushstate) {
		 *		// pushstate is available
		 * ) else {
		 *     // pushstate is not available
		 * }
		 */
		pushstate     : !!(window.history && history.pushState),
		/**
		 * @function tribe_ev.tests.reset_on
		 * @desc tribe_ev.tests.reset_on tests if any other function is currently disabling a tribe ajax function.
		 * @example <caption>In another handler that will be triggering a tribe ajax function:</caption>
		 * if (!tribe_ev.tests.reset_on()) {
		 *		// reset is not occuring so lets run some other ajax
		 * )
		 */
		reset_on      : function() {
			return $( 'body' ).is( '.tribe-reset-on' );
		},
		/**
		 * @function tribe_ev.tests.starting_delim
		 * @desc tribe_ev.tests.starting_delim is used by events url forming functions to determine if "?" is already present. It then sets the delimiter for the next part of the url concatenation to "?" if not found and "&" if it is.
		 * @example <caption>Test and set delimiter during url string concatenation.</caption>
		 *        tribe_ev.state.cur_url += tribe_ev.tests.starting_delim + tribe_ev.state.url_params;
		 */
		starting_delim: function() {
			return tribe_ev.state.cur_url.indexOf( '?' ) != -1 ? '&' : '?';
		},
		/**
		 * @type Boolean tribe_ev.tests.webkit
		 * @desc tribe_ev.tests.webkit checks if webkit is the browser in use and returns true or false.
		 * @example <caption>Execute an if else on the presence of pushstate</caption>
		 * if (tribe_ev.tests.webkit) {
		 *		// is webkit
		 * ) else {
		 *     // is not webkit
		 * }
		 */
		webkit        : 'WebkitAppearance' in document.documentElement.style
	};

	/**
	 * @namespace tribe_ev
	 * @desc tribe_ev.data stores information that is sometimes used internally and also contains useful data for themers.
	 */

	tribe_ev.data = {
		ajax_response       : {},
		base_url            : '',
		cur_url             : tribe_ev.fn.url_path( document.URL ),
		cur_date            : tribe_ev.fn.current_date(),
		datepicker_formats: {
			'main': [
				'yyyy-mm-dd',
				'm/d/yyyy',
				'mm/dd/yyyy',
				'd/m/yyyy',
				'dd/mm/yyyy',
				'm-d-yyyy',
				'mm-dd-yyyy',
				'd-m-yyyy',
				'dd-mm-yyyy',
				'yyyy.mm.dd',
				'mm.dd.yyyy',
				'dd.mm.yyyy'
			],
			'month': [
				'yyyy-mm',
				'm/yyyy',
				'mm/yyyy',
				'm/yyyy',
				'mm/yyyy',
				'm-yyyy',
				'mm-yyyy',
				'm-yyyy',
				'mm-yyyy',
				'yyyy.mm',
				'mm.yyyy',
				'mm.yyyy'
			]
		},
		datepicker_opts     : {},
		default_permalinks  : (!config.permalink_settings.length),
		initial_url         : tribe_ev.fn.url_path( document.URL ),
		mobile_break        : 768,
		default_mobile_view : null,
		redirected_view     : null,
		default_view        : null,
		params              : tribe_ev.fn.get_params(),
		v_height            : 0,
		v_width             : 0
	};

	/**
	 * @namespace tribe_ev
	 * @desc tribe_ev.events is an empty object used to attach all tribe custom events to.
	 */

	tribe_ev.events = {};

	/**
	 * @namespace tribe_ev
	 * @desc tribe_ev.state is mainly used in events ajax operations, though a few variables are set on doc ready.
	 */

	tribe_ev.state = {
		ajax_running     : false,
		ajax_timer       : 0,
		ajax_trigger     : '',
		category         : '',
		date             : '',
		datepicker_format: '0',
		do_string        : false,
		featured         : false,
		filters          : false,
		filter_cats      : false,
		initial_load     : true,
		mdate            : '',
		paged            : 1,
		page_title       : '',
		params           : {},
		popping          : false,
		pushstate        : true,
		pushcount        : 0,
		recurrence       : false,
		updating_picker  : false,
		url_params       : {},
		view             : '',
		view_target      : ''
	};

})( window, document, jQuery, tribe_debug, tribe_js_config );

(function( window, document, $, td, te, tf, ts, tt, dbug ) {

	/*
	 * $    = jQuery
	 * td   = tribe_ev.data
	 * te   = tribe_ev.events
	 * tf   = tribe_ev.fn
	 * ts   = tribe_ev.state
	 * tt   = tribe_ev.tests
	 * dbug = tribe_debug
	 */


	$( document ).ready( function() {

		// @ifdef DEBUG
		dbug && tec_debug.info( 'TEC Debug: Tribe Events JS init, Init Timer started from tribe-events.js.' );
		// @endif

		tf.update_viewport_variables();

		var $body                = $( 'body' );
		var $tribe_events        = $( document.getElementById( 'tribe-events' ) );
		var $tribe_content       = $( document.getElementById( 'tribe-events-content' ) );
		var $tribe_events_header = $( document.getElementById( 'tribe-events-header' ) );
		var resize_timer;

		$tribe_events.removeClass( 'tribe-no-js' );

		ts.category   = tf.get_category();
		td.base_url   = tf.get_base_url();
		ts.page_title = document.title;

		var tribe_display = tf.get_url_param( 'tribe_event_display' );

		if ( tribe_display ) {
			ts.view = tribe_display;
		}
		else if ( $tribe_events_header.length && $tribe_events_header.tribe_has_attr( 'data-view' ) ) {
			ts.view = $tribe_events_header.data( 'view' );
		}

		if ( $tribe_events.tribe_has_attr( 'data-datepicker_format' ) && $tribe_events.attr( 'data-datepicker_format' ).length >= 1 ) {
			ts.datepicker_format = $tribe_events.attr( 'data-datepicker_format' );
		}

		// @ifdef DEBUG
		ts.view && dbug && tec_debug.time( 'Tribe JS Init Timer' );
		// @endif

		$( te ).on( 'tribe_ev_collectParams', function() {
			// maybe add a baseurl to the Ajax request if we are attempting to navigate events. This helps with
			// our shortcode pagination
			if (
				'undefined' === typeof tribe_ev.state
				|| 'undefined' === typeof tribe_ev.state.params
			) {
				return;
			}

			if (
				-1 === tribe_ev.fn.in_params( tribe_ev.state.params, 'eventdate' )
				&& -1 === tribe_ev.fn.in_params( tribe_ev.state.params, 'tribe_event_display' )
			) {
				return;
			}

			// We only want to manipulate shortcode params. Bail otherwise
			if ( ! $( document.getElementById( 'tribe-events' ) ).is( '.tribe-events-shortcode' ) ) {
				return;
			}

			var $header = $( document.getElementById( 'tribe-events-header' ) );
			var $canonical = $( 'link[rel="canonical"]' );
			var url = null;

			if ( $canonical.length ) {
				// use the canonical URL if it is available (it should be)
				url = $canonical.attr( 'href' );
			} else if ( $header.length ) {
				// failover to the baseurl of the event header
				url = $header.data( 'baseurl' );
			} else {
				// use the current URL as a last ditch effort
				url = window.location.origin + window.location.path;
			}

			tribe_ev.state.params += '&baseurl=' + url;

			if ( $header.length ) {
				var cat = /tribe_events_cat=([^&]*)/ig.exec( $header.data( 'baseurl' ) );

				if ( cat && 'undefined' !== typeof cat[1] ) {
					cat = cat[1];
				} else {
					cat = null;
				}

				if ( cat ) {
					var cat_regexp = new RegExp( 'tribe_event_category=' + cat );

					if ( ! tribe_ev.state.params.match( cat_regexp ) ) {
						tribe_ev.state.params += '&tribe_event_category=' + cat;
					}

					if ( 'string' === typeof tribe_ev.state.url_params && ! tribe_ev.state.url_params.match( cat_regexp ) ) {
						tribe_ev.state.url_params += '&tribe_event_category=' + cat;
					}
				}
			}

			if ( 'month' === ts.view && ! $( '#tribe-events-bar' ).length ) {
				if ( ! td.default_permalinks ) {
					ts.url_params = 'tribe-bar-date=' + tribeDateFormat( ts.mdate, "tribeMonthQuery" );
				} else {
					tribe_ev.state.url_params += 'tribe-bar-date=' + tribeDateFormat( ts.mdate, "tribeMonthQuery" );
				}
			}

		} );

		/**
		 *
		 * Themers can override the mobile break with an override in functions.php
		 *
		 *************************************************************************

			add_action( 'tribe_events_mobile_breakpoint', 'mobile_breakpoint' );

			function mobile_breakpoint() {
				return 500;
			}

		 *************************************************************************
		 */

		if ( $tribe_events.length ) {
			var $breakpoint_holder = $tribe_events.tribe_has_attr( 'data-mobilebreak' );

			if ( false === $breakpoint_holder ) {
				$breakpoint_holder = $tribe_events.find( '[data-mobilebreak]' ).eq( 0 );
			} else {
				$breakpoint_holder = $tribe_events;
			}

			if ( $breakpoint_holder.length ) {
				td.mobile_break = parseInt( $breakpoint_holder.data( 'mobilebreak' ), 10 );
			}

			/**
			 * Deal with the Mobile View when we have a breakpoint
			 */
			var $mobile_view_holder = $tribe_events.tribe_has_attr( 'data-default-mobile-view' );

			if ( false === $mobile_view_holder ) {
				$mobile_view_holder = $tribe_events.find( '[data-default-mobile-view]' ).eq( 0 );
			} else {
				$mobile_view_holder = $tribe_events;
			}

			if ( $mobile_view_holder.length ) {
				// Remember, when using jQuery.data and dash separated variables they become CamelCase separated
				td.default_mobile_view = $mobile_view_holder.data( 'defaultMobileView' );
				td.default_view = $mobile_view_holder.data( 'defaultView' );
				td.redirected_view = $mobile_view_holder.data( 'redirectedView' );
			}
		}

		if ( $tribe_events.length && td.mobile_break > 0 ) {
			$body.addClass( 'tribe-is-responsive' );
		}

		/* Let's hide the widget calendar if we find more than one instance */
		$( ".tribe-events-calendar-widget" ).not( ":eq(0)" ).hide();

		tf.tooltips();
		tf.mobile_class();

		//remove border on list view event before month divider
		function list_find_month_last_event() {
			if ( $( '.tribe-events-list' ).length ) {
				$( '.tribe-events-list-separator-month' ).prev( '.vevent' ).addClass( 'tribe-event-end-month' );
			}
		}

		list_find_month_last_event();
		// remove events header subnav pagination if no results
		if ( $( '.tribe-events-list .tribe-events-notices' ).length ) {
			$( '#tribe-events-header .tribe-events-sub-nav' ).empty();
		}

		//remove border on list view event before month divider
		if ( $( '.tribe-events-list' ).length ) {
			$( '.tribe-events-list-separator-month' ).prev( '.vevent' ).addClass( 'tribe-event-end-month' );
		}

		// ajax complete function to remove active spinner
		$( te ).on( 'tribe_ev_ajaxSuccess', function() {
			$( '.tribe-events-active-spinner' ).remove();
			list_find_month_last_event();
		} );

		/**
		 * @function tribe_ical_url
		 * @desc tribe_ical_url This function adds required params to the ical url. Runs on doc ready, and hooks into 'ajax-success.tribe' also.
		 */
		function tribe_ical_url() {
			var should_overwrite = true;

			// If the "force filtered iCal link" option is set, we should not overwrite.
			if ( 'undefined' !== typeof tribe_js_config.force_filtered_ical_link ) {
				should_overwrite = ! tribe_js_config.force_filtered_ical_link;
			}

			if ( should_overwrite ) {
				var url       = document.URL;
				var separator = '?';

				if ( url.indexOf( '?' ) > 0 ) {
					separator = '&';
				}

				var new_link = url + separator + 'ical=1' + '&' + 'tribe_display=' + ts.view;

				$( 'a.tribe-events-ical' ).attr( 'href', new_link );
			}
		}

		$( te ).on( 'tribe_ev_ajaxSuccess', function() {
			tribe_ical_url();
		} );

		tribe_ical_url();

		$( window )
			.resize( function() {

				clearTimeout( resize_timer );
				resize_timer = setTimeout( tf.execute_resize, 200 );

			} );

		// @ifdef DEBUG
		if ( dbug ) {
			tec_debug.groupCollapsed( 'TEC Debug: Browser and events settings information:' );
			tec_debug.log( 'User agent reported as: "' + navigator.userAgent );
			tec_debug.log( 'Live ajax returned its state as: "' + tt.live_ajax() );
			ts.view && tec_debug.log( 'Tribe js detected the view to be: "' + ts.view );
			tec_debug.log( 'Supports pushstate: "' + tt.pushstate );
			tec_debug.groupEnd();
			tec_debug.info( 'TEC Debug: tribe-events.js successfully loaded' );
		}
		// @endif
	} );
})( window, document, jQuery, tribe_ev.data, tribe_ev.events, tribe_ev.fn, tribe_ev.state, tribe_ev.tests, tribe_debug );
