/**
 * @file This file contains all day view specific javascript.
 * This file should load after all vendors and core events javascript.
 * @version 3.0
 */

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
			var base_url = $('#tribe-events-header').data('baseurl').slice(0, -11);
		else
			var base_url = $('#tribe-events-footer .tribe-events-nav-next a').attr('href').slice(0, -11);

		ts.date = $('#tribe-events-header').data('date');

		/**
		 * @function tribe_day_add_classes
		 * @since 3.0
		 * @desc Add css classes needed for correct styling of the day list.
		 */

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

			if (ts.category)
				params = params + '&tribe_event_category=' + ts.category;

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
						tribe_events_day_ajax_post();
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
				tribe_events_day_ajax_post();
			});
		});

		tf.snap('#tribe-events-bar', '#tribe-events', '#tribe-events-footer .tribe-events-nav-previous a, #tribe-events-footer .tribe-events-nav-next a');

		/**
		 * @function tribe_events_bar_dayajax_actions
		 * @since 3.0
		 * @desc On events bar submit, this function collects the current state of the bar and sends it to the day view ajax handler.
		 * @param {event} e The event object.
		 */

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
					tribe_events_day_ajax_post();
				});

			}
		}

		$('form#tribe-bar-form').on('submit', function (e) {
			tribe_events_bar_dayajax_actions(e);
		});

		if (tt.no_bar() || tt.live_ajax() && tt.pushstate) {

			$('#tribe-bar-date').on('changeDate', function (e) {
				if (!tt.reset_on()) {
					ts.popping = false;
					ts.date = $(this).val();
					td.cur_url = base_url + ts.date + '/';
					tf.pre_ajax(function () {
						tribe_events_day_ajax_post();
					});
				}
			});

		}

		$(te).on("tribe_ev_runAjax", function () {
			tribe_events_day_ajax_post();
		});

		$(te).on("tribe_ev_updatingRecurrence", function () {
			if (ts.filter_cats)
				td.cur_url = base_url + ts.date + '/';
			else
				td.cur_url = $('#tribe-events-header').attr("data-baseurl");
			ts.popping = false;
		});

		/**
		 * @function tribe_events_day_ajax_post
		 * @since 3.0
		 * @desc The ajax handler for day view.
		 * Fires the custom event 'tribe_ev_serializeBar' at start, then 'tribe_ev_collectParams' to gather any additional parameters before actually launching the ajax post request.
		 * As post begins 'tribe_ev_ajaxStart' and 'tribe_ev_dayView_AjaxStart' are fired, and then 'tribe_ev_ajaxSuccess' and 'tribe_ev_dayView_ajaxSuccess' are fired on success.
		 * Various functions in the events plugins hook into these events. They are triggered on the tribe_ev.events object.
		 */

		function tribe_events_day_ajax_post() {

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
				$('#tribe-events-content .tribe-events-loop').tribe_spin();

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

							var $the_content = '';
							if($.isFunction($.fn.parseHTML))
								$the_content = $.parseHTML(response.html);
							else
								$the_content = response.html;

							$('#tribe-events-content').replaceWith($the_content);

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
