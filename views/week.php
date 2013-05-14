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

	if ( !defined('ABSPATH') ) 
		die('-1');

?>

<?php do_action('tribe_events_week_before_template') ?>

<!-- Tribe Bar -->
<?php tribe_get_template_part('modules/bar'); ?>

	<div id="tribe-events-content" class="tribe-events-week-grid">
		
		<!-- Calendar Title -->
		<?php do_action('tribe_events_week_before_the_title') ?>
		<h2 class="tribe-events-page-title"><?php tribe_events_title() ?></h2>
		<?php do_action('tribe_events_week_after_the_title') ?>

		<!-- Notices -->
		<?php tribe_events_the_notices() ?>

		<!-- Calendar Header -->
		<?php do_action('tribe_events_week_before_header') ?>
		<div id="tribe-events-header" <?php tribe_events_the_header_attributes('week-header') ?>>

			<!-- Header Navigation -->
			<?php tribe_get_template_part('week/nav', 'header'); ?>

		</div><!-- #tribe-events-header -->
		<?php do_action('tribe_events_week_after_header') ?>

		<!-- Calendar Grid -->
		<?php tribe_get_template_part('week/loop', 'grid') ?>

		<!-- Calendar Footer -->
		<?php do_action('tribe_events_week_before_footer') ?>
		<div id="tribe-events-footer">

			<!-- Footer Navigation -->
			<?php tribe_get_template_part('week/nav', 'footer'); ?>

		</div><!-- #tribe-events-footer -->
		<?php do_action('tribe_events_week_after_footer') ?>
		
	</div><!-- #tribe-events-content -->

<?php do_action('tribe_events_week_after_template') ?>


<?php /* echo Tribe_Events_Week_Template::the_grid(); */ ?>