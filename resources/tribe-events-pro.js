(function( $ ) {

    $.extend(tribe_ev.fn, {

        pre_ajax: function (callback) {
            if ($('#tribe-bar-geoloc').length) {
                var val = $('#tribe-bar-geoloc').val();
                if (val.length) {
                    tribe_ev.fn.process_geocoding(val, function (results) {

                        tribe_ev.geoloc.geocodes = results;

                        if (tribe_ev.geoloc.geocodes.length > 1) {
                            tribe_ev.fn.print_geo_options();
                        } else {
                            var lat = results[0].geometry.location.lat();
                            var lng = results[0].geometry.location.lng();
                            if (lat)
                                $('#tribe-bar-geoloc-lat').val(lat);

                            if (lng)
                                $('#tribe-bar-geoloc-lng').val(lng);

                            if (callback && typeof( callback ) === "function") {
                                if ($("#tribe_events_filter_item_geofence").length)
                                    $("#tribe_events_filter_item_geofence").show();
                                callback();
                            }
                        }
                    });
                } else {
                    $('#tribe-bar-geoloc-lat, #tribe-bar-geoloc-lng').val('');
                    if (callback && typeof( callback ) === "function") {
                        if ($("#tribe_events_filter_item_geofence").length) {
                            $('#tribe_events_filter_item_geofence input').prop('checked', false);
                            $("#tribe_events_filter_item_geofence").hide().find('select').prop('selectedIndex', 0);
                        }
                        callback();
                    }
                }
            } else {

                if (callback && typeof( callback ) === "function") {
                    callback();
                }
            }
        },
        print_geo_options: function (){
            $("#tribe-geo-links").empty();
            $("#tribe-geo-options").show();
            for (var i = 0; i < tribe_ev.geoloc.geocodes.length; i++) {
                $("<a/>").text(tribe_ev.geoloc.geocodes[i].formatted_address).attr("href", "#").addClass('tribe-geo-option-link').attr('data-index', i).appendTo("#tribe-geo-links");
                if (tribe_ev.tests.map_view()) {
                    tribe_ev.fn.map_add_marker(
                        tribe_ev.geoloc.geocodes[i].geometry.location.lat(),
                        tribe_ev.geoloc.geocodes[i].geometry.location.lng(),
                        tribe_ev.geoloc.geocodes[i].formatted_address
                    );
                }
            }
        },
        process_geocoding: function (location, callback) {
            var request = {
                address: location
            };

            tribe_ev.geoloc.geocoder.geocode(request, function (results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    callback(results);
                    return results;
                }



                if (status == google.maps.GeocoderStatus.ZERO_RESULTS) {
                    if (GeoLoc.map_view) {
                        spin_end();
                    }
                    return status;
                }

                return status;
            });
        },
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

    $.extend(tribe_ev.tests, {
        hide_recurrence: function () {
            return  ($('#tribeHideRecurrence:checked').length) ? true : false;
        }
    });

})( jQuery );

tribe_ev.geoloc = {
    map: [],
    geocoder: [],
    geocodes: [],
    bounds: [],
    markers: []
};

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

    if (!tribe_ev.tests.map_view()) {

        $("#tribe-events").on('click', '#tribe-geo-options a', function (e) {
            e.preventDefault();
            var $this = $(this);

            $("#tribe-geo-options a").removeClass('tribe-option-loaded');
            $this.addClass('tribe-option-loaded');

            $('#tribe-bar-geoloc').val($this.text());
            $('#tribe-bar-geoloc-lat').val(tribe_ev.geoloc.geocodes[$this.attr('data-index')].geometry.location.lat());
            $('#tribe-bar-geoloc-lng').val(tribe_ev.geoloc.geocodes[$this.attr('data-index')].geometry.location.lng());

            tribe_ev.fn.pre_ajax(function () {
                $(tribe_ev.events).trigger('tribe_ev_runAjax');
                $("#tribe-geo-options").hide();
            });

        });

        tribe_ev.fn.snap('#tribe-geo-wrapper', '#tribe-geo-wrapper', '#tribe-events-footer .tribe-events-nav-previous a, #tribe-events-footer .tribe-events-nav-next a');

    }

});