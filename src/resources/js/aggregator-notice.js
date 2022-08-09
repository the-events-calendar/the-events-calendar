/* eslint-disable */
var tribe_aggregator = tribe_aggregator || {};

// Setup the global Variable
tribe_aggregator.notice = {

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

( function( $, obj, ea ) {
	obj.progress.init = function() {
		obj.progress.data = {};
		obj.progress.$ = {};
		obj.progress.$.notice    = $( '.tribe-notice-aggregator-update-msg' );
		obj.progress.$.spinner   = obj.progress.$.notice.find( 'img' );
		obj.progress.$.progress  = obj.progress.$.notice.find( obj.selector.progress );
		obj.progress.$.tracker   = obj.progress.$.notice.find( obj.selector.tracker );
		obj.progress.$.created   = obj.progress.$.tracker.find( obj.selector.created );
		obj.progress.$.updated   = obj.progress.$.tracker.find( obj.selector.updated );
		obj.progress.$.skipped   = obj.progress.$.tracker.find( obj.selector.skipped );
		obj.progress.$.remaining = obj.progress.$.tracker.find( obj.selector.remaining );
		obj.progress.$.bar       = obj.progress.$.notice.find( obj.selector.bar );
		obj.progress.data.time   = Date.now();

		obj.progress.hasHeartBeat = 'undefined' !== typeof wp && wp.heartbeat;

		if ( obj.progress.hasHeartBeat ) {
			wp.heartbeat.interval( 15 );
		}

		setTimeout( obj.progress.start );
	};

	obj.progress.start = function () {
		if ( 'object' !== typeof tribe_aggregator_save ) {
			return;
		}

		obj.progress.update(tribe_aggregator_save.progress, tribe_aggregator_save.progressText);
		if ( ! obj.progress.hasHeartBeat ) {
			obj.progress.send_request();
		}
	};

	obj.progress.continue = true;
	$(document).on('heartbeat-send', function (event, data) {
		if ( 'object' !== typeof tribe_aggregator_save ) {
			return;
		}

		if ( obj.progress.continue ) {
			data.ea_record = tribe_aggregator_save.record_id;
		}
	});

	$(document).on('heartbeat-tick', function (event, data) {
		// Check for our data, and use it.
		if (!data.ea_progress) {
			return;
		}

		obj.progress.handle_response(data.ea_progress);
	});

	obj.progress.handle_response = function( data ) {

		if ( data.html ) {
			obj.progress.data.notice.html( data.html );
		}

		if ( ! isNaN( parseInt( data.progress, 10 ) ) ) {
			obj.progress.update( data );
		}

		obj.progress.continue = data.continue;
		if (data.continue && !obj.progress.hasHeartBeat) {
			setTimeout(obj.progress.send_request, 15000);
		}

		if ( data.error ) {
			obj.progress.$.notice.find( '.tribe-message' ).html( data.error_text );
			obj.progress.$.tracker.remove();
			obj.progress.$.notice.find( '.progress-container' ).remove();
			obj.progress.$.notice.removeClass( 'notice-warning' ).addClass( 'notice-error' );
		} else if ( data.complete ) {
			obj.progress.$.notice.find( '.tribe-message' ).html( data.complete_text );
			obj.progress.$.tracker.remove();
			obj.progress.$.notice.find( '.progress-container' ).remove();
			obj.progress.$.notice.removeClass( 'notice-warning' ).addClass( 'notice-success' );
			obj.progress.$.notice.show();
		}
	};

	obj.progress.send_request = function() {
		var payload = {
			record:  tribe_aggregator_save.record_id,
			check:  tribe_aggregator_save.check,
			action: 'tribe_aggregator_realtime_update'
		};
		$.post( ajaxurl, payload, obj.progress.handle_response, 'json' );
	};

	obj.progress.update = function( data ) {
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
			var $target = obj.progress.$[ types[ i ] ];

			// update updated and skipped count only if higher
			if ( 'updated' === types[ i ] || 'skipped' === types[ i ] ) {
				var current = $target ? $target.html() : 0;

				if ( count > current ) {
					$target.html( count );
				}
			} else {
				$target.html( count );
			}

			if ( ! obj.progress.$.tracker.hasClass( 'has-' + types[ i ] ) ) {
				obj.progress.$.tracker.addClass( 'has-' + types[ i ] );
			}
		}

		obj.progress.$.bar.css( 'width', percentage + '%' );
		obj.progress.$.progress.attr( 'title', data.progress_text );
	};

	obj.progress.remove_notice = function() {
		var effect = {
			opacity: 0,
			height:  'toggle'
		};

		obj.progress.$.notice.animate( effect, 1000, function() {
			obj.progress.$.notice.remove();
		} );
	};

	$(document).on(
		'tribe_aggregator_init_notice',
		function() {
			obj.progress.init();
		}
	);

	obj.progress.init();
} )( jQuery, tribe_aggregator.notice, tribe_aggregator );
