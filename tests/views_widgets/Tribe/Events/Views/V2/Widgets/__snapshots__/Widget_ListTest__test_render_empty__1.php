<?php return '<div  class="tribe-compatibility-container" >
	<div
		 class="tribe-common tribe-events tribe-events-view tribe-events-view--widget-events-list tribe-events-widget" 		data-js="tribe-events-view"
		data-view-rest-url="http://test.tri.be/index.php?rest_route=/tribe/views/v2/html"
		data-view-manage-url="1"
							data-view-breakpoint-pointer="random-id"
			>
		<div class="tribe-events-widget-events-list">

			
			<script data-js="tribe-events-view-data" type="application/json">
	{"slug":"widget-events-list","prev_url":"","next_url":"","view_class":"Tribe\\\\Events\\\\Views\\\\V2\\\\Views\\\\Widgets\\\\Widget_List_View","view_slug":"widget-events-list","view_label":"View","title":"The Events Calendar Tests","events":[],"url":"http:\\/\\/test.tri.be\\/?post_type=tribe_events&eventDisplay=widget-events-list","url_event_date":"2019-01-01","bar":{"keyword":"","date":"2019-01-01 09:00:00"},"today":"2019-01-01 09:00:00","now":"2019-01-01 09:00:00","home_url":"http:\\/\\/test.tri.be\\/","rest_url":"http:\\/\\/test.tri.be\\/index.php?rest_route=\\/tribe\\/views\\/v2\\/html","rest_method":"GET","rest_nonce":"","should_manage_url":true,"today_url":"http:\\/\\/test.tri.be\\/?post_type=tribe_events&eventDisplay=widget-events-list","today_title":"Click to select today\'s date","today_label":"Today","prev_label":"","next_label":"","date_formats":{"compact":"n\\/j\\/Y","month_and_year_compact":"n\\/j\\/Y","month_and_year":"F Y","time_range_separator":" - ","date_time_separator":" @ "},"messages":{"notice":["There are no upcoming events."]},"start_of_week":"1","header_title":"","header_title_element":"h1","content_title":"","breadcrumbs":[],"before_events":"","after_events":"\\n<!--\\nThis calendar is powered by The Events Calendar.\\nhttp:\\/\\/evnt.is\\/18wn\\n-->\\n","display_events_bar":false,"disable_event_search":false,"live_refresh":true,"ical":{"display_link":true,"link":{"url":"http:\\/\\/test.tri.be\\/?post_type=tribe_events&#038;eventDisplay=widget-events-list&#038;ical=1","text":"Export Events","title":"Use this to share calendar data with Google Calendar, Apple iCal and other compatible apps"}},"container_classes":["tribe-common","tribe-events","tribe-events-view","tribe-events-view--widget-events-list","tribe-events-widget"],"container_data":[],"is_past":false,"breakpoints":{"xsmall":500,"medium":768,"full":960},"breakpoint_pointer":"random-id","is_initial_load":true,"public_views":{"list":{"view_class":"Tribe\\\\Events\\\\Views\\\\V2\\\\Views\\\\List_View","view_url":"http:\\/\\/test.tri.be\\/events\\/list\\/?tribe-bar-date=2019-01-01","view_label":"List","aria_label":"Display Events in List View"},"month":{"view_class":"Tribe\\\\Events\\\\Views\\\\V2\\\\Views\\\\Month_View","view_url":"http:\\/\\/test.tri.be\\/events\\/month\\/2019-01\\/","view_label":"Month","aria_label":"Display Events in Month View"},"day":{"view_class":"Tribe\\\\Events\\\\Views\\\\V2\\\\Views\\\\Day_View","view_url":"http:\\/\\/test.tri.be\\/events\\/2019-01-01\\/","view_label":"Day","aria_label":"Display Events in Day View"}},"show_latest_past":false,"past":false,"jsonld_enable":false,"compatibility_classes":["tribe-compatibility-container"],"view_more_text":"View Calendar","view_more_title":"View more events.","view_more_link":"http:\\/\\/test.tri.be\\/events\\/","widget_title":null,"hide_if_no_upcoming_events":null,"display":[],"subscribe_links":{"gcal":{"label":"Google Calendar","single_label":"Add to Google Calendar","visible":true,"block_slug":"hasGoogleCalendar"},"ical":{"label":"iCalendar","single_label":"Add to iCalendar","visible":true,"block_slug":"hasiCal"},"outlook-365":{"label":"Outlook 365","single_label":"Outlook 365","visible":true,"block_slug":"hasOutlook365"},"outlook-live":{"label":"Outlook Live","single_label":"Outlook Live","visible":true,"block_slug":"hasOutlookLive"},"ics":{"label":"Export .ics file","single_label":"Export .ics file","visible":true,"block_slug":null},"outlook-ics":{"label":"Export Outlook .ics file","single_label":"Export Outlook .ics file","visible":true,"block_slug":null}},"_context":{"slug":"widget-events-list"}}</script>

			
			
				<div  class="tribe-events-header__messages tribe-events-c-messages tribe-common-b2"  >
			<div class="tribe-events-c-messages__message tribe-events-c-messages__message--notice" tabindex="0" role="alert" aria-live="assertive">
			<svg
	 class="tribe-common-c-svgicon tribe-common-c-svgicon--messages-not-found tribe-events-c-messages__message-icon-svg" 	aria-hidden="true"
	viewBox="0 0 21 23"
	xmlns="http://www.w3.org/2000/svg"
>
	<g fill-rule="evenodd">
		<path d="M.5 2.5h20v20H.5z"/>
		<path stroke-linecap="round" d="M7.583 11.583l5.834 5.834m0-5.834l-5.834 5.834" class="tribe-common-c-svgicon__svg-stroke"/>
		<path stroke-linecap="round" d="M4.5.5v4m12-4v4"/>
		<path stroke-linecap="square" d="M.5 7.5h20"/>
	</g>
</svg>
<span class="tribe-common-a11y-visual-hide">
	Notice</span>
							<div
					 data-key="0" >
					There are no upcoming events.				</div>
					</div>
	</div>

					</div>
	</div>
</div>
<script class="tribe-events-breakpoints">
	( function () {
		var completed = false;

		function initBreakpoints() {
			if ( completed ) {
				// This was fired already and completed no need to attach to the event listener.
				document.removeEventListener( \'DOMContentLoaded\', initBreakpoints );
				return;
			}

			if ( \'undefined\' === typeof window.tribe ) {
				return;
			}

			if ( \'undefined\' === typeof window.tribe.events ) {
				return;
			}

			if ( \'undefined\' === typeof window.tribe.events.views ) {
				return;
			}

			if ( \'undefined\' === typeof window.tribe.events.views.breakpoints ) {
				return;
			}

			if ( \'function\' !== typeof (window.tribe.events.views.breakpoints.setup) ) {
				return;
			}

			var container = document.querySelectorAll( \'[data-view-breakpoint-pointer="random-id"]\' );
			if ( ! container ) {
				return;
			}

			window.tribe.events.views.breakpoints.setup( container );
			completed = true;
			// This was fired already and completed no need to attach to the event listener.
			document.removeEventListener( \'DOMContentLoaded\', initBreakpoints );
		}

		// Try to init the breakpoints right away.
		initBreakpoints();
		document.addEventListener( \'DOMContentLoaded\', initBreakpoints );
	})();
</script>
<script data-js=\'tribe-events-view-nonce-data\' type=\'application/json\'>{"tvn1":"2ab7cc6b39","tvn2":""}</script>';
