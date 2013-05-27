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

		var tribe_is_paged = tf.get_url_param('tribe_paged');

		ts.view = 'photo';

		if (tribe_is_paged) {
			ts.paged = tribe_is_paged;
		}

		function tribe_show_loader() {
			$('.photo-loader').show();
			$('#tribe-events-photo-events').addClass("photo-hidden");
		}

		function tribe_hide_loader() {
			$('.photo-loader').hide();
			$('#tribe-events-photo-events').removeClass("photo-hidden").animate({"opacity": "1"}, {duration: 600});
		}

		function tribe_setup_isotope($container) {
			if ($().isotope) {

				var tribe_not_initial_resize = false;
				var tribe_last_width = 0;
				var container_width = 0;

				$container.imagesLoaded(function () {
					$container.isotope({
						transformsEnabled: false,
						containerStyle: {
							position: 'relative',
							overflow: 'visible'
						}
					}, tribe_hide_loader());
				});


				$container.resize(function () {
					container_width = $container.width();
					if (container_width < 645) {
						$container.addClass('photo-two-col');
					} else {
						$container.removeClass('photo-two-col');
					}

					if (tribe_not_initial_resize && container_width !== tribe_last_width) {
						$container.isotope('reLayout');
					}

					tribe_not_initial_resize = true;
					tribe_last_width = container_width;
				});

			} else {
				$('#tribe-events-photo-events').removeClass("photo-hidden").css("opacity", "1");
			}
		}

		// $('#tribe-events-header .tribe-events-ajax-loading').clone().addClass("photo-loader").appendTo('#tribe-events-content');

		var $container = $('#tribe-events-photo-events');

		tribe_setup_isotope($container);

		if ($container.width() < 643) {
			$container.addClass('photo-two-col');
		}

		if (tt.pushstate && !tt.map_view()) {

			var params = 'action=tribe_photo&tribe_paged=' + ts.paged;

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

		$('#tribe-events').on('click', 'li.tribe-events-nav-next a',function (e) {
			e.preventDefault();
			if (ts.ajax_running)
				return;
			if (ts.view === 'past') {
				if (ts.paged === 1) {
					ts.view = 'photo';
				} else {
					ts.paged--;
				}
			} else {
				ts.paged++;
			}
			ts.popping = false;
			tf.pre_ajax(function () {
				tribe_events_list_ajax_post();
			});
		}).on('click', 'li.tribe-events-nav-previous a', function (e) {
				e.preventDefault();
				if (ts.ajax_running)
					return;
				if (ts.view === 'photo') {
					if (ts.paged === 1) {
						ts.view = 'past';
					} else {
						ts.paged--;
					}
				} else {
					ts.paged++;
				}
				ts.popping = false;
				tf.pre_ajax(function () {
					tribe_events_list_ajax_post();
				});
			});

		function tribe_events_bar_photoajax_actions(e) {
			if (tribe_events_bar_action != 'change_view') {
				e.preventDefault();
				if (ts.ajax_running)
					return;
				ts.paged = 1;
				ts.popping = false;
				tf.pre_ajax(function () {
					tribe_events_list_ajax_post();
				});
			}
		}

		if (tt.live_ajax() && tt.pushstate) {
			$('#tribe-bar-date').on('changeDate', function (e) {
				if (!tt.reset_on())
					tribe_events_bar_photoajax_actions(e)
			});
		}

		$(te).on("tribe_ev_updatingRecurrence", function () {
			ts.popping = false;
		});

		$('#tribe-bar-form').on('submit', function (e) {
			if (tribe_events_bar_action != 'change_view') {
				tribe_events_bar_photoajax_actions(e)
			}
		});

		tf.snap('#tribe-events-content', '#tribe-events-content', '#tribe-events-footer .tribe-events-nav-previous a, #tribe-events-footer .tribe-events-nav-next a');

		$(te).on("tribe_ev_runAjax", function () {
			tribe_events_list_ajax_post();
		});

		function tribe_events_list_ajax_post() {

			$('#tribe-events-content').tribe_spin();
			tribe_show_loader();

			if (!ts.popping) {

				ts.ajax_running = true;
				if (ts.filter_cats)
					td.cur_url = $('#tribe-events-header').attr('data-baseurl');

				var tribe_hash_string = $('#tribe-events-list-hash').val();

				ts.params = {
					action: 'tribe_photo',
					tribe_paged: ts.paged,
					tribe_event_display: ts.view
				};

				ts.url_params = {
					action: 'tribe_photo',
					tribe_paged: ts.paged,
					tribe_event_display: ts.view
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

				dbug && debug.time('Photo View Ajax Timer');
				$(te).trigger('tribe_ev_ajaxStart').trigger('tribe_ev_photoView_AjaxStart');

				$.post(
					TribePhoto.ajaxurl,
					ts.params,
					function (response) {

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
							$('#tribe-events-content').prev('#tribe-events-list-hash').remove();
							$('.tribe-events-promo').next('.tribe-events-promo').remove();

							if (response.view === 'photo') {
								if (response.max_pages == ts.paged) {
									$('.tribe-events-nav-next').hide();
								} else {

									$('.tribe-events-nav-next').show();
								}
							} else {
								if (response.max_pages == ts.paged) {
									$('.tribe-events-nav-previous').hide();
								} else {
									$('.tribe-events-nav-previous').show();
								}
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

							tribe_setup_isotope($('#tribe-events-photo-events'));

							$(te).trigger('tribe_ev_ajaxSuccess').trigger('tribe_ev_photoView_AjaxSuccess');

							dbug && debug.timeEnd('Photo View Ajax Timer');

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

		dbug && debug.info('tribe-events-photo-view.js successfully loaded');
		ts.view && dbug && debug.timeEnd('Tribe JS Init Timer');

	});

})(jQuery, tribe_ev.data, tribe_ev.events, tribe_ev.fn, tribe_ev.state, tribe_ev.tests, tribe_debug);
