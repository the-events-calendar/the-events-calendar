<?php 
/**
 * Calendar Template
 * This file loads the TEC month or calendar view, specifically the month view navigation.
 *
 * You can recreate an ENTIRELY new calendar view by doing a template override, and placing
 * a calendar.php file in a tribe-events/ directory within your theme directory, which
 * will override the /views/calendar.php. 
 *
 * You can use any or all filters included in this file or create your own filters in 
 * your functions.php. In order to modify or extend a single filter, please see our
 * readme on templates hooks and filters (TO-DO)
 *
 * @package TribeEventsCalendar
 * @since  3.0
 * @author Modern Tribe Inc.
 *
 */
?>

<?php do_action('tribe_events_calendar_before_template') ?>
<div id="tribe-events-content" class="tribe-events-calendar">
	
	<!-- Calendar Title -->
	<?php do_action('tribe_events_calendar_before_the_title') ?>
	<h2 class="tribe-events-page-title"><?php tribe_events_title() ?></h2>
	<?php do_action('tribe_events_calendar_after_the_title') ?>

	<!-- Notices -->
	<?php tribe_events_the_notices() ?>

	<!-- Calendar Header -->
	<?php do_action('tribe_events_calendar_before_header') ?>
	<div id="tribe-events-header" <?php tribe_events_the_header_attributes() ?>>

		<!-- Header Navigation -->
		<?php tribe_get_template_part('calendar/nav', 'header'); ?>

	</div><!-- #tribe-events-header -->
	<?php do_action('tribe_events_calendar_after_header') ?>

	<!-- Calendar Grid -->
	<?php tribe_get_template_part('calendar/loop', 'grid') ?>

	<!-- Calendar Footer -->
	<?php do_action('tribe_events_calendar_before_footer') ?>
	<div id="tribe-events-footer">

		<!-- Footer Navigation -->
		<?php tribe_get_template_part('calendar/nav', 'footer'); ?>

	</div><!-- #tribe-events-footer -->
	<?php do_action('tribe_events_calendar_after_footer') ?>
	
</div><!-- #tribe-events-content -->
<?php do_action('tribe_events_calendar_after_template') ?>
