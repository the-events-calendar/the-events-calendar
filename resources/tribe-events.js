// tribe local storage

var tribe_storage, t_fail, t_uid;
try {
    t_uid = new Date;
    (tribe_storage = window.localStorage).setItem(t_uid, t_uid);
    t_fail = tribe_storage.getItem(t_uid) != t_uid;
    tribe_storage.removeItem(t_uid);
    t_fail && (tribe_storage = false);
} catch (e) {
}

// live ajax timer 

var tribe_ajax_timer;

// jquery functions

(function ($) {
    $.fn.tribe_clear_form = function () {
        return this.each(function () {
            var type = this.type, tag = this.tagName.toLowerCase();
            if (tag == 'form')
                return jQuery(':input', this).tribe_clear_form();
            if (type == 'text' || type == 'password' || tag == 'textarea')
                this.value = '';
            else if (type == 'checkbox' || type == 'radio')
                this.checked = false;
            else if (tag == 'select')
                this.selectedIndex = 0;
        });
    };
    $.fn.tribe_has_attr = function (name) {
        return this.attr(name) !== undefined;
    };
    // $(element).tribe_spin()
    $.fn.tribe_spin = function() {
        $loadingImg = $('.tribe-events-ajax-loading:first').clone().addClass('tribe-events-active-spinner');
        $loadingImg.appendTo(this);
        $(this).addClass('tribe-events-loading');
    }
})(jQuery);

// tribe events object

var tribe_ev = window.tribe_ev || {};

(function ($) {

    tribe_ev.fn = {
        current_date: function () {
            var today = new Date();
            var dd = today.getDate();
            var mm = today.getMonth() + 1;
            var yyyy = today.getFullYear();
            if (dd < 10) {
                dd = '0' + dd
            }
            if (mm < 10) {
                mm = '0' + mm
            }
            return yyyy + '-' + mm + '-' + dd;
        },
        disable_inputs: function (parent, type) {
            $(parent).find(type).prop('disabled', true);
            if ($(parent).find('.select2-container').length) {
                $(parent).find('.select2-container').each(function () {
                    var s2_id = $(this).attr('id');
                    var $this = $('#' + s2_id);
                    $this.select2("disable");
                });
            }
        },
        disable_empty: function (parent, type) {
            $(parent).find(type).each(function () {
                if ($(this).val() === '') {
                    $(this).prop('disabled', true);
                }
            });
        },
        enable_inputs: function (parent, type) {
            $(parent).find(type).prop('disabled', false);
            if ($(parent).find('.select2-container').length) {
                $(parent).find('.select2-container').each(function () {
                    var s2_id = $(this).attr('id');
                    var $this = $('#' + s2_id);
                    $this.select2("enable");
                });
            }
        },
        get_base_url: function () {
            var base_url = '';
            if ($('#tribe-events-header').length){
                base_url = $('#tribe-events-header').attr('data-baseurl');
            }
            return base_url;
        },
        get_category: function () {
            if (tribe_ev.fn.is_category())
                return $('#tribe-events').attr('data-category');
            else
                return '';
        },
        get_day: function () {
            var dp_day = '';
            if ($('#tribe-bar-date').length) {
                dp_day = $('#tribe-bar-date-day').val();
            }
            return dp_day;
        },
        get_params: function () {
            return location.search.substr(1);
        },
        get_url_param: function (tribe_param_name) {
            return decodeURIComponent((new RegExp('[?|&]' + tribe_param_name + '=' + '([^&;]+?)(&|#|;|$)').exec(location.search) || [, ""])[1].replace(/\+/g, '%20')) || null;
        },
        hide_settings: function () {
            $('#tribe-events-bar [class^="tribe-bar-button-"]')
                .removeClass('open')
                .next('.tribe-bar-drop-content')
                .hide();
        },
        in_params: function (params, term) {
            return params.toLowerCase().indexOf(term);
        },
        is_category: function () {
            return ($('#tribe-events').length && $('#tribe-events').tribe_has_attr('data-category') && $('#tribe-events').attr('data-category') !== '') ? true : false;
        },
        make_slug: function (string) {
            var string_h = string.replace(/\s/g, '-');
            var slug = string_h.replace(/[^a-zA-Z0-9\-]/g, '');
            return slug.toLowerCase();
        },
        parse_string: function (string) {
            var map = {};
            string.replace(/([^&=]+)=?([^&]*)(?:&+|$)/g, function (match, key, value) {
                (map[key] = map[key] || []).push(value);
            });
            return map;
        },
        pre_ajax: function (tribe_ajax_callback) {
            if (tribe_ajax_callback && typeof( tribe_ajax_callback ) === "function") {
                tribe_ajax_callback();
            }
        },
        serialize: function (form, type) {
            tribe_ev.fn.enable_inputs(form, type);
            tribe_ev.fn.disable_empty(form, type);
            var params = $(form).serialize();
            tribe_ev.fn.disable_inputs(form, type);
            return params;
        },
        set_form: function (params) {
            $('body').addClass('tribe-reset-on');

            if ($('#tribe-bar-form').length) {
                $('#tribe-bar-form').tribe_clear_form();
            }

            params = tribe_ev.fn.parse_string(params);

            $.each(params, function (key, value) {
                if (key !== 'action') {
                    var name = decodeURI(key);
                    var $target = '';
                    if (value.length === 1) {
                        if ($('[name="' + name + '"]').is('input[type="text"], input[type="hidden"]')) {
                            $('[name="' + name + '"]').val(value);
                        } else if ($('[name="' + name + '"][value="' + value + '"]').is(':checkbox, :radio')) {
                            $('[name="' + name + '"][value="' + value + '"]').prop("checked", true);
                        } else if ($('[name="' + name + '"]').is('select')) {
                            $('select[name="' + name + '"] option[value="' + value + '"]').attr('selected', true);
                        }
                    } else {
                        for (var i = 0; i < value.length; i++) {
                            $target = $('[name="' + name + '"][value="' + value[i] + '"]');
                            if ($target.is(':checkbox, :radio')) {
                                $target.prop("checked", true);
                            } else {
                                $('select[name="' + name + '"] option[value="' + value[i] + '"]').attr('selected', true);
                            }
                        }
                    }
                }
            });

            $('body').removeClass('tribe-reset-on');
        },
        setup_ajax_timer: function (callback) {
            clearTimeout(tribe_ajax_timer);
            if (!tribe_ev.tests.reset_on()) {
                tribe_ajax_timer = setTimeout(function () {
                    callback();
                }, 500);
            }
        },
        snap: function (container, trigger_parent, trigger) {
            $(trigger_parent).on('click', trigger, function (e) {
                $('html, body').animate({scrollTop: $(container).offset().top - 120}, {duration: 0});
            });
        },
        spin_hide: function () {
            $('#tribe-events-footer, #tribe-events-header').find('.tribe-events-ajax-loading').hide();
        },
        spin_show: function () {
            $('#tribe-events-footer, #tribe-events-header').find('.tribe-events-ajax-loading').show();
        },
        tooltips: function () {

            $('#tribe-events').on('mouseenter', 'div[id*="tribe-events-event-"], div[id*="tribe-events-daynum-"]:has(a), div.event-is-recurring',function () {

                var bottomPad = 0;
                var $this = $(this);
                var $body = $('body');

                if ($body.hasClass('events-gridview')) { // Cal View Tooltips
                    bottomPad = $this.find('a').outerHeight() + 18;
                } else if ($body.is('.single-tribe_events, .events-list')) { // Single/List View Recurring Tooltips
                    bottomPad = $this.outerHeight() + 12;
                } else if ($body.is('.tribe-events-photo')) { // Photo View
                    bottomPad = $this.outerHeight() + 10;
                }

                // Widget Tooltips
                if ($this.parents('.tribe-events-calendar-widget').length) {
                    bottomPad = $this.outerHeight() - 6;
                }
                if (!$('body').hasClass('tribe-events-week')) {
                    $this.find('.tribe-events-tooltip').css('bottom', bottomPad).show();
                }

            }).on('mouseleave', 'div[id*="tribe-events-event-"], div[id*="tribe-events-daynum-"]:has(a), div.event-is-recurring', function () {
                    $(this).find('.tribe-events-tooltip').stop(true, false).fadeOut(200);
                });
        },
        update_picker: function (date) {
            if ($().bootstrapDatepicker && $("#tribe-bar-date").length) {
                $("#tribe-bar-date").bootstrapDatepicker("setValue", date);
            } else if ($("#tribe-bar-date").length) {
                $("#tribe-bar-date").val(date);
            }
        },
        url_path: function (url) {
            return url.split("?")[0];
        }
    };

    tribe_ev.tests = {
        live_ajax: function () {
            return ($('#tribe-events').length && $('#tribe-events').tribe_has_attr('data-live_ajax') && $('#tribe-events').attr('data-live_ajax') == '1') ? true : false;
        },
        map_view: function () {
            return ( typeof GeoLoc !== 'undefined' && GeoLoc.map_view ) ? true : false;
        },
        pushstate: !!(window.history && history.pushState),
        reset_on: function () {
            return $('body').is('.tribe-reset-on');
        },
        starting_delim: function () {
            return tribe_ev.state.cur_url.indexOf('?') != -1 ? '&' : '?';
        }
    };

    tribe_ev.data = {
        ajax_response: {},
        base_url: '',
        cur_url: tribe_ev.fn.url_path(document.URL),
        cur_date: tribe_ev.fn.current_date(),
        initial_url: tribe_ev.fn.url_path(document.URL),
        params: tribe_ev.fn.get_params()
    };

    tribe_ev.events = {};

    tribe_ev.state = {
        ajax_running: false,
        category: '',
        date: '',
        do_string: false,
        filters: false,
        filter_cats: false,
        initial_load: true,
        paged: 1,
        params: {},
        popping: false,
        pushstate: true,
        pushcount: 0,
        recurrence: false,
        url_params: {},
        view: '',
        view_target: ''
    };

})(jQuery);

jQuery(document).ready(function ($) {

    tribe_ev.state.category = tribe_ev.fn.get_category();
    tribe_ev.data.base_url = tribe_ev.fn.get_base_url();

    var tribe_display = tribe_ev.fn.get_url_param('tribe_event_display');

    if (tribe_display) {
        tribe_ev.state.view = tribe_display;
    } else if ($('#tribe-events-header').length && $('#tribe-events-header').tribe_has_attr('data-view')) {
        tribe_ev.state.view = $('#tribe-events-header').attr('data-view');
    }

    /* Let's hide the widget calendar if we find more than one instance */
    $(".tribe-events-calendar-widget").not(":eq(0)").hide();

    tribe_ev.fn.tooltips();


    //remove border on list view event before month divider
    if ($('.tribe-events-list').length) {
        $('.tribe-events-list-separator-month').prev('.vevent').addClass('tribe-event-end-month');
    }

    // ajax complete function to remove active spinner
    $(tribe_ev.events).on( 'tribe_ev_ajaxSuccess', function() {
        $('.tribe-events-active-spinner').remove();
    });
});
