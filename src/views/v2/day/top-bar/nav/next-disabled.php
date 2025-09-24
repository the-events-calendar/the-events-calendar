<?php
/**
 * View: Top Bar Navigation Next Disabled Template
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/day/top-bar/nav/next-disabled.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 5.3.0
 *
 */

$events_label_plural = tribe_get_event_label_plural();

// Translators: %s: Events label plural.
$next_day_label = sprintf( __( 'Next day\'s %s', 'the-events-calendar' ), $events_label_plural );
?>
<li class="tribe-events-c-top-bar__nav-list-item">
	<button
		class="tribe-common-c-btn-icon tribe-common-c-btn-icon--caret-right tribe-events-c-top-bar__nav-link tribe-events-c-top-bar__nav-link--next"
		aria-label="<?php echo esc_attr( $next_day_label ); ?>"
		disabled
	>
		<?php $this->template( 'components/icons/caret-right', [ 'classes' => [ 'tribe-common-c-btn-icon__icon-svg', 'tribe-events-c-top-bar__nav-link-icon-svg' ] ] ); ?>
	</button>
</li>
