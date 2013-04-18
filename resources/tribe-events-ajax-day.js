jQuery(document).ready(function ($) {

    if (tribe_ev.state.filter_cats)
        var base_url = $('#tribe-events-header').attr('data-baseurl').slice(0, -11);
    else
        var base_url = $('#tribe-events-header .tribe-events-nav-next a').attr('href').slice(0, -11);

    tribe_ev.state.date = $('#tribe-events-header').attr('data-date');

    function tribe_day_add_classes() {
        if ($('.tribe-events-day-time-slot').length) {
            $('.tribe-events-day-time-slot').find('.vevent:last').addClass('tribe-events-last');
            $('.tribe-events-day-time-slot:first').find('.vevent:first').removeClass('tribe-events-first');
        }
    }

    tribe_day_add_classes();

    if (tribe_ev.tests.pushstate && !tribe_ev.tests.map_view()) {

        var params = 'action=tribe_event_day&eventDate=' + tribe_ev.state.date;

        if (tribe_ev.data.params.length)
            params = params + '&' + tribe_ev.data.params;

        history.replaceState({
            "tribe_params": params,
            "tribe_url_params": tribe_ev.data.params
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

    $('#tribe-events').on('click', '.tribe-events-nav-previous a, .tribe-events-nav-next a', function (e) {
        e.preventDefault();
        if (tribe_ev.state.ajax_running)
            return;
        var $this = $(this);
        tribe_ev.state.popping = false;
        tribe_ev.state.date = $this.attr("data-day");
        if (tribe_ev.state.filter_cats)
            tribe_ev.data.cur_url = base_url + tribe_ev.state.date + '/';
        else
            tribe_ev.data.cur_url = $this.attr("href");
        tribe_ev.fn.update_picker(tribe_ev.state.date);
        tribe_ev.fn.pre_ajax(function () {
            tribe_events_calendar_ajax_post();
        });
    });

    tribe_ev.fn.snap('#tribe-events-bar', '#tribe-events', '#tribe-events-footer .tribe-events-nav-previous a, #tribe-events-footer .tribe-events-nav-next a');

    function tribe_events_bar_dayajax_actions(e) {
        if (tribe_events_bar_action != 'change_view') {
            e.preventDefault();
            if (tribe_ev.state.ajax_running)
                return;
            var picker = $('#tribe-bar-date').val();
            tribe_ev.state.popping = false;
            if (picker.length) {
                tribe_ev.state.date = $('#tribe-bar-date').val();
                tribe_ev.data.cur_url = base_url + tribe_ev.state.date + '/';
            } else {
                tribe_ev.state.date = tribe_ev.data.cur_date;
                tribe_ev.data.cur_url = base_url + tribe_ev.data.cur_date + '/';
            }
            tribe_ev.fn.pre_ajax(function () {
                tribe_events_calendar_ajax_post();
            });

        }
    }

    $('form#tribe-bar-form').on('submit', function (e) {
        tribe_events_bar_dayajax_actions(e);
    });

    if (tribe_ev.tests.live_ajax() && tribe_ev.tests.pushstate) {

        $('#tribe-bar-date').on('changeDate', function (e) {
            if (!tribe_ev.tests.reset_on()) {
                tribe_ev.state.popping = false;
                tribe_ev.state.date = $(this).val();
                tribe_ev.data.cur_url = base_url + tribe_ev.state.date + '/';
                tribe_ev.fn.pre_ajax(function () {
                    tribe_events_calendar_ajax_post();
                });
            }
        });

    }

    $(tribe_ev.events).on("tribe_ev_runAjax", function () {
        tribe_events_calendar_ajax_post();
    });

    $(tribe_ev.events).on("tribe_ev_updatingRecurrence", function () {
        if (tribe_ev.state.filter_cats)
            tribe_ev.data.cur_url = base_url + tribe_ev.state.date + '/';
        else
            tribe_ev.data.cur_url = $('#tribe-events-header').attr("data-baseurl");
        tribe_ev.state.popping = false;
    });

    function tribe_events_calendar_ajax_post() {

        tribe_ev.fn.spin_show();
        tribe_ev.state.pushcount = 0;
        tribe_ev.state.ajax_running = true;

        if (!tribe_ev.state.popping) {

            tribe_ev.state.url_params = '';

            tribe_ev.state.params = {
                action: 'tribe_event_day',
                eventDate: tribe_ev.state.date
            };

            tribe_ev.state.url_params = {
                action: 'tribe_event_day'
            };

            if (tribe_ev.state.category) {
                tribe_ev.state.params['tribe_event_category'] = tribe_ev.state.category;
            }

            $(tribe_ev.events).trigger('tribe_ev_serializeBar');

            tribe_ev.state.params = $.param(tribe_ev.state.params);
            tribe_ev.state.url_params = $.param(tribe_ev.state.url_params);

            $(tribe_ev.events).trigger('tribe_ev_collectParams');

            tribe_ev.state.pushstate = true;
            tribe_ev.state.do_string = false;

            if (tribe_ev.state.pushcount > 0 || tribe_ev.state.filters) {
                tribe_ev.state.pushstate = false;
                tribe_ev.state.do_string = true;
            }
        }

        if (tribe_ev.tests.pushstate && !tribe_ev.state.filter_cats) {

            $(tribe_ev.events).trigger('tribe_ev_ajaxStart').trigger('tribe_ev_dayView_AjaxStart');
            $('#tribe-events-header').tribe_spin();

            $.post(
                TribeCalendar.ajaxurl,
                tribe_ev.state.params,
                function (response) {

                    tribe_ev.state.initial_load = false;
                    tribe_ev.fn.enable_inputs('#tribe_events_filters_form', 'input, select');

                    if (response.success) {

                        tribe_ev.state.ajax_running = false;

                        tribe_ev.data.ajax_response = {
                            'total_count': parseInt(response.total_count),
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

                        if (response.total_count === 0) {
                            $('#tribe-events-header .tribe-events-sub-nav').empty();
                        }
                        $('.tribe-events-promo').next('.tribe-events-promo').remove();
                        $('#tribe-events-content').next('.tribe-clear').remove();

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

                        tribe_day_add_classes();

                        $(tribe_ev.events).trigger('tribe_ev_ajaxSuccess').trigger('tribe_ev_dayView_AjaxSuccess');

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