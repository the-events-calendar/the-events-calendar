<?php
if ( ! $this->get_view()->get_context()->doing_php_initial_state() ) {
	return;
}
?>
<script class="tribe-events-views-breakpoints">
	var scripts = document.getElementsByTagName( 'script' );
	window.tribe.events.views.breakpoints.setup( scripts[ scripts.length - 1 ] );
</script>
