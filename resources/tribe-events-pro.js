jQuery(document).ready(function ($) {
    function tribe_ical_url() {
        var url = document.URL;
        var separator = '?';

        if (url.indexOf('?') > 0)
            separator = '&';

        var new_link = url + separator + 'ical=1' + '&' + 'tribe_display=' + tribe_ev.state.view;

        $('a.tribe-events-ical').attr('href', new_link);
    }

    $(tribe_ev.events).on("tribe_ev_ajaxSuccess", function () {
        tribe_ical_url();
    });

    tribe_ical_url();

    tribe_ev.state.recurrence = tribe_ev.tests.hide_recurrence();

    $('#tribe-events').on('click', '#tribeHideRecurrence', function () {
        tribe_ev.state.popping = false;
        tribe_ev.state.do_string = true;
        if ($('this').is(':checked')) {
            tribe_ev.state.recurrence = true;
        } else {
            tribe_ev.state.recurrence = false;
        }
        $(tribe_ev.events).trigger('tribe_ev_updatingRecurrence').trigger('tribe_ev_runAjax');
    });
});