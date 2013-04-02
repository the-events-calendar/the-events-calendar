tribe_ev.geoloc = {
    map: [],
    geocoder: [],
    geocodes: [],
    bounds: [],
    markers: []
};

tribe_ev.geoloc.geocoder = new google.maps.Geocoder();
tribe_ev.geoloc.bounds = new google.maps.LatLngBounds();

function tribe_process_geocoding(location, callback) {

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
}

jQuery(document).ready(function ($) {

    function tribe_test_location() {

        if ($('#tribe-bar-geoloc').length) {
            var tribe_map_val = $('#tribe-bar-geoloc').val();
            if (tribe_map_val.length) {
                if ($("#tribe_events_filter_item_geofence").length)
                    $("#tribe_events_filter_item_geofence").show();
            } else {
                if ($("#tribe_events_filter_item_geofence").length)
                    $("#tribe_events_filter_item_geofence").hide();
                if ($('#tribe-bar-geoloc-lat, #tribe-bar-geoloc-lng').length)
                    $('#tribe-bar-geoloc-lat, #tribe-bar-geoloc-lng').val('');
            }
        }
    }

    tribe_test_location();

    var options = {
        zoom: 5,
        center: new google.maps.LatLng(GeoLoc.center.max_lat, GeoLoc.center.max_lng),
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };

    if (document.getElementById('tribe-geo-map')) {
        tribe_ev.geoloc.map = new google.maps.Map(document.getElementById('tribe-geo-map'), options);
        tribe_ev.geoloc.bounds = new google.maps.LatLngBounds();

        var minLatlng = new google.maps.LatLng(GeoLoc.center.min_lat, GeoLoc.center.min_lng);
        tribe_ev.geoloc.bounds.extend(minLatlng);

        var maxLatlng = new google.maps.LatLng(GeoLoc.center.max_lat, GeoLoc.center.max_lng);
        tribe_ev.geoloc.bounds.extend(maxLatlng);
    }

    $('#tribe-geo-location').placeholder();

    if (tribe_ev.tests.map_view()) {

        var tribe_is_paged = tribe_ev.fn.get_url_param('tribe_paged');
        if (tribe_is_paged) {
            tribe_ev.state.paged = tribe_is_paged;
        }

        $('body').addClass('events-list');
        tribe_ev.fn.tooltips();
    }

    if (tribe_ev.tests.map_view() && tribe_ev.data.params) {

        var tp = tribe_ev.data.params;
        if (tribe_ev.fn.in_params(tp, "geosearch") >= 0) {
        } else
            tp += '&action=geosearch';
        if (tribe_ev.fn.in_params(tp, "tribe_paged") >= 0) {
        } else
            tp += '&tribe_paged=1';

        tribe_ev.state.params = tp;

        tribe_ev.state.do_string = false;
        tribe_ev.state.pushstate = false;
        tribe_ev.state.popping = true;
        tribe_ev.fn.pre_ajax(function () {
            tribe_map_processOption(null);
        });
    } else if (tribe_ev.tests.map_view()) {

        tribe_ev.state.do_string = false;
        tribe_ev.state.pushstate = false;
        tribe_ev.state.popping = false;
        tribe_ev.state.initial_load = true;
        tribe_ev.fn.pre_ajax(function () {
            tribe_map_processOption(null);
        });
    }

    if (tribe_ev.tests.pushstate && tribe_ev.tests.map_view()) {

        history.replaceState({
            "tribe_paged": tribe_ev.state.paged,
            "tribe_params": tribe_ev.state.params
        }, '', location.href);

        $(window).on('popstate', function (event) {

            var state = event.originalEvent.state;

            if (state) {
                tribe_ev.state.do_string = false;
                tribe_ev.state.pushstate = false;
                tribe_ev.state.popping = true;
                tribe_ev.state.params = state.tribe_params;
                tribe_ev.state.paged = state.tribe_paged;
                tribe_ev.fn.pre_ajax(function () {
                    tribe_map_processOption(null);
                });

                tribe_ev.fn.set_form(tribe_ev.state.params);
            }
        });
    }


    $("#tribe-geo-options").on('click', 'a', function (e) {
        spin_start();
        e.preventDefault();
        $("#tribe-geo-options a").removeClass('tribe-option-loaded');
        $(this).addClass('tribe-option-loaded');

        $('#tribe-bar-geoloc').val($(this).text());
        $('#tribe-bar-geoloc-lat').val(tribe_ev.geoloc.geocodes[$(this).attr('data-index')].geometry.location.lat());
        $('#tribe-bar-geoloc-lng').val(tribe_ev.geoloc.geocodes[$(this).attr('data-index')].geometry.location.lng());


        if (tribe_ev.tests.pushstate) {
            tribe_ev.fn.pre_ajax(function () {
                tribe_map_processOption(null);
                $("#tribe-geo-options").hide();
            });
        } else {
            tribe_ev.fn.pre_ajax(function () {
                $(tribe_ev.events).trigger('tribe_ev_reloadOldBrowser');
            });
        }

    });

    tribe_ev.fn.snap('#tribe-geo-wrapper', '#tribe-geo-wrapper', '#tribe-events-footer .tribe-events-nav-previous a, #tribe-events-footer .tribe-events-nav-next a');

    function tribe_generate_map_params() {
        tribe_ev.state.ajax_running = true;
        tribe_ev.state.params = {
            action: 'geosearch',
            tribe_paged: tribe_ev.state.paged,
            tribe_event_display: tribe_ev.state.view
        };

        $(tribe_ev.events).trigger('tribe_ev_serializeBar');

        tribe_ev.state.params = $.param(tribe_ev.state.params);

        $(tribe_ev.events).trigger('tribe_ev_collectParams');

    }

    $(tribe_ev.events).on("tribe_ev_reloadOldBrowser", function () {
        tribe_generate_map_params();
        window.location = tribe_ev.data.cur_url + '?' + tribe_ev.state.params;
    });


    function tribe_map_processOption(geocode) {
        spin_start();
        deleteMarkers();

        if (!tribe_ev.state.popping) {
            tribe_generate_map_params();
            tribe_ev.state.pushstate = false;
            if (!tribe_ev.state.initial_load) {
                tribe_ev.state.do_string = true;
            }
        }

        $.post(GeoLoc.ajaxurl, tribe_ev.state.params, function (response) {

            $(tribe_ev.events).trigger('tribe_ev_ajaxStart').trigger('tribe_ev_mapView_AjaxStart');

            spin_end();
            tribe_ev.fn.enable_inputs('#tribe_events_filters_form', 'input, select');

            if (response.success) {

                tribe_ev.state.ajax_running = false;

                tribe_ev.data.ajax_response = {
                    'total_count': parseInt(response.total_count),
                    'view': response.view,
                    'max_pages': response.max_pages,
                    'tribe_paged': response.tribe_paged,
                    'timestamp': new Date().getTime()
                };

                tribe_ev.state.initial_load = false;

                $("#tribe-events-geo-content").html(response.html);

                if (response.view === 'map') {
                    if (response.max_pages == response.tribe_paged) {
                        $('.tribe-events-nav-next').hide();
                    } else {

                        $('.tribe-events-nav-next').show();
                    }
                } else {
                    if (response.max_pages == response.tribe_paged) {
                        $('.tribe-events-nav-previous').hide();
                    } else {
                        $('.tribe-events-nav-previous').show();
                    }
                }

                $.each(response.markers, function (i, e) {
                    tribe_map_addMarker(e.lat, e.lng, e.title, e.address, e.link);
                });

                if (tribe_ev.tests.pushstate) {

                    if (tribe_ev.state.do_string) {
                        history.pushState({
                            "tribe_paged": tribe_ev.state.paged,
                            "tribe_params": tribe_ev.state.params
                        }, '', tribe_ev.data.cur_url + '?' + tribe_ev.state.params);
                    }

                    if (tribe_ev.state.pushstate) {
                        history.pushState({
                            "tribe_paged": tribe_ev.state.paged,
                            "tribe_params": tribe_ev.state.params
                        }, '', tribe_ev.data.cur_url);
                    }

                }

                $(tribe_ev.events).trigger('tribe_ev_ajaxSuccess').trigger('tribe_ev_mapView_AjaxSuccess');

                if (response.markers.length > 0) {
                    centerMap();
                }
            }

            spin_end();

        });

    }

    if (tribe_ev.tests.map_view()) {

        var center;

        $("#tribe-geo-map-wrapper").resize(function () {
            center = tribe_ev.geoloc.map.getCenter();
            google.maps.event.trigger(tribe_ev.geoloc.map, "resize");
            tribe_ev.geoloc.map.setCenter(center);
        });

        $('#tribe-events').on('click', 'li.tribe-events-nav-next a',function (e) {
            e.preventDefault();
            if (tribe_ev.state.ajax_running)
                return;
            if (tribe_ev.state.view === 'past') {
                if (tribe_ev.state.paged === 1) {
                    tribe_ev.state.view = 'map';
                } else {
                    tribe_ev.state.paged--;
                }
            } else {
                tribe_ev.state.paged++;
            }
            tribe_ev.state.popping = false;
            if (tribe_ev.tests.pushstate) {
                tribe_ev.fn.pre_ajax(function () {
                    tribe_map_processOption(null);
                });
            } else {
                tribe_ev.fn.pre_ajax(function () {
                    $(tribe_ev.events).trigger('tribe_ev_reloadOldBrowser');
                });
            }
        }).on('click', 'li.tribe-events-nav-previous a', function (e) {
                e.preventDefault();
                if (tribe_ev.state.ajax_running)
                    return;
                if (tribe_ev.state.view === 'map') {
                    if (tribe_ev.state.paged === 1) {
                        tribe_ev.state.view = 'past';
                    } else {
                        tribe_ev.state.paged--;
                    }
                } else {
                    tribe_ev.state.paged++;
                }
                tribe_ev.state.popping = false;
                if (tribe_ev.tests.pushstate) {
                    tribe_ev.fn.pre_ajax(function () {
                        tribe_map_processOption(null);
                    });
                } else {
                    tribe_ev.fn.pre_ajax(function () {
                        $(tribe_ev.events).trigger('tribe_ev_reloadOldBrowser');
                    });
                }
            });

    }

    function tribe_events_bar_mapajax_actions(e) {
        if (tribe_events_bar_action != 'change_view') {
            e.preventDefault();
            if (tribe_ev.state.ajax_running)
                return;
            tribe_ev.state.paged = 1;
            tribe_ev.state.view = 'map';
            tribe_ev.state.popping = false;
            if (tribe_ev.tests.pushstate) {
                tribe_ev.fn.pre_ajax(function () {
                    tribe_map_processOption(null);
                });
            } else {
                tribe_ev.fn.pre_ajax(function () {
                    $(tribe_ev.events).trigger('tribe_ev_reloadOldBrowser');
                });
            }

        }
    }

    if (GeoLoc.map_view && $('form#tribe-bar-form').length) {
        $('#tribe-events-bar').on('changeDate', '#tribe-bar-date', function (e) {
            tribe_events_bar_mapajax_actions(e);
        });
        $('.tribe-bar-settings button[name="settingsUpdate"]').on('click', function (e) {
            tribe_events_bar_mapajax_actions(e);
            tribe_ev.fn.hide_settings();
        });
    }

    if (GeoLoc.map_view) {
        $(tribe_ev.events).on("tribe_ev_runAjax", function () {
            tribe_map_processOption(null);
        });
    }


    function tribe_map_addMarker(lat, lng, title, address, link) {
        var myLatlng = new google.maps.LatLng(lat, lng);

        var marker = new google.maps.Marker({
            position: myLatlng,
            map: tribe_ev.geoloc.map,
            title: title
        });

        var infoWindow = new google.maps.InfoWindow();

        var content_title = title;
        if (link) {
            content_title = $('<div/>').append($("<a/>").attr('href', link).text(title)).html();
        }

        var content = "Event: " + content_title;

        if (address) {
            content = content + "<br/>" + "Address: " + address;
        }

        infoWindow.setContent(content);

        google.maps.event.addListener(marker, 'click', function (event) {
            infoWindow.open(tribe_ev.geoloc.map, marker);
        });

        tribe_ev.geoloc.markers.push(marker);
        tribe_ev.geoloc.bounds.extend(myLatlng);

    }

    function deleteMarkers() {
        if (tribe_ev.geoloc.markers) {
            for (i in tribe_ev.geoloc.markers) {
                tribe_ev.geoloc.markers[i].setMap(null);
            }
            tribe_ev.geoloc.markers.length = 0;
            tribe_ev.geoloc.bounds = new google.maps.LatLngBounds();
        }
    }

    function centerMap() {
        tribe_ev.geoloc.map.fitBounds(tribe_ev.geoloc.bounds);
        if (tribe_ev.geoloc.map.getZoom() > 13) {
            tribe_ev.geoloc.map.setZoom(13);
        }
    }

    function spin_start() {
        $('#tribe-events-footer, #tribe-events-header').find('.tribe-events-ajax-loading').show();
    }

    function spin_end() {
        $('#tribe-events-footer, #tribe-events-header').find('.tribe-events-ajax-loading').hide();
    }

    if (tribe_ev.tests.map_view()) {

        $('form#tribe-bar-form').on('submit', function () {
            if (tribe_events_bar_action != 'change_view') {
                tribe_ev.state.paged = 1;
                spin_start();

                var val = $('#tribe-bar-geoloc').val();

                if (val !== '') {

                    deleteMarkers();
                    $("#tribe-geo-results").empty();
                    $("#tribe-geo-options").hide();
                    $("#tribe-geo-options #tribe-geo-links").empty();

                    tribe_process_geocoding(val, function (results, selected_index) {
                        tribe_ev.geoloc.geocodes = results;

                        spin_end();

                        var lat = results[0].geometry.location.lat();
                        var lng = results[0].geometry.location.lng();

                        if (lat)
                            $('#tribe-bar-geoloc-lat').val(lat);

                        if (lng)
                            $('#tribe-bar-geoloc-lng').val(lng);

                        if (tribe_ev.geoloc.geocodes.length > 1) {
                            $("#tribe-geo-options").show();

                            for (var i = 0; i < tribe_ev.geoloc.geocodes.length; i++) {
                                $("<a/>").text(tribe_ev.geoloc.geocodes[i].formatted_address).attr("href", "#").addClass('tribe-geo-option-link').attr('data-index', i).appendTo("#tribe-geo-options #tribe-geo-links");
                                tribe_map_addMarker(tribe_ev.geoloc.geocodes[i].geometry.location.lat(), tribe_ev.geoloc.geocodes[i].geometry.location.lng(), tribe_ev.geoloc.geocodes[i].formatted_address);
                            }
                            tribe_test_location();
                            centerMap();


                        } else {
                            if (tribe_ev.tests.pushstate) {
                                tribe_test_location();
                                tribe_map_processOption(tribe_ev.geoloc.geocodes[0]);
                            } else {
                                $(tribe_ev.events).trigger('tribe_ev_reloadOldBrowser');
                            }
                        }

                    });

                    return false;
                }

                if (val === '') {
                    $('#tribe-bar-geoloc-lat').val('');
                    $('#tribe-bar-geoloc-lng').val('');
                    $("#tribe-geo-options").hide();
                    //We can show the map even if we don't get a geo query
                    if (tribe_ev.tests.pushstate) {
                        tribe_test_location();
                        tribe_map_processOption(null);
                    } else {
                        $(tribe_ev.events).trigger('tribe_ev_reloadOldBrowser');
                    }
                    spin_end();
                    return false;

                }
                return true;
            }
        });
    }

});
