<?php
/**
 * Component: After Events
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/after.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.11
 *
 * @var string $after_events HTML stored on the Advanced settings to be printed after the Events.
 */

if ( empty( $after_events ) ) {
	return;
}
?>
<div class="tribe-events-after-html">
	<?php echo $after_events; ?>
</div>

