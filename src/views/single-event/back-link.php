<?php
/**
 * Single Event Back link Template Part
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/single-event/back-link.php
 *
 * See more documentation about our Blocks Editor templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 4.7
 *
 */
?>

<?php
$label = esc_html_x( 'All %s', '%s Events plural label', 'the-events-calendar' );
$events_label_plural = tribe_get_event_label_plural();

/* translators: %s: The plural label for events (e.g. "Events", "Workshops") */
$aria_label_back_to_events = sprintf( esc_html__( 'Back to all %s', 'the-events-calendar' ), $events_label_plural );
?>
<div class="tribe-events-back">
	<a href="<?php echo esc_url( tribe_get_events_link() ); ?>" aria-label="<?php echo esc_attr( $aria_label_back_to_events ); ?>">
		&laquo; <?php printf( $label, $events_label_plural ); ?>
	</a>
</div>
