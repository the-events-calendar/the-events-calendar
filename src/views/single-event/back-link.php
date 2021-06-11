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
?>
<p class="tribe-events-back">
	<a href="<?php echo esc_url( tribe_get_events_link() ); ?>">
		&laquo; <?php printf( $label, $events_label_plural ); ?>
	</a>
</p>
