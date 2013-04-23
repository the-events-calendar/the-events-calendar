// Check for width of events bar so can kick in the view filter select input when appropriate
var tribe_events_bar_action;

jQuery(document).ready(function ($) {

    var $tribebar = $('#tribe-bar-form');
    var $tribedate = $('#tribe-bar-date');

    // Check width of events bar
    function eventsBarWidth($tribebar) {

        var tribeBarWidth = $tribebar.width();

        if (tribeBarWidth > 800) {
            $tribebar.removeClass('tribe-bar-mini tribe-bar-collapse').addClass('tribe-bar-full');
        } else {
            $tribebar.removeClass('tribe-bar-full').addClass('tribe-bar-mini');
        }
        if (tribeBarWidth < 670) {
            $tribebar.removeClass('tribe-bar-mini').addClass('tribe-bar-collapse');
        } else {
            $tribebar.removeClass('tribe-bar-collapse');
        }
    }

    eventsBarWidth($tribebar);

    $tribebar.resize(function () {
        eventsBarWidth($tribebar);
    });

    if (!$('.tribe-events-week-grid').length) {
        // includes temporary check for map view, as it currently has the grid view body class
        if (!$('.events-gridview').length || tribe_ev.tests.map_view()) {

            // setup list view datepicker
            var tribe_var_datepickerOpts = {
                format: 'yyyy-mm-dd',
                showAnim: 'fadeIn'
            };

            var tribeBarDate = $tribedate.bootstrapDatepicker(tribe_var_datepickerOpts).on('changeDate',function () {
                tribeBarDate.hide();
            }).data('datepicker');
        }
    }

    $tribedate.blur(function () {
        if ($tribedate.val() === '' && $('.datepicker.dropdown-menu').is(':hidden') && tribe_ev.tests.live_ajax() && tribe_ev.tests.pushstate) {
            tribe_ev.state.date = tribe_ev.data.cur_date;
            tribe_ev.data.cur_url = tribe_ev.data.base_url;
            $(tribe_ev.events).trigger('tribe_ev_runAjax');
        }
    });

    // Add some classes
    if ($('.tribe-bar-settings').length) {
        $('#tribe-events-bar').addClass('tribe-has-settings');
    }
    if ($('#tribe-events-bar .hasDatepicker').length) {
        $('#tribe-events-bar').addClass('tribe-has-datepicker');
    }

    // Implement placeholder
    $('input[name*="tribe-bar-"]').placeholder();

    // Implement select2
    function format(view) {
        return '<span class="tribe-icon-' + $.trim(view.text.toLowerCase()) + '">' + view.text + '</span>';
    }


    // trying to add a unique class to the select2 dropdown if the tribe bar is mini

    var select2_opts = {}

    if ($tribebar.is('.tribe-bar-mini')) {
        select2_opts = {
            placeholder: "Views",
            dropdownCssClass: "tribe-select2-results-views tribe-bar-mini-select2-results",
            minimumResultsForSearch: 9999,
            formatResult: format,
            formatSelection: format
        }
    } else {
        select2_opts = {
            placeholder: "Views",
            dropdownCssClass: "tribe-select2-results-views",
            minimumResultsForSearch: 9999,
            formatResult: format,
            formatSelection: format
        }
    }

    $('#tribe-bar-views .tribe-select2').select2(select2_opts);

    $tribebar.on('click', '#tribe-bar-views', function (e) {
        e.stopPropagation();
        var $this = $(this);
        $this.toggleClass('tribe-bar-views-open');
        if (!$this.is('.tribe-bar-views-open'))
            $('#tribe-bar-views .tribe-select2').select2('close');
        else
            $('#tribe-bar-views .tribe-select2').select2('open');
    });

    $tribebar.on('click', '#tribe-bar-collapse-toggle', function () {
        $(this).toggleClass('tribe-bar-filters-open');
        $('.tribe-bar-filters').slideToggle('fast');
    });

    $('body').on('click', function () {
        $('#tribe-bar-views').removeClass('tribe-bar-views-closed');
    });

    // Wrap date inputs with a parent container
    $('label[for="tribe-bar-date"], input[name="tribe-bar-date"]').wrapAll('<div id="tribe-bar-dates" />');

    // Add our date bits outside of our filter container
    $('#tribe-bar-filters').before($('#tribe-bar-dates'));


    // Implement our views bit
    $('select[name=tribe-bar-view]').change(function () {
        tribe_ev.state.cur_url = $(this).val();
        tribe_ev.state.view_target = $('select[name=tribe-bar-view] option[value="' + tribe_ev.state.cur_url + '"]').attr('data-view');
        tribe_events_bar_action = 'change_view';
        tribe_events_bar_change_view();
    });

    $('a.tribe-bar-view').on('click', function (e) {
        e.preventDefault();
        var el = $(this);
        var name = el.attr('data-view');
        tribe_events_bar_change_view(el.attr('href'), name);

    });

    $(tribe_ev.events).on("tribe_ev_serializeBar", function () {
        $('form#tribe-bar-form input, #tribeHideRecurrence').each(function () {
            var $this = $(this);
            if($this.is('#tribe-bar-date')){
                if ($this.val().length){
                    tribe_ev.state.params[$this.attr('name')] = $this.val();
                } else {
                    tribe_ev.state.date = tribe_ev.data.cur_date;
                }
            }

            if ($this.val().length && !$this.hasClass('tribe-no-param') && !$this.is('#tribe-bar-date')) {
                if ($this.is(':checkbox')) {
                    if ($this.is(':checked')) {
                        tribe_ev.state.params[$this.attr('name')] = $this.val();
                        if (tribe_ev.state.view !== 'map')
                            tribe_ev.state.url_params[$this.attr('name')] = $this.val();
                        if (tribe_ev.state.view === 'month' || tribe_ev.state.view === 'day' || tribe_ev.state.view === 'week' || tribe_ev.state.recurrence)
                            tribe_ev.state.pushcount++;
                    }
                } else {
                    tribe_ev.state.params[$this.attr('name')] = $this.val();
                    if (tribe_ev.state.view !== 'map')
                        tribe_ev.state.url_params[$this.attr('name')] = $this.val();
                    if (tribe_ev.state.view === 'month' || tribe_ev.state.view === 'day' || tribe_ev.state.view === 'week')
                        tribe_ev.state.pushcount++;
                }
            }
        });
    });

    function tribe_events_bar_change_view() {

        tribe_events_bar_action = 'change_view';

        if (tribe_ev.state.view === 'month' && $tribedate.length) {
            var dp_date = $tribedate.val();
            if (dp_date.length === 7) {
                $tribedate.val(dp_date + tribe_ev.fn.get_day());
            }
        }

        tribe_ev.state.url_params = {};

        $(tribe_ev.events).trigger('tribe_ev_preCollectBarParams');

        $('#tribe-bar-form input').each(function () {
            var $this = $(this);
            if ($this.val().length && !$this.hasClass('tribe-no-param')) {
                if ($this.is(':checkbox')) {
                    if ($this.is(':checked')) {
                        tribe_ev.state.url_params[$this.attr('name')] = $this.val();
                    }
                } else {
                    tribe_ev.state.url_params[$this.attr('name')] = $this.val();
                }
            }
        });

        tribe_ev.state.url_params = $.param(tribe_ev.state.url_params);

        $(tribe_ev.events).trigger('tribe_ev_postCollectBarParams');

        if (tribe_ev.state.url_params.length)
            tribe_ev.state.cur_url += tribe_ev.tests.starting_delim() + tribe_ev.state.url_params;

        window.location.href = tribe_ev.state.cur_url;
    }

    // Implement simple toggle for filters at smaller size (and close if click outside of toggle area)
    var $tribeDropToggle = $('#tribe-events-bar [class^="tribe-bar-button-"]');
    var $tribeDropToggleEl = $tribeDropToggle.next('.tribe-bar-drop-content');

    $tribeDropToggle.click(function () {
        var $this = $(this);
        $this.toggleClass('open');
        $this.next('.tribe-bar-drop-content').toggle();
        return false
    });

    $(document).click(function () {
        if ($tribeDropToggle.hasClass('open')) {
            $tribeDropToggle.removeClass('open');
            $tribeDropToggleEl.hide();
        }
    });

    $tribeDropToggleEl.click(function (e) {
        e.stopPropagation();
    });

});

