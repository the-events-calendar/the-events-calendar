<?php return '<script class="tribe-events-breakpoints">
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
