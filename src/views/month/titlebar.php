<?php
/**
 * Month View Title Template
 * The title template for the month view of events.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/month/titlebar.php
 *
 * @package TribeEventsCalendar
 *
 */
?>

<div class="tribe-events-titlebar">

	<!-- Month Title -->
	<?php do_action( 'tribe_events_before_the_title' ); ?>
	<h1 class="tribe-events-page-title"><?php echo tribe_get_events_title() ?></h1>
	<?php do_action( 'tribe_events_after_the_title' ); ?>

</div>
