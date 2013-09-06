/**
 * @file This file contains all month view specific javascript.
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

		var base_url = $('#tribe-events-header .tribe-events-nav-next a').attr('href').slice(0, -8),
			initial_date = tf.get_url_param('tribe-bar-date'),
			$tribedate = $('#tribe-bar-date'),
			date_mod = false;

		if ($('.tribe-events-calendar').length && $('#tribe-events-bar').length) {
			if (initial_date) {
				if (initial_date.length > 7) {
					$('#tribe-bar-date-day').val(initial_date.slice(-3));
					$tribedate.val(initial_date.substring(0, 7));
				}
			}
		}

		var tribe_var_datepickerOpts = {
			format: 'yyyy-mm',
			showAnim: 'fadeIn',
			viewMode: 'months'
		};

		var tribeBarDate = $tribedate.bootstrapDatepicker(tribe_var_datepickerOpts).on('changeDate', function (e) {

			var year = e.date.getFullYear(),
				month = ('0' + (e.date.getMonth() + 1)).slice(-2);

			tribeBarDate.hide();

			date_mod = true;

			tf.update_picker(e.date);

			ts.date = year + '-' + month;

			if (tt.no_bar() || tt.live_ajax() && tt.pushstate) {
				if (ts.ajax_running)
					return;
				if (ts.filter_cats)
					td.cur_url = $('#tribe-events-header').data('baseurl') + ts.date + '/';
				else
					td.cur_url = base_url + ts.date + '/';
				ts.popping = false;
				tf.pre_ajax(function () {
					tribe_events_calendar_ajax_post();
				});
			}
		}).data('datepicker');

		if (tt.pushstate && !tt.map_view()) {

			var params = 'action=tribe_calendar&eventDate=' + $('#tribe-events-header').data('date');

			if (td.params.length)
				params = params + '&' + td.params;

			history.replaceState({
				"tribe_params": params
			}, ts.page_title, location.href);

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

		$('#tribe-events')
			.on('click', '.tribe-events-sub-nav a', function (e) {
				e.preventDefault();
				if (ts.ajax_running)
					return;
				var $this = $(this);
				ts.date = $this.data("month");
				tf.update_picker(ts.date);
				if (ts.filter_cats)
					td.cur_url = $('#tribe-events-header').data('baseurl');
				else
					td.cur_url = $this.attr("href");
				ts.popping = false;
				tf.pre_ajax(function () {
					tribe_events_calendar_ajax_post();
				});
			})
			.on('click', 'td.tribe-events-thismonth', function (e) {
				e.preventDefault();
				var day_link = $(this).find('[id^="tribe-events-daynum"] a').attr('href');
				if(typeof day_link !== 'undefined')
					window.location = day_link;
			})
			.on('click', 'td.tribe-events-thismonth a', function (e) {
				e.stopPropagation();
			});

		tf.snap('#tribe-bar-form', 'body', '#tribe-events-footer .tribe-events-nav-previous a, #tribe-events-footer .tribe-events-nav-next a');

		/**
		 * @function tribe_events_bar_calendar_ajax_actions
		 * @since 3.0
		 * @desc On events bar submit, this function collects the current state of the bar and sends it to the month view ajax handler.
		 * @param {event} e The event object.
		 */

		function tribe_events_bar_calendar_ajax_actions(e) {
			if (tribe_events_bar_action != 'change_view') {
				e.preventDefault();
				if (ts.ajax_running)
					return;
				if ($tribedate.val().length) {
					ts.date = $tribedate.val();
				} else {
					if(!date_mod)
						ts.date = td.cur_date.slice(0, -3);
				}

				if (ts.filter_cats) {
					td.cur_url = $('#tribe-events-header').data('baseurl') + ts.date + '/';
				} else {
					td.cur_url = base_url + ts.date + '/';
				}
				ts.popping = false;
				tf.pre_ajax(function () {
					tribe_events_calendar_ajax_post();
				});
			}
		}

		$('form#tribe-bar-form').on('submit', function (e) {
			tribe_events_bar_calendar_ajax_actions(e);
		});

		$(te).on("tribe_ev_runAjax", function () {
			tribe_events_calendar_ajax_post();
		});

		$(te).on("tribe_ev_updatingRecurrence", function () {
			ts.date = $('#tribe-events-header').data("date");
			if (ts.filter_cats)
				td.cur_url = $('#tribe-events-header').data('baseurl') + ts.date + '/';
			else
				td.cur_url = base_url + ts.date + '/';
			ts.popping = false;
		});

		/**
		 * @function tribe_events_calendar_ajax_post
		 * @since 3.0
		 * @desc The ajax handler for month view.
		 * Fires the custom event 'tribe_ev_serializeBar' at start, then 'tribe_ev_collectParams' to gather any additional paramters before actually launching the ajax post request.
		 * As post begins 'tribe_ev_ajaxStart' and 'tribe_ev_monthView_AjaxStart' are fired, and then 'tribe_ev_ajaxSuccess' and 'tribe_ev_monthView_ajaxSuccess' are fired on success.
		 * Various functions in the events plugins hook into these events. They are triggered on the tribe_ev.events object.
		 */

		function tribe_events_calendar_ajax_post() {

			$('.tribe-events-calendar').tribe_spin();
			ts.pushcount = 0;
			ts.ajax_running = true;

			if (!ts.popping) {

				ts.params = {
					action: 'tribe_calendar',
					eventDate: ts.date
				};

				if (ts.category) {
					ts.params['tribe_event_category'] = ts.category;
				}

				ts.url_params = {};

				$(te).trigger('tribe_ev_serializeBar');

				ts.params = $.param(ts.params);
				ts.url_params = $.param(ts.url_params);

				$(te).trigger('tribe_ev_collectParams');

				if (ts.pushcount > 0 || ts.filters) {
					ts.do_string = true;
					ts.pushstate = false;
				} else {
					ts.do_string = false;
					ts.pushstate = true;
				}
			}

			if (tt.pushstate && !ts.filter_cats) {

				dbug && debug.time('Month View Ajax Timer');

				$(te).trigger('tribe_ev_ajaxStart').trigger('tribe_ev_monthView_AjaxStart');

				$.post(
					TribeCalendar.ajaxurl,
					ts.params,
					function (response) {

						ts.initial_load = false;
						tf.enable_inputs('#tribe_events_filters_form', 'input, select');

						if (response.success) {

							ts.ajax_running = false;

							td.ajax_response = {
								'total_count': '',
								'view': response.view,
								'max_pages': '',
								'tribe_paged': '',
								'timestamp': new Date().getTime()
							};

							if(dbug && response.html === 0){
								debug.warn('Month view ajax had an error in the query and returned 0.');
							}

							var $the_content = '';
							if($.isFunction($.fn.parseHTML))
								$the_content = $.parseHTML(response.html);
							else
								$the_content = response.html;

							$('#tribe-events-content').replaceWith($the_content);

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

							$(te).trigger('tribe_ev_ajaxSuccess').trigger('tribe_ev_monthView_ajaxSuccess');

							dbug && debug.timeEnd('Month View Ajax Timer');
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
		dbug && debug.info('TEC Debug: tribe-events-ajax-calendar.js successfully loaded, Tribe Events Init finished');
		dbug && debug.timeEnd('Tribe JS Init Timer');
	});

})(window, document, jQuery, tribe_ev.data, tribe_ev.events, tribe_ev.fn, tribe_ev.state, tribe_ev.tests, tribe_debug);
