<?php 
/**
 * Calendar Header Nav Template
 * This file loads the month view navigation.
 *
 * You can recreate an ENTIRELY new calendar header nav by doing a template override, and placing
 * a calendar/nav-header.php file in a tribe-events/ directory within your theme directory, which
 * will override /views/calendar/nav-header.php. 
 * *
 * @package TribeEventsCalendar
 * @since  3.0
 * @author Modern Tribe Inc.
 *
 */
?>

<?php 

$current_month_date = tribe_get_month_view_date()
$prev_month_date = $tribe_ecp->previousMonth($current_month_view);
$prev_month_link = tribe_get_previous_month_link();
$prev_month_text = tribe_get_previous_month_text();

?>

<?php do_action('tribe_events_calendar_before_header_nav') ?>

<h3 class="tribe-events-visuallyhidden"><?php _e( 'Calendar Month Navigation', 'tribe-events-calendar' ) ?></h3>

<ul class="tribe-events-sub-nav">
	<li class="tribe-events-nav-previous">
			<?php tribe_previous_month_link(); ?>
	</li><!-- .tribe-events-nav-previous -->
	<li class="tribe-events-nav-next">
		<?php tribe_next_month_link(); ?>
		<img class="tribe-events-ajax-loading tribe-events-spinner-medium" src="<?php tribe_events_resource_url('images/tribe-loading.gif') ?>" alt="<?php _e('Loading Events', 'tribe-events') ?>" />	
	</li><!-- .tribe-events-nav-next -->
</ul><!-- .tribe-events-sub-nav -->

<?php do_action('tribe_events_calendar_after_header_nav') ?>
