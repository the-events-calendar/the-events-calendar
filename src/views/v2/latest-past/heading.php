<?php
/**
 * View: Latest Past Event Heading
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/latest-past/heading.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 5.1.0
 */

$label = sprintf( __( 'Latest Past %s', 'the-events-calendar' ), tribe_get_event_label_plural() );
?>
<h2 class="tribe-events-calendar-latest-past__heading tribe-common-h5 tribe-common-h3--min-medium">
	<?php echo esc_html( $label ); ?>
</h2>
