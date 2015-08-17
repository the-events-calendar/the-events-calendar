<?php
/**
 * Events Pro Mini Calendar Widget
 * This is the template for the output of the mini calendar widget.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/pro/widgets/mini-calendar-widget.php
 *
 * @package TribeEventsCalendarPro
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

$args = tribe_events_get_mini_calendar_args();

?>

<!-- Removing this wrapper class will break the claendar javascript, please avoid and extend as needed -->

<div class="tribe-mini-calendar-wrapper">

	<!-- Grid -->
	<?php
	$month_widget_args = array(
		'tax_query' => $args['tax_query'],
		'eventDate' => $args['eventDate'],
		'suppress_nothing_found_notice' => true,
		'tribe_render_context' => 'widget',
	);

	/**
	 * Filter Mini Calendar Widget tribe_show_month args
	 *
	 * @param array $month_widget_args Arguments for the Mini Calendar Widget's call to tribe_show_month
	 */
	$month_widget_args = apply_filters( 'tribe_events_pro_min_calendar_widget_query_args', $month_widget_args );

	tribe_show_month( $month_widget_args, 'pro/widgets/mini-calendar/grid' ); ?>

	<!-- List -->
	<?php
	if ( 0 < $args['count'] ) {
		tribe_get_template_part( 'pro/widgets/mini-calendar/list', null, array( 'venue' => true ) );
	}
	?>

</div>
