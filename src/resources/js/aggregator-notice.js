/* eslint-disable */
window.tribe_aggregator = window.tribe_aggregator || {};
window.tribe_aggregator.notice = window.tribe_aggregator.notice || {};


( function( $, ea ) {

	ea.localized = window.tribe_aggregator_data || {};

	// Setup the global Variable
	ea.notice = {

		selector: {
			notice: '.tribe-notice-aggregator-update-msg',
			progress: '.progress',
			tracker: '.tracker',
			created: '.track-created .value',
			updated: '.track-updated .value',
			skipped: '.track-skipped .value',
			remaining: '.track-remaining .value',
			bar: '.bar',
		},

		progress: {},
	};

	ea.notice.progress.init = function() {
		ea.notice.progress.data = {};
		ea.notice.progress.$ = {};
		ea.notice.progress.$.notice    = $( '.tribe-notice-aggregator-update-msg' );
		ea.notice.progress.$.spinner   = ea.notice.progress.$.notice.find( 'img' );
		ea.notice.progress.$.progress  = ea.notice.progress.$.notice.find( ea.notice.selector.progress );
		ea.notice.progress.$.tracker   = ea.notice.progress.$.notice.find( ea.notice.selector.tracker );
		ea.notice.progress.$.created   = ea.notice.progress.$.tracker.find( ea.notice.selector.created );
		ea.notice.progress.$.updated   = ea.notice.progress.$.tracker.find( ea.notice.selector.updated );
		ea.notice.progress.$.skipped   = ea.notice.progress.$.tracker.find( ea.notice.selector.skipped );
		ea.notice.progress.$.remaining = ea.notice.progress.$.tracker.find( ea.notice.selector.remaining );
		ea.notice.progress.$.bar       = ea.notice.progress.$.notice.find( ea.notice.selector.bar );
		ea.notice.progress.data.time   = Date.now();

		ea.notice.progress.hasHeartBeat = 'undefined' !== typeof wp && wp.heartbeat;

		if ( ea.notice.progress.hasHeartBeat ) {
			wp.heartbeat.interval( 15 );
		}

		setTimeout( ea.notice.progress.start );
	};

	ea.notice.progress.start = function () {
		if ( 'object' !== typeof window.tribe_aggregator_save ) {
			return;
		}

		ea.notice.progress.update(window.tribe_aggregator_save.progress, window.tribe_aggregator_save.progressText);
		if ( ! ea.notice.progress.hasHeartBeat ) {
			ea.notice.progress.send_request();
		}
	};

	ea.notice.progress.continue = true;
	$(document).on('heartbeat-send', function (event, data) {
		if ( 'object' !== typeof window.tribe_aggregator_save ) {
			return;
		}

		if ( ea.notice.progress.continue ) {
			data.ea_record = window.tribe_aggregator_save.record_id;
		}
	});

	$(document).on('heartbeat-tick', function (event, data) {
		// Check for our data, and use it.
		if (!data.ea_progress) {
			return;
		}

		ea.notice.progress.handle_response(data.ea_progress);
	});

	ea.notice.progress.handle_response = function( data ) {

		if ( data.html ) {
			ea.notice.progress.data.notice.html( data.html );
		}

		if ( ! isNaN( parseInt( data.progress, 10 ) ) ) {
			ea.notice.progress.update( data );
		}

		ea.notice.progress.continue = data.continue;
		if (data.continue && !ea.notice.progress.hasHeartBeat) {
			setTimeout(ea.notice.progress.send_request, 15000);
		}

		if ( data.error ) {
			ea.notice.progress.$.notice.find( '.tribe-message' ).html( data.error_text );
			ea.notice.progress.$.tracker.remove();
			ea.notice.progress.$.notice.find( '.progress-container' ).remove();
			ea.notice.progress.$.notice.removeClass( 'notice-warning' ).addClass( 'notice-error' );
		} else if ( data.complete ) {
			ea.notice.progress.$.notice.find( '.tribe-message' ).html( data.complete_text );
			ea.notice.progress.$.tracker.remove();
			ea.notice.progress.$.notice.find( '.progress-container' ).remove();
			ea.notice.progress.$.notice.removeClass( 'notice-warning' ).addClass( 'notice-success' );
			ea.notice.progress.$.notice.show();
		}
	};

	ea.notice.progress.send_request = function() {
		var payload = {
			record:  window.tribe_aggregator_save.record_id,
			check:  window.tribe_aggregator_save.check,
			action: 'tribe_aggregator_realtime_update'
		};
		$.post( ajaxurl, payload, ea.notice.progress.handle_response, 'json' );
	};

	ea.notice.progress.update = function( data ) {
		var percentage = parseInt( data.progress, 10 );

		// The percentage should never be out of bounds, but let's handle such a thing gracefully if it arises.
		if ( percentage < 0 || percentage > 100 ) {
			return;
		}

		if ( 'undefined' === typeof data.counts ) {
			return;
		}

		var types = [ 'created', 'updated', 'skipped' ];
		for ( var i in types ) {
			if ( ! data.counts[ types[ i ] ] ) {
				continue;
			}

			var count = data.counts[ types[ i ] ];
			var $target = ea.notice.progress.$[ types[ i ] ];

			// update updated and skipped count only if higher
			if ( 'updated' === types[ i ] || 'skipped' === types[ i ] ) {
				var current = $target ? $target.html() : 0;

				if ( count > current ) {
					$target.html( count );
				}
			} else {
				$target.html( count );
			}

			if ( ! ea.notice.progress.$.tracker.hasClass( 'has-' + types[ i ] ) ) {
				ea.notice.progress.$.tracker.addClass( 'has-' + types[ i ] );
			}
		}

		ea.notice.progress.$.bar.css( 'width', percentage + '%' );
		ea.notice.progress.$.progress.attr( 'title', data.progress_text );
	};

	ea.notice.progress.remove_notice = function() {
		var effect = {
			opacity: 0,
			height:  'toggle'
		};

		ea.notice.progress.$.notice.animate( effect, 1000, function() {
			ea.notice.progress.$.notice.remove();
		} );
	};

	$(document).on(
		'tribe_aggregator_init_notice',
		function() {
			ea.notice.progress.init();
		}
	);

	ea.notice.progress.init();
} )( jQuery, window.tribe_aggregator );
