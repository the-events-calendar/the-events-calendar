<?php 
/**
 * Month Template
 * This file loads the TEC month or month view, specifically the month view navigation.
 *
 * You can recreate an ENTIRELY new month view by doing a template override, and placing
 * a month.php file in a tribe-events/ directory within your theme directory, which
 * will override the /views/month.php. 
 *
 * You can use any or all filters included in this file or create your own filters in 
 * your functions.php. In order to modify or extend a single filter, please see our
 * readme on templates hooks and filters (TO-DO)
 *
 * @package TribeEventsMonth
 * @since  3.0
 * @author Modern Tribe Inc.
 *
 */
?>

<?php do_action('tribe_events_month_before_template') ?>

<!-- Tribe Bar -->
<?php tribe_get_template_part('modules/bar'); ?>

<!-- Main Events Content -->
<div id="tribe-events-content" class="tribe-events-month">
	
	<!-- Month Title -->
	<?php do_action('tribe_events_month_before_the_title') ?>
	<h2 class="tribe-events-page-title"><?php tribe_events_title() ?></h2>
	<?php do_action('tribe_events_month_after_the_title') ?>

	<!-- Notices -->
	<?php tribe_events_the_notices() ?>

	<!-- Month Header -->
	<?php do_action('tribe_events_month_before_header') ?>
	<div id="tribe-events-header" <?php tribe_events_the_header_attributes() ?>>

		<!-- Header Navigation -->
		<?php tribe_get_template_part('month/nav'); ?>

	</div><!-- #tribe-events-header -->
	<?php do_action('tribe_events_month_after_header') ?>

	<!-- Month Grid -->
	<?php tribe_get_template_part('month/loop', 'grid') ?>

	<!-- Month Footer -->
	<?php do_action('tribe_events_month_before_footer') ?>
	<div id="tribe-events-footer">

		<!-- Footer Navigation -->
		<?php tribe_get_template_part('month/nav'); ?>

	</div><!-- #tribe-events-footer -->
	<?php do_action('tribe_events_month_after_footer') ?>
	
</div><!-- #tribe-events-content -->
<?php do_action('tribe_events_month_after_template') ?>
