<?php
/**
 * View: Breakpoints
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/breakpoints.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version TBD
 *
 * @var bool   $is_initial_load    Boolean on whether view is being loaded for the first time.
 * @var string $breakpoint_pointer String we use as pointer to the current view we are setting up with breakpoints.
 */

if ( ! $is_initial_load ) {
	return;
}
?>
<script class="tribe-events-breakpoints">
	window.addEventListener('DOMContentLoaded', function(){
		if ( 'undefined' === typeof window.tribe ) {
			return;
		}

		if ( 'undefined' === typeof window.tribe.events ) {
			return;
		}

		if ( 'undefined' === typeof window.tribe.events.views ) {
			return;
		}

		if ( 'undefined' === typeof window.tribe.events.views.breakpoints ) {
			return;
		}

		if ( 'function' !== typeof( window.tribe.events.views.breakpoints.setup ) ) {
			return;
		}

		var container = document.querySelectorAll( '[data-view-breakpoint-pointer="<?php echo esc_js( $breakpoint_pointer ); ?>"]' );
		if ( ! container ) {
			return;
		}
		window.tribe.events.views.breakpoints.setup( container );
	});
</script>
