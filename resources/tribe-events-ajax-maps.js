/**
 * @file This file contains all map view specific javascript.
 * This file should load after all vendors and core events javascript.
 * @version 3.0
 */

(function (window, document, $, td, te, tf, tg, ts, tt, dbug) {

	/*
	 * $    = jQuery
	 * td   = tribe_ev.data
	 * te   = tribe_ev.events
	 * tf   = tribe_ev.fn
	 * tg   = tribe_ev.geoloc
	 * ts   = tribe_ev.state
	 * tt   = tribe_ev.tests
	 * dbug = tribe_debug
	 */

	$.extend(tribe_ev.fn, {

		/**
		 * @function tribe_ev.fn.map_add_marker
		 * @since 3.0
		 * @desc tribe_ev.fn.map_add_marker adds event markers to the map on geoloc view.
		 * @param {String} lat Marker latitude.
		 * @param {String} lng Marker longitude.
		 * @param {String} title Marker event title.
		 * @param {String} address Marker event address.
		 * @param {String} link Marker event permalink.
		 */

		map_add_marker: function (lat, lng, title, address, link) {
			var myLatlng = new google.maps.LatLng(lat, lng);

			var marker = new google.maps.Marker({
				position: myLatlng,
				map: tg.map,
				title: title
			});

			var infoWindow = new google.maps.InfoWindow();

			var content_title = title;
			if (link) {
				content_title = $('<div/>').append($("<a/>").attr('href', link).text(title)).html();
			}

			var content = "Event: " + content_title;

			if (address) {
				content = content + "<br/>" + "Address: " + address;
			}

			infoWindow.setContent(content);

			google.maps.event.addListener(marker, 'click', function (event) {
				infoWindow.open(tg.map, marker);
			});

			tg.markers.push(marker);

			if(tg.refine){
				marker.setVisible(false);
			}
			tg.bounds.extend(myLatlng);
		}
	});

	tg.geocoder = new google.maps.Geocoder();
	tg.bounds = new google.maps.LatLngBounds();

	$(document).ready(function () {

		/**
		 * @function tribe_test_location
		 * @since 3.0
		 * @desc tribe_test_location clears the lat and lng values in event bar if needed. Also hides or shows the geofence filter if present.
		 */

		function tribe_test_location() {

			if ($('#tribe-bar-geoloc').length) {
				var val = $('#tribe-bar-geoloc').val(),
					$fence = $("#tribe_events_filter_item_geofence"),
					$latlng = $('#tribe-bar-geoloc-lat, #tribe-bar-geoloc-lng');
				if (val.length) {
					$fence.show();
				} else {
					$fence.hide();
					if ($latlng.length)
						$latlng.val('');
				}
			}
		}

		tribe_test_location();

		var $tribe_container = $('#tribe-events'),
			$geo_bar_input = $('#tribe-bar-geoloc'),
			$geo_options = $("#tribe-geo-options");

		var options = {
			zoom: 5,
			center: new google.maps.LatLng(TribeEventsPro.geocenter.max_lat, TribeEventsPro.geocenter.max_lng),
			mapTypeId: google.maps.MapTypeId.ROADMAP
		};

		if (document.getElementById('tribe-geo-map')) {
			tg.map = new google.maps.Map(document.getElementById('tribe-geo-map'), options);
			tg.bounds = new google.maps.LatLngBounds();

			var minLatlng = new google.maps.LatLng(TribeEventsPro.geocenter.min_lat, TribeEventsPro.geocenter.min_lng);
			tg.bounds.extend(minLatlng);

			var maxLatlng = new google.maps.LatLng(TribeEventsPro.geocenter.max_lat, TribeEventsPro.geocenter.max_lng);
			tg.bounds.extend(maxLatlng);
		}
		if($().placeholder)
			$('#tribe-geo-location').placeholder();

		if (tt.map_view()) {

			var tribe_is_paged = tf.get_url_param('tribe_paged'),
				tribe_display = tf.get_url_param('tribe_event_display');

			if (tribe_is_paged)
				ts.paged = tribe_is_paged;

			ts.view = 'map';

			if(tribe_display == 'past')
				ts.view = 'past';

			tf.tooltips();
		}

		if (tt.map_view() && td.params) {

			var tp = td.params;
			if (tf.in_params(tp, "tribe_geosearch") >= 0) {
			} else
				tp += '&action=tribe_geosearch';
			if (tf.in_params(tp, "tribe_paged") >= 0) {
			} else
				tp += '&tribe_paged=1';

			ts.params = tp;

			ts.do_string = false;
			ts.pushstate = false;
			ts.popping = true;
			tf.pre_ajax(function () {
				tribe_map_processOption();
			});
		} else if (tt.map_view()) {

			ts.do_string = false;
			ts.pushstate = false;
			ts.popping = false;
			ts.initial_load = true;
			tf.pre_ajax(function () {
				tribe_map_processOption();
			});
		}

		if (tt.pushstate && tt.map_view()) {

			history.replaceState({
				"tribe_paged": ts.paged,
				"tribe_params": ts.params
			}, '', location.href);

			$(window).on('popstate', function (event) {

				var state = event.originalEvent.state;

				if (state) {
					ts.do_string = false;
					ts.pushstate = false;
					ts.popping = true;
					ts.params = state.tribe_params;
					ts.paged = state.tribe_paged;
					tf.pre_ajax(function () {
						tribe_map_processOption();
					});

					tf.set_form(ts.params);
				}
			});
		}

		if (tt.map_view()) {

			$tribe_container.on('click', '.tribe-geo-option-link',function (e) {
				e.preventDefault();
				e.stopPropagation();
				var $this = $(this);

				$('.tribe-geo-option-link').removeClass('tribe-option-loaded');
				$this.addClass('tribe-option-loaded');

				$geo_bar_input.val($this.text());

				$('#tribe-bar-geoloc-lat').val(tg.geocodes[$this.data('index')].geometry.location.lat());
				$('#tribe-bar-geoloc-lng').val(tg.geocodes[$this.data('index')].geometry.location.lng());

				ts.do_string = true;
				ts.pushstate = false;
				ts.popping = false;

				if (tt.pushstate) {
					tf.pre_ajax(function () {
						tribe_map_processOption();
						$geo_options.hide();
					});
				} else {
					tf.pre_ajax(function () {
						$(te).trigger('tribe_ev_reloadOldBrowser');
					});
				}

			});

			$(document).on('click', function () {
				$geo_options.hide();
			});

			tf.snap('#tribe-events-content-wrapper', '#tribe-events-content-wrapper', '#tribe-events-footer .tribe-events-nav-previous a, #tribe-events-footer .tribe-events-nav-next a');

		}

		/**
		 * @function tribe_generate_map_params
		 * @since 3.0
		 * @desc tribe_generate_map_params generates query parameters for the map view ajax call.
		 */

		function tribe_generate_map_params() {
			ts.ajax_running = true;
			ts.params = {
				action: 'tribe_geosearch',
				tribe_paged: ts.paged,
				tribe_event_display: ts.view
			};
			
			if (ts.category)
				ts.params.tribe_event_category = ts.category;

			$(te).trigger('tribe_ev_serializeBar');

			ts.params = $.param(ts.params);

			$(te).trigger('tribe_ev_collectParams');

		}

		$(te).on("tribe_ev_reloadOldBrowser", function () {
			tribe_generate_map_params();
			window.location = td.cur_url + '?' + ts.params;
		});

		/**
		 * @function tribe_map_processOption
		 * @since 3.0
		 * @desc tribe_map_processOption is the main ajax event query for map view.
		 */

		function tribe_map_processOption() {
			$('#tribe-events-content .tribe-events-loop').tribe_spin();
			deleteMarkers();

			if (!ts.popping) {
				tribe_generate_map_params();
				ts.pushstate = false;
				if (!ts.initial_load) {
					ts.do_string = true;
				}
			}

			$.post(GeoLoc.ajaxurl, ts.params, function (response) {

				$(te).trigger('tribe_ev_ajaxStart').trigger('tribe_ev_mapView_AjaxStart');

				tf.enable_inputs('#tribe_events_filters_form', 'input, select');

				if (response.success) {

					ts.ajax_running = false;

					td.ajax_response = {
						'total_count': parseInt(response.total_count),
						'view': response.view,
						'max_pages': response.max_pages,
						'tribe_paged': response.tribe_paged,
						'timestamp': new Date().getTime()
					};

					ts.initial_load = false;

					var $the_content = '';
					if($.isFunction($.fn.parseHTML))
						$the_content = $.parseHTML(response.html);
					else
						$the_content = response.html;

					$('#tribe-events-content').replaceWith($the_content);

					if (response.view === 'map') {
						if (response.max_pages == response.tribe_paged || 0 == response.max_pages) {
							$('.tribe-events-nav-next').hide();
						} else {

							$('.tribe-events-nav-next').show();
						}
					}

					$.each(response.markers, function (i, e) {
						tf.map_add_marker(e.lat, e.lng, e.title, e.address, e.link);
					});

					if (tt.pushstate) {

						ts.page_title = $('#tribe-events-header').data('title');
						document.title = ts.page_title;

						if (ts.do_string) {
							history.pushState({
								"tribe_paged": ts.paged,
								"tribe_params": ts.params
							}, ts.page_title, td.cur_url + '?' + ts.params);
						}

						if (ts.pushstate) {
							history.pushState({
								"tribe_paged": ts.paged,
								"tribe_params": ts.params
							}, ts.page_title, td.cur_url);
						}

					}

					$(te).trigger('tribe_ev_ajaxSuccess').trigger('tribe_ev_mapView_AjaxSuccess');

					if (response.markers.length > 0) {
						centerMap();
					}
				}
			});

		}

		if (tt.map_view()) {

			var center;

			$("#tribe-geo-map-wrapper").resize(function () {
				center = tg.map.getCenter();
				google.maps.event.trigger(tg.map, "resize");
				tg.map.setCenter(center);
			});

			$('#tribe-events').on('click', 'li.tribe-events-nav-next a',function (e) {
				e.preventDefault();
				if (ts.ajax_running)
					return;
				if (ts.view === 'past') {
					if (ts.paged == '1') {
						ts.view = 'map';
					} else {
						ts.paged--;
					}
				} else {
					ts.paged++;
				}
				ts.popping = false;
				if (tt.pushstate) {
					tf.pre_ajax(function () {
						tribe_map_processOption();
					});
				} else {
					tf.pre_ajax(function () {
						$(te).trigger('tribe_ev_reloadOldBrowser');
					});
				}
			}).on('click', 'li.tribe-events-nav-previous a', function (e) {
					e.preventDefault();
					if (ts.ajax_running)
						return;
					if (ts.view === 'map') {
						if (ts.paged == '1') {
							ts.view = 'past';
						} else {
							ts.paged--;
						}
					} else {
						ts.paged++;
					}
					ts.popping = false;
					if (tt.pushstate) {
						tf.pre_ajax(function () {
							tribe_map_processOption(null);
						});
					} else {
						tf.pre_ajax(function () {
							$(te).trigger('tribe_ev_reloadOldBrowser');
						});
					}
				});

		}

		/**
		 * @function tribe_events_bar_mapajax_actions
		 * @since 3.0
		 * @desc On events bar submit, this function collects the current state of the bar and sends it to the map view ajax handler.
		 * @param {event} e The event object.
		 */

		function tribe_events_bar_mapajax_actions(e) {
			if (tribe_events_bar_action != 'change_view') {
				e.preventDefault();
				if (ts.ajax_running)
					return;
				ts.paged = 1;
				ts.view = 'map';
				ts.popping = false;
				if (tt.pushstate) {
					tf.pre_ajax(function () {
						tribe_map_processOption(null);
					});
				} else {
					tf.pre_ajax(function () {
						$(te).trigger('tribe_ev_reloadOldBrowser');
					});
				}

			}
		}

		if ((GeoLoc.map_view && $('form#tribe-bar-form').length && tt.live_ajax() && tt.pushstate) || (GeoLoc.map_view && tt.no_bar())) {
			$('#tribe-events-bar').on('changeDate', '#tribe-bar-date', function (e) {
				tribe_events_bar_mapajax_actions(e);
			});
		}

		if (GeoLoc.map_view) {
			$(te).on("tribe_ev_runAjax", function () {
				tribe_map_processOption();
			});
		}

		/**
		 * @function deleteMarkers
		 * @since 3.0
		 * @desc Clears markers from the active map.
		 */

		function deleteMarkers() {
			if (tg.markers) {
				for (i in tg.markers) {
					tg.markers[i].setMap(null);
				}
				tg.markers.length = 0;
				tg.bounds = new google.maps.LatLngBounds();
			}
		}

		/**
		 * @function centerMap
		 * @since 3.0
		 * @desc Centers the active map.
		 */

		function centerMap() {
			tg.map.fitBounds(tg.bounds);
			if (tg.map.getZoom() > 13) {
				tg.map.setZoom(13);
			}
		}

		function spin_start() {
			$('#tribe-events-footer, #tribe-events-header').find('.tribe-events-ajax-loading').show();
		}

		function spin_end() {
			$('#tribe-events-footer, #tribe-events-header').find('.tribe-events-ajax-loading').hide();
		}

		if (tt.map_view()) {

			$('form#tribe-bar-form').on('submit', function () {
				if (tribe_events_bar_action != 'change_view') {
					ts.paged = 1;
					spin_start();

					// hide pagination on submit
					$('.tribe-events-sub-nav').remove();

					var val = $('#tribe-bar-geoloc').val();

					if (val !== '') {

						ts.do_string = true;
						ts.pushstate = false;
						ts.popping = false;

						deleteMarkers();
						$("#tribe-geo-results").empty();
						$("#tribe-geo-options").hide();
						$("#tribe-geo-options #tribe-geo-links").empty();

						tf.process_geocoding(val, function (results) {
							tg.geocodes = results;

							spin_end();

							var lat = results[0].geometry.location.lat();
							var lng = results[0].geometry.location.lng();

							if (lat)
								$('#tribe-bar-geoloc-lat').val(lat);

							if (lng)
								$('#tribe-bar-geoloc-lng').val(lng);

							if (tg.geocodes.length > 1) {
								tf.print_geo_options();
								tribe_test_location();
								centerMap();


							} else {
								if (tt.pushstate) {
									tribe_test_location();
									tribe_map_processOption(tg.geocodes[0]);
								} else {
									$(te).trigger('tribe_ev_reloadOldBrowser');
								}
							}

						});

						return false;
					}

					if (val === '') {
						$('#tribe-bar-geoloc-lat').val('');
						$('#tribe-bar-geoloc-lng').val('');
						$("#tribe-geo-options").hide();
						//We can show the map even if we don't get a geo query
						if (tt.pushstate) {
							ts.do_string = true;
							ts.pushstate = false;
							ts.popping = false;
							tribe_test_location();
							tribe_map_processOption();
						} else {
							$(te).trigger('tribe_ev_reloadOldBrowser');
						}
						spin_end();
						return false;

					}
					return true;
				}
			});
			ts.view && dbug && debug.timeEnd('Tribe JS Init Timer');
		}

		dbug && debug.info('TEC Debug: tribe-events-ajax-maps.js successfully loaded');

	});

})(window, document, jQuery, tribe_ev.data, tribe_ev.events, tribe_ev.fn, tribe_ev.geoloc, tribe_ev.state, tribe_ev.tests, tribe_debug);
