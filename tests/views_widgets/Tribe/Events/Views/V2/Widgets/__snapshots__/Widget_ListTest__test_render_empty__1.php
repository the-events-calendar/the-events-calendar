<?php return '<div
	 class="tribe-common tribe-events tribe-events-view tribe-events-view--widget-events-list tribe-events-widget" 	data-js="tribe-events-view"
	data-view-rest-nonce="2ab7cc6b39"
	data-view-rest-url="http://test.tri.be/index.php?rest_route=/tribe/views/v2/html"
	data-view-manage-url="1"
				data-view-breakpoint-pointer="random-id"
	>
	<div class="tribe-events-widget-events-list">

		
		<script data-js="tribe-events-view-data" type="application/json">
	{"slug":"widget-events-list","prev_url":"","next_url":"","view_class":"Tribe\\\\Events\\\\Views\\\\V2\\\\Views\\\\Widgets\\\\Widget_List_View","view_slug":"widget-events-list","view_label":"Widget-events-list","title":"The Events Calendar Tests","events":[],"url":"http:\\/\\/test.tri.be\\/?post_type=tribe_events&eventDisplay=widget-events-list","url_event_date":"2019-01-01","bar":{"keyword":"","date":"2019-01-01 09:00:00"},"today":"2019-01-01 09:00:00","now":"2019-01-01 09:00:00","rest_url":"http:\\/\\/test.tri.be\\/index.php?rest_route=\\/tribe\\/views\\/v2\\/html","rest_method":"POST","rest_nonce":"2ab7cc6b39","should_manage_url":true,"today_url":"http:\\/\\/test.tri.be\\/?post_type=tribe_events&eventDisplay=widget-events-list","prev_label":"","next_label":"","date_formats":{"compact":"n\\/j\\/Y","month_and_year_compact":"n\\/j\\/Y","month_and_year":"F Y","time_range_separator":" - ","date_time_separator":" @ "},"messages":{"notice":["There are no upcoming events."]},"start_of_week":"1","breadcrumbs":[],"before_events":"<div id=\\"tribe-events\\" class=\\"tribe-no-js\\" data-live_ajax=\\"1\\" data-datepicker_format=\\"1\\" data-category=\\"\\" data-featured=\\"\\">","after_events":"<\\/div><!-- #tribe-events -->\\n<!--\\nThis calendar is powered by The Events Calendar.\\nhttp:\\/\\/m.tri.be\\/18wn\\n-->\\n","display_events_bar":false,"disable_event_search":false,"live_refresh":true,"ical":{"display_link":true,"link":{"url":"http:\\/\\/test.tri.be\\/events\\/?ical=1","text":"Export Events","title":"Use this to share calendar data with Google Calendar, Apple iCal and other compatible apps"}},"container_classes":["tribe-common","tribe-events","tribe-events-view","tribe-events-view--widget-events-list","tribe-events-widget"],"container_data":[],"is_past":false,"breakpoints":{"xsmall":500,"medium":768,"full":960},"breakpoint_pointer":"random-id","is_initial_load":true,"public_views":{"list":{"view_class":"Tribe\\\\Events\\\\Views\\\\V2\\\\Views\\\\List_View","view_url":"http:\\/\\/test.tri.be\\/events\\/list\\/?tribe-bar-date=2019-01-01","view_label":"List"},"month":{"view_class":"Tribe\\\\Events\\\\Views\\\\V2\\\\Views\\\\Month_View","view_url":"http:\\/\\/test.tri.be\\/events\\/month\\/2019-01\\/","view_label":"Month"},"day":{"view_class":"Tribe\\\\Events\\\\Views\\\\V2\\\\Views\\\\Day_View","view_url":"http:\\/\\/test.tri.be\\/events\\/2019-01-01\\/","view_label":"Day"}},"show_latest_past":false,"view_more_link":"http:\\/\\/test.tri.be\\/events\\/","widget_title":null,"hide_if_no_upcoming_events":null,"jsonld_enable":0,"display":[],"_context":{"slug":"widget-events-list"}}</script>

		<header class="tribe-events-widget-events-list__header">
			<h3 class="tribe-events-widget-events-list__header-title tribe-common-h6 tribe-common-h--alt">
							</h3>
		</header>

		
			<div  class="tribe-events-header__messages tribe-events-c-messages tribe-common-b2" >
			<div class="tribe-events-c-messages__message tribe-events-c-messages__message--notice" role="alert">
			<ul class="tribe-events-c-messages__message-list">
									<li class="tribe-events-c-messages__message-list-item">
						There are no upcoming events.					</li>
							</ul>
		</div>
	</div>

			</div>
</div>

<script class="tribe-events-breakpoints">
	(function(){
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

		if ( \'function\' !== typeof( window.tribe.events.views.breakpoints.setup ) ) {
			return;
		}

		var container = document.querySelectorAll( \'[data-view-breakpoint-pointer="random-id"]\' );
		if ( ! container ) {
			return;
		}

		window.tribe.events.views.breakpoints.setup( container );
	})();
</script>
';
