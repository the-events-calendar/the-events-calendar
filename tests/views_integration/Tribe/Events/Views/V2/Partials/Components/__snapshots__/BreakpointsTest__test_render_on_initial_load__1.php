<?php return '<script class="tribe-events-breakpoints">
	if ( \'undefined\' !== typeof window.tribe ) {
		var scripts = document.getElementsByTagName( \'script\' );
		window.tribe.events.views.breakpoints.setup( scripts[ scripts.length - 1 ] );
	}
</script>
';
