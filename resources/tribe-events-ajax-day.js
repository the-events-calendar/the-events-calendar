(function (window, document, $, td, te, tf, ts, tt, dbug) {

	/*
	 * $    = jQuery
	 * td   = tribe_ev.data
	 * te   = tribe_ev.events
	 * tf   = tribe_ev.fn
	 * ts   = tribe_ev.state
	 * tt   = tribe_ev.tests
	 * dbug = tribe_debug
	 */

	$(document).ready(function () {

		if (ts.filter_cats)
			var base_url = $('#tribe-events-header').attr('data-baseurl').slice(0, -11);
		else
			var base_url = $('#tribe-events-footer .tribe-events-nav-next a').attr('href').slice(0, -11);

		ts.date = $('#tribe-events-header').attr('data-date');

		function tribe_day_add_classes() {
			if ($('.tribe-events-day-time-slot').length) {
				$('.tribe-events-day-time-slot').find('.vevent:last').addClass('tribe-events-last');
				$('.tribe-events-day-time-slot:first').find('.vevent:first').removeClass('tribe-events-first');
			}
		}

		tribe_day_add_classes();

		if (tt.pushstate && !tt.map_view()) {

			var params = 'action=tribe_event_day&eventDate=' + ts.date;

			if (td.params.length)
				params = params + '&' + td.params;

			history.replaceState({
				"tribe_params": params,
				"tribe_url_params": td.params
			}, '', location.href);

			$(window).on('popstate', function (event) {

				var state = event.originalEvent.state;

				if (state) {
					ts.do_string = false;
					ts.pushstate = false;
					ts.popping = true;
					ts.params = state.tribe_params;
					tf.pre_ajax(function () {
						tribe_events_calendar_ajax_post();
					});

					tf.set_form(ts.params);
				}
			});
		}

		$('#tribe-events').on('click', '.tribe-events-nav-previous a, .tribe-events-nav-next a', function (e) {
			e.preventDefault();
			if (ts.ajax_running)
				return;
			var $this = $(this);
			ts.popping = false;
			ts.date = $this.attr("data-day");
			if (ts.filter_cats)
				td.cur_url = base_url + ts.date + '/';
			else
				td.cur_url = $this.attr("href");
			tf.update_picker(ts.date);
			tf.pre_ajax(function () {
				tribe_events_calendar_ajax_post();
			});
		});

		tf.snap('#tribe-events-bar', '#tribe-events', '#tribe-events-footer .tribe-events-nav-previous a, #tribe-events-footer .tribe-events-nav-next a');

		function tribe_events_bar_dayajax_actions(e) {
			if (tribe_events_bar_action != 'change_view') {
				e.preventDefault();
				if (ts.ajax_running)
					return;
				var picker = $('#tribe-bar-date').val();
				ts.popping = false;
				if (picker.length) {
					ts.date = $('#tribe-bar-date').val();
					td.cur_url = base_url + ts.date + '/';
				} else {
					ts.date = td.cur_date;
					td.cur_url = base_url + td.cur_date + '/';
				}
				tf.pre_ajax(function () {
					tribe_events_calendar_ajax_post();
				});

			}
		}

		$('form#tribe-bar-form').on('submit', function (e) {
			tribe_events_bar_dayajax_actions(e);
		});

		if (tt.live_ajax() && tt.pushstate) {

			$('#tribe-bar-date').on('changeDate', function (e) {
				if (!tt.reset_on()) {
					ts.popping = false;
					ts.date = $(this).val();
					td.cur_url = base_url + ts.date + '/';
					tf.pre_ajax(function () {
						tribe_events_calendar_ajax_post();
					});
				}
			});

		}

		$(te).on("tribe_ev_runAjax", function () {
			tribe_events_calendar_ajax_post();
		});

		$(te).on("tribe_ev_updatingRecurrence", function () {
			if (ts.filter_cats)
				td.cur_url = base_url + ts.date + '/';
			else
				td.cur_url = $('#tribe-events-header').attr("data-baseurl");
			ts.popping = false;
		});

		function tribe_events_calendar_ajax_post() {

			ts.pushcount = 0;
			ts.ajax_running = true;

			if (!ts.popping) {

				ts.url_params = '';

				ts.params = {
					action: 'tribe_event_day',
					eventDate: ts.date
				};

				ts.url_params = {
					action: 'tribe_event_day'
				};

				if (ts.category) {
					ts.params['tribe_event_category'] = ts.category;
				}

				$(te).trigger('tribe_ev_serializeBar');

				ts.params = $.param(ts.params);
				ts.url_params = $.param(ts.url_params);

				$(te).trigger('tribe_ev_collectParams');

				ts.pushstate = true;
				ts.do_string = false;

				if (ts.pushcount > 0 || ts.filters) {
					ts.pushstate = false;
					ts.do_string = true;
				}
			}

			if (tt.pushstate && !ts.filter_cats) {

				dbug && debug.time('Day View Ajax Timer');
				$(te).trigger('tribe_ev_ajaxStart').trigger('tribe_ev_dayView_AjaxStart');
				$('#tribe-events-header').tribe_spin();

				$.post(
					TribeCalendar.ajaxurl,
					ts.params,
					function (response) {

						ts.initial_load = false;
						tf.enable_inputs('#tribe_events_filters_form', 'input, select');

						if (response.success) {

							ts.ajax_running = false;

							td.ajax_response = {
								'total_count': parseInt(response.total_count),
								'view': response.view,
								'max_pages': '',
								'tribe_paged': '',
								'timestamp': new Date().getTime()
							};

							$('#tribe-events-content').replaceWith(response.html);

							if (response.total_count === 0) {
								$('#tribe-events-header .tribe-events-sub-nav').empty();
							}
							$('.tribe-events-promo').next('.tribe-events-promo').remove();

							ts.page_title = $('#tribe-events-header').data('title');
							document.title = ts.page_title;

							if (ts.do_string) {
								td.cur_url = td.cur_url + '?' + ts.url_params;
								history.pushState({
									"tribe_date": ts.date,
									"tribe_params": ts.params
								}, ts.page_title, td.cur_url);
							}

							if (ts.pushstate) {
								history.pushState({
									"tribe_date": ts.date,
									"tribe_params": ts.params
								}, ts.page_title, td.cur_url);
							}

							tribe_day_add_classes();

							$(te).trigger('tribe_ev_ajaxSuccess').trigger('tribe_ev_dayView_AjaxSuccess');

							dbug && debug.timeEnd('Day View Ajax Timer');

						}
					}
				);
			} else {

				if (ts.do_string)
					window.location = td.cur_url + '?' + ts.url_params;
				else
					window.location = td.cur_url;
			}
		}
		dbug && debug.info('TEC Debug: tribe-events-ajax-day.js successfully loaded');
		ts.view && dbug && debug.timeEnd('Tribe JS Init Timer');
	});

})(window, document, jQuery, tribe_ev.data, tribe_ev.events, tribe_ev.fn, tribe_ev.state, tribe_ev.tests, tribe_debug);