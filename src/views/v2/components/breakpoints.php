<?php
/**
 * View: Breakpoints
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/breakpoints.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 5.0.0
 *
 * @var bool $is_initial_load Boolean on whether view is being loaded for the first time.
 */

if ( ! $is_initial_load ) {
	return;
}
?>
<script class="tribe-events-breakpoints">
	if ( 'undefined' !== typeof window.tribe ) {
		var scripts = document.getElementsByTagName( 'script' );
		window.tribe.events.views.breakpoints.setup( scripts[ scripts.length - 1 ] );
	}
</script>
