(function ($, td, te, tf, ts, tt, dbug) {

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

		var base_url = $('#tribe-events-header .tribe-events-nav-next a').attr('href').slice(0, -8);
		var initial_date = tf.get_url_param('tribe-bar-date');
		var $tribedate = $('#tribe-bar-date');

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

		var tribeBarDate = $tribedate.bootstrapDatepicker(tribe_var_datepickerOpts).on('changeDate',function (e) {
			tribeBarDate.hide();
			var $this = $(this);
			tf.update_picker(e.date);
			if ($this.val() === '') {
				return;
			}
			ts.date = $this.val();
			if (tt.live_ajax() && tt.pushstate) {
				if (ts.ajax_running)
					return;
				if (ts.filter_cats)
					td.cur_url = $('#tribe-events-header').attr('data-baseurl') + ts.date + '/';
				else
					td.cur_url = base_url + ts.date + '/';
				ts.popping = false;
				tf.pre_ajax(function () {
					tribe_events_calendar_ajax_post();
				});
			}
		}).data('datepicker');

		if (tt.pushstate && !tt.map_view()) {

			var params = 'action=tribe_calendar&eventDate=' + $('#tribe-events-header').attr('data-date');

			if (td.params.length)
				params = params + '&' + td.params;

			history.replaceState({
				"tribe_params": params
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

		$('#tribe-events').on('click', '.tribe-events-sub-nav a', function (e) {
			e.preventDefault();
			if (ts.ajax_running)
				return;
			var $this = $(this);
			ts.date = $this.attr("data-month");
			tf.update_picker(ts.date);
			if (ts.filter_cats)
				td.cur_url = $('#tribe-events-header').attr('data-baseurl');
			else
				td.cur_url = $this.attr("href");
			ts.popping = false;
			tf.pre_ajax(function () {
				tribe_events_calendar_ajax_post();
			});
		});

		tf.snap('#tribe-bar-form', 'body', '#tribe-events-footer .tribe-events-nav-previous a, #tribe-events-footer .tribe-events-nav-next a');

		// events bar intercept submit

		function tribe_events_bar_calajax_actions(e) {
			if (tribe_events_bar_action != 'change_view') {
				e.preventDefault();
				if (ts.ajax_running)
					return;
				if ($tribedate.val().length) {
					ts.date = $tribedate.val();
				} else {
					ts.date = td.cur_date.slice(0, -3);
				}

				if (ts.filter_cats) {
					td.cur_url = $('#tribe-events-header').attr('data-baseurl') + ts.date + '/';
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
			tribe_events_bar_calajax_actions(e);
		});

		$(te).on("tribe_ev_runAjax", function () {
			tribe_events_calendar_ajax_post();
		});

		$(te).on("tribe_ev_updatingRecurrence", function () {
			ts.date = $('#tribe-events-header').attr("data-date");
			if (ts.filter_cats)
				td.cur_url = $('#tribe-events-header').attr('data-baseurl') + ts.date + '/';
			else
				td.cur_url = base_url + ts.date + '/';
			ts.popping = false;
		});


		function tribe_events_calendar_ajax_post() {

			$('#tribe-events-header').tribe_spin();
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

							$('#tribe-events-content').replaceWith(response.html);

							var page_title = $("#tribe-events-header").attr('data-title');

							$(document).attr('title', page_title);

							if (ts.do_string) {
								td.cur_url = td.cur_url + '?' + ts.url_params;
								history.pushState({
									"tribe_date": ts.date,
									"tribe_params": ts.params
								}, page_title, td.cur_url);
							}

							if (ts.pushstate) {
								history.pushState({
									"tribe_date": ts.date,
									"tribe_params": ts.params
								}, page_title, td.cur_url);
							}

							$(te).trigger('tribe_ev_ajaxSuccess').trigger('tribe_ev__monthView_ajaxSuccess');
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
		dbug && debug.info('tribe-events-ajax-calendar.js successfully loaded');
	});

})(jQuery, tribe_ev.data, tribe_ev.events, tribe_ev.fn, tribe_ev.state, tribe_ev.tests, tribe_debug);
