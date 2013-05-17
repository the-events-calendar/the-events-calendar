(function ($, td, te, tf, ts, tt) {

	$(document).ready(function () {

		var tribe_is_paged = tf.get_url_param('tribe_paged');

		if (tribe_is_paged) {
			tf.paged = tribe_is_paged;
		}

		if (tt.pushstate && !tt.map_view()) {

			var params = 'action=tribe_list&tribe_paged=' + ts.paged;

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
					ts.url_params = state.tribe_url_params;
					tf.pre_ajax(function () {
						tribe_events_list_ajax_post();
					});

					tf.set_form(ts.params);
				}
			});
		}

		$('#tribe-events-content-wrapper').on('click', 'li.tribe-events-nav-next a',function (e) {
			e.preventDefault();

			if (ts.ajax_running)
				return;

			if ($(this).parent().is('.tribe-events-past'))
				ts.view = 'past';
			else
				ts.view = 'list';

			td.cur_url = tf.url_path($(this).attr('href'));

			ts.paged++;

			ts.popping = false;
			tf.pre_ajax(function () {
				tribe_events_list_ajax_post();
			});
		}).on('click', 'li.tribe-events-nav-previous a', function (e) {
				e.preventDefault();

				if (ts.ajax_running)
					return;

				if ($(this).parent().is('.tribe-events-past'))
					ts.view = 'past';
				else
					ts.view = 'list';

				td.cur_url = tf.url_path($(this).attr('href'));

				if (ts.paged > 1) {
					ts.paged--;
				}
				ts.popping = false;
				tf.pre_ajax(function () {
					tribe_events_list_ajax_post();
				});
			});

		tf.snap('#tribe-events-content-wrapper', '#tribe-events-content-wrapper', '#tribe-events-footer .tribe-events-nav-previous a, #tribe-events-footer .tribe-events-nav-next a');

		function tribe_events_bar_listajax_actions(e) {
			if (tribe_events_bar_action != 'change_view') {
				e.preventDefault();
				if (ts.ajax_running)
					return;
				ts.paged = 1;
				ts.view = 'list';
				ts.popping = false;
				tf.pre_ajax(function () {
					tribe_events_list_ajax_post();
				});
			}
		}

		if (tt.live_ajax() && tt.pushstate) {
			$('#tribe-events-bar').on('changeDate', '#tribe-bar-date', function (e) {
				if (!tt.reset_on()) {
					ts.popping = false;
					tribe_events_bar_listajax_actions(e);
				}
			});
		}

		$('form#tribe-bar-form').on('submit', function (e) {
			ts.popping = false;
			tribe_events_bar_listajax_actions(e);
		});

		$(te).on("tribe_ev_runAjax", function () {
			tribe_events_list_ajax_post();
		});

		function tribe_events_list_ajax_post() {

			$('#tribe-events-header').tribe_spin();
			ts.ajax_running = true;

			if (!ts.popping) {

				if (ts.filter_cats)
					td.cur_url = $('#tribe-events-header').attr('data-baseurl');

				var tribe_hash_string = $('#tribe-events-list-hash').val();

				ts.params = {
					action: 'tribe_list',
					tribe_paged: ts.paged,
					tribe_event_display: ts.view
				};

				ts.url_params = {
					action: 'tribe_list',
					tribe_paged: ts.paged
				};

				if (tribe_hash_string.length) {
					ts.params['hash'] = tribe_hash_string;
				}

				if (ts.category) {
					ts.params['tribe_event_category'] = ts.category;
				}

				$(te).trigger('tribe_ev_serializeBar');

				ts.params = $.param(ts.params);
				ts.url_params = $.param(ts.url_params);

				$(te).trigger('tribe_ev_collectParams');

				ts.pushstate = false;
				ts.do_string = true;

			}

			if (tt.pushstate && !ts.filter_cats) {

				$(te).trigger('tribe_ev_ajaxStart').trigger('tribe_ev_listView_AjaxStart');

				$.post(
					TribeList.ajaxurl,
					ts.params,
					function (response) {

						tf.spin_hide();
						ts.initial_load = false;
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

							$('#tribe-events-list-hash').val(response.hash);
							$('#tribe-events-content').replaceWith(response.html);
							$('#tribe-events-content').next('.tribe-clear').remove();
							if (response.total_count === 0) {
								$('#tribe-events-header .tribe-events-sub-nav').empty();
							}

							if (ts.do_string) {
								history.pushState({
									"tribe_params": ts.params,
									"tribe_url_params": ts.url_params
								}, '', td.cur_url + '?' + ts.url_params);
							}

							if (ts.pushstate) {
								history.pushState({
									"tribe_params": ts.params,
									"tribe_url_params": ts.url_params
								}, '', td.cur_url);
							}

							$(te).trigger('tribe_ev_ajaxSuccess').trigger('tribe_ev_listView_AjaxSuccess');

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
	});

})(jQuery, tribe_ev.data, tribe_ev.events, tribe_ev.fn, tribe_ev.state, tribe_ev.tests);
