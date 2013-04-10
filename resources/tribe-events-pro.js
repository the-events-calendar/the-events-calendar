jQuery.extend(tribe_ev.fn, {
    set_recurrence: function (recurrence_on) {
        if (recurrence_on) {
            tribe_ev.state.recurrence = true;
            if (tribe_storage) {
                tribe_storage.setItem('tribeHideRecurrence', '1');
            }
        } else {
            tribe_ev.state.recurrence = false;
            if (tribe_storage) {
                tribe_storage.setItem('tribeHideRecurrence', '0');
            }
        }
    }
});

jQuery.extend(tribe_ev.tests, {
    hide_recurrence: function () {
        return  (jQuery('#tribeHideRecurrence:checked').length) ? true : false;
    }
});

jQuery(document).ready(function ($) {

    var recurrence_on = false;

    if (tribe_ev.tests.hide_recurrence()) {
        tribe_ev.fn.set_recurrence(true);
    }

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
        if ($(this).is(':checked')) {
            recurrence_on = true;
        } else {
            recurrence_on = false;
        }

        tribe_ev.fn.set_recurrence(recurrence_on);

        $(tribe_ev.events).trigger('tribe_ev_updatingRecurrence').trigger('tribe_ev_runAjax');
    });

    $(tribe_ev.events).on("tribe_ev_preCollectBarParams", function() {
        if ($('#tribe-bar-geoloc').length) {
            var tribe_map_val = jQuery('#tribe-bar-geoloc').val();
            if (!tribe_map_val.length) {
                $('#tribe-bar-geoloc-lat, #tribe-bar-geoloc-lng').val('');
            } else {
                if (tribe_ev.state.view_target === 'map')
                    tribe_ev.state.url_params['action'] = 'geosearch';
            }
        }

        if (tribe_storage) {
            if (tribe_storage.getItem('tribeHideRecurrence') === '1' && (tribe_ev.state.view_target !== 'month' && tribe_ev.state.view_target !== 'week')) {
                tribe_ev.state.url_params['tribeHideRecurrence'] = '1';
            }
        }
    });

});