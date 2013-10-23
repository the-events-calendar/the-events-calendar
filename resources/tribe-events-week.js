/**
 * @file This file contains all week view specific javascript.
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

		var $tribe_container = $('#tribe-events'),
			$tribe_bar = $('#tribe-events-bar'),
			$tribe_header = $('#tribe-events-header'),
			start_day = 0,
			date_mod = false;

		if($tribe_header.length)
			start_day = $tribe_header.data('startofweek');

		$tribe_bar.addClass('tribe-has-datepicker');

		ts.date = $tribe_header.data('date');

		function disableSpecificWeekDays(date) {
			var start_day = $tribe_header.data('startofweek');
			var daysToDisable = [0, 1, 2, 3, 4, 5, 6];
			delete daysToDisable[start_day];
			var day = date.getDay();
			for (i = 0; i < daysToDisable.length; i++) {
				if ($.inArray(day, daysToDisable) != -1) {
					return 'disabled';
				}
			}
			return '';
		}

		var tribe_var_datepickerOpts = {
			format: 'yyyy-mm-dd',
			showAnim: 'fadeIn',
			onRender: disableSpecificWeekDays
		};

		var tribeBarDate = $('#tribe-bar-date').bootstrapDatepicker(tribe_var_datepickerOpts).on('changeDate',function (e) {

			var day = ('0' + e.date.getDate()).slice(-2),
				month = ('0' + (e.date.getMonth() + 1)).slice(-2),
				year = e.date.getFullYear(),
				date = year + '-' + month  + '-' + day;

			ts.date = date;

			date_mod = true;
			tribeBarDate.hide();

			if (tt.no_bar() || tt.live_ajax() && tt.pushstate) {
				if (!tt.reset_on())
					tribe_events_bar_weekajax_actions(e, date);
			}

		}).data('datepicker');

		function tribe_go_to_8() {
			var $start = $('.time-row-8AM');
			$('.tribe-week-grid-wrapper').slimScroll({
				height: '500px',
				railVisible: true,
				alwaysVisible: true,
				start: $start
			});
		}

		function tribe_add_right_class() {
			$('.tribe-grid-body .column:eq(5), .tribe-grid-body .column:eq(6), .tribe-grid-body .column:eq(7)').addClass('tribe-events-right');
		}

		function tribe_set_allday_placeholder_height() {
			$('.tribe-event-placeholder').each(function () {
				var pid = $(this).attr("data-event-id");
				var hght = parseInt($('#tribe-events-event-' + pid).outerHeight());
				$(this).height(hght);
			});
		}

		function tribe_set_allday_spanning_events_width() {

			var $ad = $('.tribe-grid-allday');
			var $ad_e = $ad.find('.vevent');
			var ad_c_w = parseInt($('.tribe-grid-content-wrap .column').width()) - 8;

			for (var i = 1; i < 8; i++) {
				if ($ad_e.hasClass('tribe-dayspan' + i)) {
					$ad.find('.tribe-dayspan' + i).children('div').css('width', ad_c_w * i + ((i * 2 - 2) * 4 + (i - 1)) + 'px');
				}
			}

		}

		function tribe_find_overlapped_events($week_events) {

			$week_events.each(function () {

				var $this = $(this);
				var $target = $this.next();

				var css_left = {"left": "0", "width": "65%"};
				var css_right = {"right": "0", "width": "65%"};

				if ($target.length) {

					var tAxis = $target.offset();
					var t_x = [tAxis.left, tAxis.left + $target.outerWidth()];
					var t_y = [tAxis.top, tAxis.top + $target.outerHeight()];
					var thisPos = $this.offset();
					var i_x = [thisPos.left, thisPos.left + $this.outerWidth()];
					var i_y = [thisPos.top, thisPos.top + $this.outerHeight()];

					if (t_x[0] < i_x[1] && t_x[1] > i_x[0] && t_y[0] < i_y[1] && t_y[1] > i_y[0]) {

						if ($this.is('.overlap-right')) {
							$target.css(css_left).addClass('overlap-left');
						} else if ($this.is('.overlap-left')) {
							$target.css(css_right).addClass('overlap-right');
						} else {
							$this.css(css_left);
							$target.css(css_right).addClass('overlap-right');
						}
					}
				}
			});
		}

		function tribe_display_week_view() {

			var $week_events = $(".tribe-grid-body .tribe-grid-content-wrap .column > div[id*='tribe-events-event-']");
			var grid_height = $(".tribe-week-grid-inner-wrap").height();

			$week_events.each(function () {

				// iterate through each event in the main grid and set their length plus position in time.

				var $this = $(this);
				var event_hour = $this.attr("data-hour");
				var event_length = $this.attr("data-duration");
				var event_min = $this.attr("data-min");

				// $event_target is our grid block with the same data-hour value as our event.

				var $event_target = $('.tribe-week-grid-block[data-hour="' + event_hour + '"]');

				// find it's offset from top of main grid container

				var event_position_top =
					$event_target.offset().top -
						$event_target.parent().offset().top -
						$event_target.parent().scrollTop();

				// add the events minutes to the offset (relies on grid block being 60px, 1px per minute, nice)

				event_position_top = parseInt(Math.round(event_position_top)) + parseInt(event_min);

				// test if we've exceeded space because this event runs into next day

				var free_space = parseInt(grid_height) - parseInt(event_length) - parseInt(event_position_top);

				if (free_space < 0) {
					event_length = event_length + free_space - 14;
				}

				// set length and position from top for our event and show it. Also set length for the event anchor so the entire event is clickable.

				$this.css({
					"height": event_length + "px",
					"top": event_position_top + "px"
				}).find('a').css({
						"height": event_length - 16 + "px"
					});
			});

			// Fade our events in upon js load

			$("div[id^='tribe-events-event-']").css({'visibility': 'visible', 'opacity': '0'}).delay(500).animate({"opacity": "1"}, {duration: 250});

			// deal with our overlaps

			tribe_find_overlapped_events($week_events);

			// set the height of the header columns to the height of the tallest

			var header_column_height = $(".tribe-grid-header .tribe-grid-content-wrap .column").height();

			$(".tribe-grid-header .column").height(header_column_height);

			// set the height of the allday columns to the height of the tallest

			var all_day_height = $(".tribe-grid-allday .tribe-grid-content-wrap").height();

			$(".tribe-grid-allday .column").height(all_day_height);

			// set the height of the other columns for week days to be as tall as the main container

			var week_day_height = $(".tribe-grid-body").height();

			$(".tribe-grid-body .tribe-grid-content-wrap .column").height(week_day_height);

		}

		function tribe_week_view_init(callback, resize) {
			tribe_set_allday_placeholder_height();
			tribe_set_allday_spanning_events_width();
			tribe_add_right_class();
			if(resize)
				$('.tribe-grid-content-wrap .column').css('height', 'auto');
			tribe_display_week_view();
			if (callback && typeof( callback ) === "function")
				callback();
		}

		tribe_week_view_init(tribe_go_to_8(), false);

		$('.tribe-events-grid').resize(function () {
			tribe_week_view_init(false, true);
		});

		if (tt.pushstate && !tt.map_view()) {

			var params = 'action=tribe_week&eventDate=' + ts.date;

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
					ts.url_params = state.tribe_url_params;
					tf.pre_ajax(function () {
						tribe_events_week_ajax_post();
					});

					tf.set_form(ts.params);
				}
			});
		}

		$tribe_container.on('click', '.tribe-events-sub-nav a', function (e) {
			e.preventDefault();
			if (ts.ajax_running)
				return;
			var $this = $(this);
			ts.popping = false;
			ts.date = $this.attr("data-week");
			td.cur_url = $this.attr("href");
			tf.update_picker(ts.date);
			tf.pre_ajax(function () {
				tribe_events_week_ajax_post();
			});
		});

		/**
		 * @function tribe_events_bar_weekajax_actions
		 * @since 3.0
		 * @desc On events bar submit, this function collects the current state of the bar and sends it to the week view ajax handler.
		 * @param {event} e The event object.
		 * @param {string} date Date passed by datepicker.
		 */

		function tribe_events_bar_weekajax_actions(e, date) {
			if (tribe_events_bar_action != 'change_view') {
				e.preventDefault();
				if (ts.ajax_running)
					return;

				var $tdate = $('#tribe-bar-date');

				ts.popping = false;

				if (date) {

					ts.date = date;
					td.cur_url = td.base_url + ts.date + '/';

				} else if($tdate.length && $tdate.val() !== ''){

					ts.date = $tdate.val();
					td.cur_url = td.base_url + ts.date + '/';

				} else if(date_mod) {

					td.cur_url = td.base_url + ts.date + '/';

				} else {

					ts.date = td.cur_date;
					td.cur_url = td.base_url + td.cur_date + '/';

				}

				tf.pre_ajax(function () {
					tribe_events_week_ajax_post();
				});
			}
		}

		$('form#tribe-bar-form').on('submit', function (e) {
			tribe_events_bar_weekajax_actions(e, null);
		});

		tf.snap('#tribe-events-content', 'body', '#tribe-events-footer .tribe-events-nav-previous a, #tribe-events-footer .tribe-events-nav-next a');

		$(te).on("tribe_ev_runAjax", function () {
			tribe_events_week_ajax_post();
		});

		/**
		 * @function tribe_events_week_ajax_post
		 * @since 3.0
		 * @desc The ajax handler for week view.
		 * Fires the custom event 'tribe_ev_serializeBar' at start, then 'tribe_ev_collectParams' to gather any additional parameters before actually launching the ajax post request.
		 * As post begins 'tribe_ev_ajaxStart' and 'tribe_ev_weekView_AjaxStart' are fired, and then 'tribe_ev_ajaxSuccess' and 'tribe_ev_weekView_ajaxSuccess' are fired on success.
		 * Various functions in the events plugins hook into these events. They are triggered on the tribe_ev.events object.
		 */

		function tribe_events_week_ajax_post() {

			var $tribe_header = $('#tribe-events-header');

			$('.tribe-events-grid').tribe_spin();
			ts.pushcount = 0;
			ts.ajax_running = true;

			if (!ts.popping) {

				if (ts.filter_cats)
					td.cur_url = td.base_url;

				ts.params = {
					action: 'tribe_week',
					eventDate: ts.date
				};

				ts.url_params = {};

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

			if (tt.pushstate) {

				dbug && debug.time('Week View Ajax Timer');
				$(te).trigger('tribe_ev_ajaxStart').trigger('tribe_ev_weekView_AjaxStart');

				$.post(
					TribeWeek.ajaxurl,
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

							var $the_content = '';
							if($.isFunction($.fn.parseHTML))
								$the_content = $.parseHTML(response.html);
							else
								$the_content = response.html;

							$('#tribe-events-content.tribe-events-week-grid').replaceWith($the_content);

							tribe_week_view_init(tribe_go_to_8(), false);

							$('.tribe-events-grid').resize(function () {
								tribe_week_view_init(false, true);
							});

							$("div[id*='tribe-events-event-']").hide().fadeIn('fast');

							ts.page_title = $('#tribe-events-header').data('title');
							document.title = ts.page_title;

							if (ts.do_string) {
								history.pushState({
									"tribe_url_params": ts.url_params,
									"tribe_params": ts.params
								}, ts.page_title, td.cur_url + '?' + ts.url_params);
							}

							if (ts.pushstate) {
								history.pushState({
									"tribe_url_params": ts.url_params,
									"tribe_params": ts.params
								}, ts.page_title, td.cur_url);
							}

							$(te)
								.trigger('tribe_ev_ajaxSuccess')
								.trigger('tribe_ev_weekView_AjaxSuccess');

							dbug && debug.timeEnd('Week View Ajax Timer');

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
		dbug && debug.info('TEC Debug: tribe-events-week.js successfully loaded');
		ts.view && dbug && debug.timeEnd('Tribe JS Init Timer');
	});

})(window, document, jQuery, tribe_ev.data, tribe_ev.events, tribe_ev.fn, tribe_ev.state, tribe_ev.tests, tribe_debug);
