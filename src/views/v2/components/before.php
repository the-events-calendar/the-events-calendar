<?php
/**
 * Component: Before Events
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/before.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 4.9.11
 *
 * @var string $before_events HTML stored on the Advanced settings to be printed before the Events.
 */

if ( empty( $before_events ) ) {
	return;
}
?>
<div class="tribe-events-before-html">
	<?php echo $before_events; ?>
</div>
