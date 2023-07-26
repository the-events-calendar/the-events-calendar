<?php return '<div  class="tribe-compatibility-container" >
	<div
		 class="tribe-common tribe-events tribe-events-widget" 		data-js="tribe-events-view"
		data-view-rest-nonce="1122334455"
		data-view-rest-url="https://rest.tri.be/"
		data-view-manage-url=""
							data-view-breakpoint-pointer="aabbccddee"
			>
		<div class="tribe-events-widget-events-list">

			{}
			<script data-js="tribe-events-view-data" type="application/json">
	{"slug":"reflector","prev_url":"","next_url":"","view_class":"Tribe\\\\Events\\\\Views\\\\V2\\\\Views\\\\Reflector_View","view_slug":"reflector","view_label":"View","events":[8,9],"is_initial_load":true,"rest_url":"https:\\/\\/rest.tri.be\\/","rest_nonce":"1122334455","should_manage_url":false,"container_classes":["tribe-common","tribe-events","tribe-events-widget"],"compatibility_classes":["tribe-compatibility-container"],"container_data":[],"breakpoint_pointer":"aabbccddee","messages":[],"hide_if_no_upcoming_events":false,"view_more_link":"https:\\/\\/test.tri.be\\/","view_more_text":"View More","view_more_title":"View more events.","widget_title":"Upcoming Events"}</script>

							<header class="tribe-events-widget-events-list__header">
					<h2 class="tribe-events-widget-events-list__header-title tribe-common-h6 tribe-common-h--alt">
						Upcoming Events					</h2>
				</header>
			
			
				<div class="tribe-events-widget-events-list__events">
											<div  class="tribe-common-g-row tribe-events-widget-events-list__event-row" >

	<div class="tribe-events-widget-events-list__event-date-tag tribe-common-g-col">
	<time class="tribe-events-widget-events-list__event-date-tag-datetime" datetime="2019-06-20">
		<span class="tribe-events-widget-events-list__event-date-tag-month">
			Jun		</span>
		<span class="tribe-events-widget-events-list__event-date-tag-daynum tribe-common-h2 tribe-common-h4--min-medium">
			20		</span>
	</time>
</div>

	<div class="tribe-events-widget-events-list__event-wrapper tribe-common-g-col">
		<article  class="tribe-events-widget-events-list__event post-8 tribe_events type-tribe_events status-publish hentry" >
			<div class="tribe-events-widget-events-list__event-details">

				<header class="tribe-events-widget-events-list__event-header">
					<div class="tribe-events-widget-events-list__event-datetime-wrapper tribe-common-b2 tribe-common-b3--min-medium">
		<time class="tribe-events-widget-events-list__event-datetime" datetime="2019-06-20">
		<span class="tribe-event-date-start">1:04 pm</span> - <span class="tribe-event-time">5:04 pm</span>	</time>
	</div>
					<h3 class="tribe-events-widget-events-list__event-title tribe-common-h7">
	<a
		href="http://test.tri.be/?tribe_events=single-event-1"
		title="Single Event 1"
		rel="bookmark"
		class="tribe-events-widget-events-list__event-title-link tribe-common-anchor-thin"
	>
		Single Event 1	</a>
</h3>
				</header>

				
			</div>
		</article>
	</div>

</div>
											<div  class="tribe-common-g-row tribe-events-widget-events-list__event-row" >

	<div class="tribe-events-widget-events-list__event-date-tag tribe-common-g-col">
	<time class="tribe-events-widget-events-list__event-date-tag-datetime" datetime="2019-06-20">
		<span class="tribe-events-widget-events-list__event-date-tag-month">
			Jun		</span>
		<span class="tribe-events-widget-events-list__event-date-tag-daynum tribe-common-h2 tribe-common-h4--min-medium">
			20		</span>
	</time>
</div>

	<div class="tribe-events-widget-events-list__event-wrapper tribe-common-g-col">
		<article  class="tribe-events-widget-events-list__event post-9 tribe_events type-tribe_events status-publish hentry" >
			<div class="tribe-events-widget-events-list__event-details">

				<header class="tribe-events-widget-events-list__event-header">
					<div class="tribe-events-widget-events-list__event-datetime-wrapper tribe-common-b2 tribe-common-b3--min-medium">
		<time class="tribe-events-widget-events-list__event-datetime" datetime="2019-06-20">
		<span class="tribe-event-date-start">1:04 pm</span> - <span class="tribe-event-time">5:04 pm</span>	</time>
	</div>
					<h3 class="tribe-events-widget-events-list__event-title tribe-common-h7">
	<a
		href="http://test.tri.be/?tribe_events=single-event-2"
		title="Single Event 2"
		rel="bookmark"
		class="tribe-events-widget-events-list__event-title-link tribe-common-anchor-thin"
	>
		Single Event 2	</a>
</h3>
				</header>

				
			</div>
		</article>
	</div>

</div>
									</div>

				<div class="tribe-events-widget-events-list__view-more tribe-common-b1 tribe-common-b2--min-medium">
	<a
		href="https://test.tri.be/"
		class="tribe-events-widget-events-list__view-more-link tribe-common-anchor-thin"
		title="View more events."
	>
		View More	</a>
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

			var container = document.querySelectorAll( \'[data-view-breakpoint-pointer="aabbccddee"]\' );
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
';
