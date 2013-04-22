/**
 * @global
 * @desc Test for localstorage support. Returns false if not available and tribe_storage as a method if true.
 * @example
 * if (tribe_storage) {
 *      tribe_storage.setItem('cats', 'hairball');
 *      tribe_storage.getItem('cats');
 * }
 */

var tribe_storage, t_fail, t_uid;
try {
    t_uid = new Date;
    (tribe_storage = window.localStorage).setItem(t_uid, t_uid);
    t_fail = tribe_storage.getItem(t_uid) != t_uid;
    tribe_storage.removeItem(t_uid);
    t_fail && (tribe_storage = false);
} catch (e) {}

/**
 * @global
 * @desc Variable used when live ajax is on for delay interval.
 */

var tribe_ajax_timer;

/**
 * @external "jQuery.fn"
 * @desc The jQuery plugin namespace.
 */


(function ($) {
    /**
     * @function external:"jQuery.fn".tribe_clear_form
     * @since 3.0
     * @desc Clear a forms inputs with jquery.
     * @example <caption>Clear a form with the forms id as a selector.</caption>
     * $('#myForm').tribe_clear_form();
     */
    $.fn.tribe_clear_form = function () {
        return this.each(function () {
            var type = this.type, tag = this.tagName.toLowerCase();
            if (tag == 'form')
                return $(':input', this).tribe_clear_form();
            if (type == 'text' || type == 'password' || tag == 'textarea')
                this.value = '';
            else if (type == 'checkbox' || type == 'radio')
                this.checked = false;
            else if (tag == 'select')
                this.selectedIndex = 0;
        });
    };
    /**
     * @function external:"jQuery.fn".tribe_has_attr
     * @since 3.0
     * @desc Check if a given element has an attribute.
     * @example if($('#myLink').tribe_has_attr('data-cats')) {true} else {false}
     */
    $.fn.tribe_has_attr = function (name) {
        return this.attr(name) !== undefined;
    };
    /**
     * @function external:"jQuery.fn".tribe_spin
     * @since 3.0
     * @desc Shows loading spinners for events ajax interactions.
     * @example $('#myElement').tribe_spin();
     */
    $.fn.tribe_spin = function() {
        var $loadingImg = $('.tribe-events-ajax-loading:first').clone().addClass('tribe-events-active-spinner');
        $loadingImg.appendTo(this);
        $(this).addClass('tribe-events-loading');
    }
})(jQuery);

/**
 * @namespace tribe_ev
 * @since 3.0
 * @desc The tribe_ev namespace that stores all custom functions, data, application state and an empty events object to bind custom events to.
 * This namespace loads for all tribe events pages.
 * @example <caption>Test for tribe_ev in your own js and then run one of our functions.</caption>
 * jQuery(document).ready(function ($) {
 *      if (typeof window['tribe_ev'​​​​​​​] !== 'undefined') {
 *          if(tribe_ev.fn.get_category() === 'Cats'){
 *              alert('Meow!');
 *          }
 *      }
 * });
 */

var tribe_ev = window.tribe_ev || {};

(function ($) {
    /**
     * @namespace tribe_ev.fn
     * @since 3.0
     * @desc tribe_ev.fn namespace stores all the custom functions used throughout the core events plugin.
     */
    tribe_ev.fn = {
        /**
         * @function tribe_ev.fn.current_date
         * @since 3.0
         * @desc tribe_ev.fn.current_date simply gets the current date in javascript and formats it to yyyy-mm-dd for use were needed.
         * @example var right_now = tribe_ev.fn.current_date();
         */
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
        /**
         * @function tribe_ev.fn.disable_inputs
         * @since 3.0
         * @desc tribe_ev.fn.disable_inputs disables all inputs of a specified type inside a parent element, and also disables select2 selects if it discovers any.
         * @param {String} parent The top level element you would like all child inputs of the specified type to be disabled for.
         * @param {String} type A single or comma separated string of the type of inputs you would like disabled.
         * @example <caption>Disable all inputs and selects for #myForm.</caption>
         * tribe_ev.fn.disable_inputs( '#myForm', 'input, select' );
         */
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
        /**
         * @function tribe_ev.fn.disable_empty
         * @since 3.0
         * @desc tribe_ev.fn.disable_empty disables all empty inputs of a specified type inside a parent element.
         * @param {String} parent The top level element you would like all empty child inputs of the specified type to be disabled for.
         * @param {String} type A single or comma separated string of the type of empty inputs you would like disabled.
         * @example <caption>Disable all empty inputs and selects for #myForm.</caption>
         * tribe_ev.fn.disable_empty( '#myForm', 'input, select' );
         */
        disable_empty: function (parent, type) {
            $(parent).find(type).each(function () {
                if ($(this).val() === '') {
                    $(this).prop('disabled', true);
                }
            });
        },
        /**
         * @function tribe_ev.fn.enable_inputs
         * @since 3.0
         * @desc tribe_ev.fn.enable_inputs enables all inputs of a specified type inside a parent element, and also enables select2 selects if it discovers any.
         * @param {String} parent The top level element you would like all child inputs of the specified type to be disabled for.
         * @param {String} type A single or comma separated string of the type of inputs you would like enabled.
         * @example <caption>Enable all inputs and selects for #myForm.</caption>
         * tribe_ev.fn.enable_inputs( '#myForm', 'input, select' );
         */
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
        /**
         * @function tribe_ev.fn.get_base_url
         * @since 3.0
         * @desc tribe_ev.fn.get_base_url can be used on any events view to get the base_url for that view, even when on a category subset for that view.
         * @returns {String} Either an empty string or base url if data-baseurl is found on #tribe-events-header.
         * @example var base_url = tribe_ev.fn.get_base_url();
         */
        get_base_url: function () {
            var base_url = '';
            if ($('#tribe-events-header').length){
                base_url = $('#tribe-events-header').attr('data-baseurl');
            }
            return base_url;
        },
        /**
         * @function tribe_ev.fn.get_category
         * @since 3.0
         * @desc tribe_ev.fn.get_category can be used on any events view to get the category for that view.
         * @returns {String} Either an empty string or category slug if data-category is found on #tribe-events.
         * @example var cat = tribe_ev.fn.get_category();
         */
        get_category: function () {
            if (tribe_ev.fn.is_category())
                return $('#tribe-events').attr('data-category');
            else
                return '';
        },
        /**
         * @function tribe_ev.fn.get_day
         * @since 3.0
         * @desc tribe_ev.fn.get_day can be used to check the event bar for a day value that was set by the user when using the datepicker.
         * @returns {String|Number} Either an empty string or day number if #tribe-bar-date-day has a val() set by user interaction.
         * @example var day = tribe_ev.fn.get_day();
         */
        get_day: function () {
            var dp_day = '';
            if ($('#tribe-bar-date').length) {
                dp_day = $('#tribe-bar-date-day').val();
            }
            return dp_day;
        },
        /**
         * @function tribe_ev.fn.get_params
         * @since 3.0
         * @desc tribe_ev.fn.get_params returns the params of the current document.url.
         * @returns {String} any url params sans "?".
         * @example var params = tribe_ev.fn.get_params();
         */
        get_params: function () {
            return location.search.substr(1);
        },
        /**
         * @function tribe_ev.fn.get_url_param
         * @since 3.0
         * @desc tribe_ev.fn.get_url_param returns the value of a passed param name if set.
         * @param {String} name The name of the url param value you are after.
         * @returns {String|Null} the value of a parameter if set or null if not.
         * @example var param = tribe_ev.fn.get_url_param('category');
         */
        get_url_param: function (name) {
            return decodeURIComponent((new RegExp('[?|&]' + name + '=' + '([^&;]+?)(&|#|;|$)').exec(location.search) || [, ""])[1].replace(/\+/g, '%20')) || null;
        },
        /**
         * @function tribe_ev.fn.in_params
         * @since 3.0
         * @desc tribe_ev.fn.in_params returns the value of a passed param name if set.
         * @param {String} params The paramter string you would like to search for a term.
         * @param {String} term The name of the url param value you are checking for.
         * @returns {Number} Returns index if term is present in params, or -1 if not found.
         * @example
         * if (tribe_ev.fn.in_params(tribe_ev.data.params, "tabby") >= 0)){
         *     // tabby is in params
         * } else {
         *     // tabby is not in params
         * }
         */
        in_params: function (params, term) {
            return params.toLowerCase().indexOf(term);
        },
        /**
         * @function tribe_ev.fn.is_category
         * @since 3.0
         * @desc tribe_ev.fn.is_category test for whether the view is a category subpage.
         * @returns {Boolean} Returns true if category page, false if not.
         * @example if (tribe_ev.fn.is_category()){ true } else { false }
         */
        is_category: function () {
            return ($('#tribe-events').length && $('#tribe-events').tribe_has_attr('data-category') && $('#tribe-events').attr('data-category') !== '') ? true : false;
        },
        /**
         * @function tribe_ev.fn.parse_string
         * @since 3.0
         * @desc tribe_ev.fn.parse_string converts a string to an object.
         * @param {String} string The string to be converted.
         * @returns {Object} Returns mapped object.
         * @example if (tribe_ev.fn.is_category()){ true } else { false }
         */
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
