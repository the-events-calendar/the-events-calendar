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
	{"slug":"reflector","prev_url":"","next_url":"","view_class":"Tribe\\\\Events\\\\Views\\\\V2\\\\Views\\\\Reflector_View","view_slug":"reflector","view_label":"View","events":[],"is_initial_load":true,"rest_url":"https:\\/\\/rest.tri.be\\/","rest_nonce":"1122334455","should_manage_url":false,"container_classes":["tribe-common","tribe-events","tribe-events-widget"],"compatibility_classes":["tribe-compatibility-container"],"container_data":[],"breakpoint_pointer":"aabbccddee","messages":{"notice":["There are no upcoming events."]},"hide_if_no_upcoming_events":false,"view_more_link":"https:\\/\\/test.tri.be\\/","view_more_text":"View More","view_more_title":"View more events.","widget_title":"Upcoming Events"}</script>

							<header class="tribe-events-widget-events-list__header">
					<h2 class="tribe-events-widget-events-list__header-title tribe-common-h6 tribe-common-h--alt">
						Upcoming Events					</h2>
				</header>
			
			
				<div  class="tribe-events-header__messages tribe-events-c-messages tribe-common-b2"  >
			<div class="tribe-events-c-messages__message tribe-events-c-messages__message--notice" role="alert">
			<svg  class="tribe-common-c-svgicon tribe-common-c-svgicon--messages-not-found tribe-events-c-messages__message-icon-svg"  viewBox="0 0 21 23" xmlns="http://www.w3.org/2000/svg"><g fill-rule="evenodd"><path d="M.5 2.5h20v20H.5z"/><path stroke-linecap="round" d="M7.583 11.583l5.834 5.834m0-5.834l-5.834 5.834" class="tribe-common-c-svgicon__svg-stroke"/><path stroke-linecap="round" d="M4.5.5v4m12-4v4"/><path stroke-linecap="square" d="M.5 7.5h20"/></g></svg>
			<ul class="tribe-events-c-messages__message-list">
									<li
						class="tribe-events-c-messages__message-list-item"
						 data-key="0" 					>
					There are no upcoming events.					</li>
							</ul>
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
