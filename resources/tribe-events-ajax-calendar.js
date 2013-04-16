jQuery(document).ready(function ($) {

    var base_url = $('#tribe-events-header .tribe-events-nav-next a').attr('href').slice(0, -8);
    var initial_date = tribe_ev.fn.get_url_param('tribe-bar-date');
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

    var tribeBarDate = $tribedate.bootstrapDatepicker(tribe_var_datepickerOpts).on('changeDate', function (e) {
        tribeBarDate.hide();
        tribe_ev.fn.update_picker(e.date);
        tribe_ev.state.date = $(this).val();
        if (tribe_ev.tests.live_ajax() && tribe_ev.tests.pushstate) {
            if (tribe_ev.state.ajax_running)
                return;
            if (tribe_ev.state.filter_cats)
                tribe_ev.data.cur_url = $('#tribe-events-header').attr('data-baseurl') + tribe_ev.state.date + '/';
            else
                tribe_ev.data.cur_url = base_url + tribe_ev.state.date + '/';
            tribe_ev.state.popping = false;
            tribe_ev.fn.pre_ajax(function () {
                tribe_events_calendar_ajax_post();
            });
        }
    }).data('datepicker');

    if (tribe_ev.tests.pushstate && !tribe_ev.tests.map_view()) {

        var params = 'action=tribe_calendar&eventDate=' + $('#tribe-events-header').attr('data-date');

        if (tribe_ev.data.params.length)
            params = params + '&' + tribe_ev.data.params;

        history.replaceState({
            "tribe_params": params
        }, '', location.href);

        $(window).on('popstate', function (event) {

            var state = event.originalEvent.state;

            if (state) {
                tribe_ev.state.do_string = false;
                tribe_ev.state.pushstate = false;
                tribe_ev.state.popping = true;
                tribe_ev.state.params = state.tribe_params;
                tribe_ev.fn.pre_ajax(function () {
                    tribe_events_calendar_ajax_post();
                });

                tribe_ev.fn.set_form(tribe_ev.state.params);
            }
        });
    }

    $('#tribe-events').on('click', '.tribe-events-sub-nav a', function (e) {
        e.preventDefault();
        if (tribe_ev.state.ajax_running)
            return;
        var $this = $(this);
        tribe_ev.state.date = $this.attr("data-month");
        tribe_ev.fn.update_picker(tribe_ev.state.date);
        if (tribe_ev.state.filter_cats)
            tribe_ev.data.cur_url = $('#tribe-events-header').attr('data-baseurl');
        else
            tribe_ev.data.cur_url = $this.attr("href");
        tribe_ev.state.popping = false;
        tribe_ev.fn.pre_ajax(function () {
            tribe_events_calendar_ajax_post();
        });
    });

    tribe_ev.fn.snap('#tribe-bar-form', 'body', '#tribe-events-footer .tribe-events-nav-previous a, #tribe-events-footer .tribe-events-nav-next a');

    // events bar intercept submit

    function tribe_events_bar_calajax_actions(e) {
        if (tribe_events_bar_action != 'change_view') {
            e.preventDefault();
            if (tribe_ev.state.ajax_running)
                return;
            if($tribedate.val().length){
                tribe_ev.state.date = $tribedate.val();
            } else {
                tribe_ev.state.date = tribe_ev.data.cur_date.slice(0, -3);
            }

            if (tribe_ev.state.filter_cats) {
                tribe_ev.data.cur_url = $('#tribe-events-header').attr('data-baseurl') + tribe_ev.state.date + '/';
            } else {
                tribe_ev.data.cur_url = base_url + tribe_ev.state.date + '/';
            }
            tribe_ev.state.popping = false;
            tribe_ev.fn.pre_ajax(function () {
                tribe_events_calendar_ajax_post();
            });
        }
    }

    $('form#tribe-bar-form').on('submit', function (e) {
        tribe_events_bar_calajax_actions(e);
    });

    $(tribe_ev.events).on("tribe_ev_runAjax", function () {
        tribe_events_calendar_ajax_post();
    });

    $(tribe_ev.events).on("tribe_ev_updatingRecurrence", function () {
        tribe_ev.state.date = $('#tribe-events-header').attr("data-date");
        if (tribe_ev.state.filter_cats)
            tribe_ev.data.cur_url = $('#tribe-events-header').attr('data-baseurl') + tribe_ev.state.date + '/';
        else
            tribe_ev.data.cur_url = base_url + tribe_ev.state.date + '/';
        tribe_ev.state.popping = false;
    });


    function tribe_events_calendar_ajax_post() {

        $('#tribe-events-header').tribe_spin();
        tribe_ev.state.pushcount = 0;
        tribe_ev.state.ajax_running = true;

        if (!tribe_ev.state.popping) {

            tribe_ev.state.params = {
                action: 'tribe_calendar',
                eventDate: tribe_ev.state.date
            };

            if (tribe_ev.state.category) {
                tribe_ev.state.params['tribe_event_category'] = tribe_ev.state.category;
            }

            tribe_ev.state.url_params = {};

            $(tribe_ev.events).trigger('tribe_ev_serializeBar');

            tribe_ev.state.params = $.param(tribe_ev.state.params);
            tribe_ev.state.url_params = $.param(tribe_ev.state.url_params);

            $(tribe_ev.events).trigger('tribe_ev_collectParams');

            if (tribe_ev.state.pushcount > 0 || tribe_ev.state.filters) {
                tribe_ev.state.do_string = true;
                tribe_ev.state.pushstate = false;
            } else {
                tribe_ev.state.do_string = false;
                tribe_ev.state.pushstate = true;
            }
        }

        if (tribe_ev.tests.pushstate && !tribe_ev.state.filter_cats) {

            $(tribe_ev.events).trigger('tribe_ev_ajaxStart').trigger('tribe_ev_monthView_AjaxStart');

            $.post(
                TribeCalendar.ajaxurl,
                tribe_ev.state.params,
                function (response) {

                    tribe_ev.fn.spin_hide();
                    tribe_ev.state.initial_load = false;
                    tribe_ev.fn.enable_inputs('#tribe_events_filters_form', 'input, select');

                    if (response.success) {

                        tribe_ev.state.ajax_running = false;

                        tribe_ev.data.ajax_response = {
                            'total_count': '',
                            'view': response.view,
                            'max_pages': '',
                            'tribe_paged': '',
                            'timestamp': new Date().getTime()
                        };

                        $('#tribe-events-content')
                            .replaceWith(
                                    $('<div />').append(response.html)
                                    .find('#tribe-events-content')
                             );

                        var page_title = $("#tribe-events-header").attr('data-title');

                        $(document).attr('title', page_title);

                        if (tribe_ev.state.do_string) {
                            tribe_ev.data.cur_url = tribe_ev.data.cur_url + '?' + tribe_ev.state.url_params;
                            history.pushState({
                                "tribe_date": tribe_ev.state.date,
                                "tribe_params": tribe_ev.state.params
                            }, page_title, tribe_ev.data.cur_url);
                        }

                        if (tribe_ev.state.pushstate) {
                            history.pushState({
                                "tribe_date": tribe_ev.state.date,
                                "tribe_params": tribe_ev.state.params
                            }, page_title, tribe_ev.data.cur_url);
                        }

                        $(tribe_ev.events).trigger('tribe_ev_ajaxSuccess').trigger('tribe_ev__monthView_ajaxSuccess');
                    }
                }
            );

        } else {
            if (tribe_ev.state.do_string)
                window.location = tribe_ev.data.cur_url + '?' + tribe_ev.state.url_params;
            else
                window.location = tribe_ev.data.cur_url;
        }
    }
});
