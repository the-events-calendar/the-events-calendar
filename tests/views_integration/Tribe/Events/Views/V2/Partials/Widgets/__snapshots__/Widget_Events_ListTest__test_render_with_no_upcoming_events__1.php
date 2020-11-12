<?php return '<div
	 class="tribe-common tribe-events tribe-events-widget" 	data-js="tribe-events-view"
	data-view-rest-nonce="1122334455"
	data-view-rest-url="https://rest.tri.be/"
	data-view-manage-url=""
				data-view-breakpoint-pointer="aabbccddee"
	>
	<div class="tribe-events-widget-events-list">

		{}
		<script data-js="tribe-events-view-data" type="application/json">
	{"slug":"reflector","prev_url":"","next_url":"","view_class":"Tribe\\\\Events\\\\Views\\\\V2\\\\Views\\\\Reflector_View","view_slug":"reflector","view_label":"Reflector","events":[],"is_initial_load":true,"rest_url":"https:\\/\\/rest.tri.be\\/","rest_nonce":"1122334455","should_manage_url":false,"container_classes":["tribe-common","tribe-events","tribe-events-widget"],"container_data":[],"breakpoint_pointer":"aabbccddee","messages":{"notice":["There are no upcoming events."]},"hide_if_no_upcoming_events":false,"view_more_link":"https:\\/\\/test.tri.be\\/","widget_title":"Upcoming Events"}</script>

		<header class="tribe-events-widget-events-list__header">
			<h3 class="tribe-events-widget-events-list__header-title tribe-common-h6">
				Upcoming Events			</h3>
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

		var container = document.querySelectorAll( \'[data-view-breakpoint-pointer="aabbccddee"]\' );
		if ( ! container ) {
			return;
		}

		window.tribe.events.views.breakpoints.setup( container );
	})();
</script>
';
