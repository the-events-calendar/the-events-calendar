<?php
/**
 * Events Pro Mini Calendar Widget
 * This is the template for the output of the mini calendar widget.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/widgets/mini-calendar-widget.php
 *
 * @package TribeEventsCalendarPro
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
} ?>

<?php $args = tribe_events_get_mini_calendar_args(); ?>

<!-- Removing this wrapper class will break the claendar javascript, please avoid and extend as needed -->

<div class="tribe-mini-calendar-wrapper">

	<!-- Grid -->
	<?php

	tribe_show_month( array(
		'tax_query' => $args['tax_query'],
		'eventDate' => $args['eventDate'],
	), 'pro/widgets/mini-calendar/grid' ); ?>

	<!-- List -->
	<?php
	if ( 0 < $args['count'] ) {
		tribe_get_template_part( 'pro/widgets/mini-calendar/list', null, array( 'venue' => true ) );
	}
	?>

</div>