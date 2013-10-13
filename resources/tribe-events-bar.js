/**
 * @file Sets up the event bar javascript.
 * This file should load after tribe events core and pro js and before any events view javascript.
 * @version 3.0
 */

/**
 * @global
 * @desc This global is used in various functions throughout the plugin to determine if the view is being changed. Its value will be set to 'change_view' when true.
 */

var tribe_events_bar_action;

(function (window, document, $, td, te, tf, ts, tt, dbug) {

	/*
	 * $    = jQuery
	 * td   = tribe_ev.data
	 * te   = tribe_ev.events
	 * tf   = tribe_ev.fn
	 * ts   = tribe_ev.state
	 * tt   = tribe_ev.tests
	 * dbug = tribe_debug
	 */

	if(dbug){
		if(!$().bootstrapDatepicker){
			debug.warn('TEC Debug: vendor bootstrapDatepicker was not loaded before its dependant file tribe-events-bar.js');
		}
		if(!$().placeholder){
			debug.warn('TEC Debug: vendor placeholder was not loaded before its dependant file tribe-events-bar.js');
		}
	}

	$(document).ready(function () {
		var $tribebar = $('#tribe-bar-form'),
			$tribedate = $('#tribe-bar-date'),
			$tribebarselect = $('select[name=tribe-bar-view]');

		/**
		 * @function eventsBarWidth
		 * @since 3.0
		 * @desc eventsBarWidth applies responsive css classes to the bar to adjust its layout for smaller screens.
		 * @param {jQuery} $tribebar The event bar jquery object.
		 */
		function eventsBarWidth($tribebar) {
			if ( $tribebar.parents('.tribe-bar-disabled').length )
				return;

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
			if (!$('.events-gridview').length || tt.map_view()) {

				dbug && debug.info('TEC Debug: bootstrapDatepicker was just initialized in "tribe-events-bar.js" on:', $tribedate);

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
			if ($tribedate.val() === '' && $('.datepicker.dropdown-menu').is(':hidden') && tt.live_ajax() && tt.pushstate) {
				ts.date = td.cur_date;
				td.cur_url = td.base_url;
				$(te).trigger('tribe_ev_runAjax');
			}
		});

		// Add some classes
		if ($('.tribe-bar-settings').length) {
			$('#tribe-events-bar').addClass('tribe-has-settings');
		}
		if ($('#tribe-events-bar .hasDatepicker').length) {
			$('#tribe-events-bar').addClass('tribe-has-datepicker');
		}

		// Implement views links
		function format(view) {
			return '<span class="tribe-icon-' + $.trim(view.text.toLowerCase()) + '">' + view.text + '</span>';
		}
		// Implement placeholder
		$('input[name*="tribe-bar-"]').placeholder();

		// Create list
		$('<ul class="tribe-bar-views-list" />').insertAfter( $tribebarselect );

		var $tribebarviews = $('.tribe-bar-views-list');

		// Create list from select options
		$tribebarselect.find('option').each(function(i){
			var $view = $(this);
			// build list items and append them
			var unique_c = 'tribe-bar-views-option-' + $view.data('view');
			$('<li></li>', {
				'class': 'tribe-bar-views-option ' + unique_c,
				'data-tribe-bar-order': i,
				'data-view': $view.data('view')
			}).html([
                '   <a href="#">',
                '   <span class="tribe-icon-' + $.trim($view.text().toLowerCase()) + '">' + $view.text() + '</span>',
                '</a>'].join("")
			).appendTo( '.tribe-bar-views-list' );

		}); 
		
		//find the current view and select it in the bar
		var currentview = $tribebarselect.find(':selected').data('view'),
			$currentli = $tribebarviews.find('li[data-view='+ currentview +']');

		$currentli.prependTo($tribebarviews).addClass('tribe-bar-active');

		// toggle the views dropdown	
		$tribebar.on('click', '#tribe-bar-views', function (e) {
			e.stopPropagation();
			var $this = $(this);
			$this.toggleClass('tribe-bar-views-open');
		});

		// change views
		$tribebar.on('click', '.tribe-bar-views-option', function(e) {
			e.preventDefault();
			var $this = $(this);
			if ( !$this.is('.tribe-bar-active') ) {

				var target = $this.data('view');

				ts.cur_url = $('option[data-view='+ target +']').val();
				ts.view_target = $('select[name=tribe-bar-view] option[value="' + ts.cur_url + '"]').data('view');
				tribe_events_bar_action = 'change_view';
				tribe_events_bar_change_view();

			} 
		});

		$tribebar.on('click', '#tribe-bar-collapse-toggle', function () {
			$(this).toggleClass('tribe-bar-filters-open');
			$('.tribe-bar-filters').slideToggle('fast');
		});

		// Wrap date inputs with a parent container
		$('label[for="tribe-bar-date"], input[name="tribe-bar-date"]').wrapAll('<div id="tribe-bar-dates" />');

		// Add our date bits outside of our filter container
		$('#tribe-bar-filters').before($('#tribe-bar-dates'));

		$(te).on("tribe_ev_serializeBar", function () {
			$('form#tribe-bar-form input, #tribeHideRecurrence').each(function () {
				var $this = $(this);
				if ($this.is('#tribe-bar-date')) {
					if ($this.val().length) {
						ts.params[$this.attr('name')] = $this.val();
						ts.url_params[$this.attr('name')] = $this.val();
					} else if($this.is('.placeholder') && $this.is('.bd-updated')){
						ts.url_params[$this.attr('name')] = $this.attr('data-oldDate');
					} else {
						ts.date = td.cur_date;
					}
				}

				if ($this.val().length && !$this.hasClass('tribe-no-param') && !$this.is('#tribe-bar-date')) {
					if ($this.is(':checkbox')) {
						if ($this.is(':checked')) {
							ts.params[$this.attr('name')] = $this.val();
							if (ts.view !== 'map')
								ts.url_params[$this.attr('name')] = $this.val();
							if (ts.view === 'month' || ts.view === 'day' || ts.view === 'week' || ts.recurrence)
								ts.pushcount++;
						}
					} else {
						ts.params[$this.attr('name')] = $this.val();
						if (ts.view !== 'map')
							ts.url_params[$this.attr('name')] = $this.val();
						if (ts.view === 'month' || ts.view === 'day' || ts.view === 'week')
							ts.pushcount++;
					}
				}
			});
		});

		/**
		 * @function tribe_events_bar_change_view
		 * @since 3.0
		 * @desc tribe_events_bar_change_view handles switching views and collecting any params from the events bar. It also fires 2 custom actions that can be hooked into: 'tribe_ev_preCollectBarParams' and 'tribe_ev_postCollectBarParams'.
		 */

		function tribe_events_bar_change_view() {

			tribe_events_bar_action = 'change_view';

			if (ts.view === 'month' && $tribedate.length) {
				var dp_date = $tribedate.val();
				if (dp_date.length === 7) {
					$tribedate.val(dp_date + tf.get_day());

				}

			}

			ts.url_params = {};

			$(te).trigger('tribe_ev_preCollectBarParams');

			$('#tribe-bar-form input').each(function () {
				var $this = $(this);
				if ($this.val().length && !$this.hasClass('tribe-no-param')) {
					if ($this.is(':checkbox')) {
						if ($this.is(':checked')) {
							ts.url_params[$this.attr('name')] = $this.val();
						}
					} else {
						ts.url_params[$this.attr('name')] = $this.val();
					}
				}
			});

			ts.url_params = $.param(ts.url_params);

			$(te).trigger('tribe_ev_postCollectBarParams');

			if (ts.url_params.length)
				ts.cur_url += tt.starting_delim() + ts.url_params;

			window.location.href = ts.cur_url;
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
			$('#tribe-bar-views').removeClass('tribe-bar-views-open');
			if ($tribeDropToggle.hasClass('open')) {
				$tribeDropToggle.removeClass('open');
				$tribeDropToggleEl.hide();
			}
		});

		$tribeDropToggleEl.click(function (e) {
			e.stopPropagation();
		});
		dbug && debug.info('TEC Debug: tribe-events-bar.js successfully loaded');
	});

})(window, document, jQuery, tribe_ev.data, tribe_ev.events, tribe_ev.fn, tribe_ev.state, tribe_ev.tests, tribe_debug);

