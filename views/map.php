<?php
/**
 * Events List Template
 * The template for a list of events. This includes the Past Events and Upcoming Events views 
 * as well as those same views filtered to a specific category.
 *
 * This view contains the filters required to create an effective events list view.
 *
 * You can recreate an ENTIRELY new list view by doing a template override, and placing
 * a list.php file in a tribe-events/ directory within your theme directory, which
 * will override the /views/list.php.
 *
 * You can use any or all filters included in this file or create your own filters in 
 * your functions.php. In order to modify or extend a single filter, please see our
 * readme on templates hooks and filters (TO-DO)
 *
 * @package TribeEventsCalendar
 * @since  2.1
 * @author Modern Tribe Inc.
 *
 */
?>

<?php do_action( 'tribe_events_map_before_template' ); ?>

<!-- Google Map Container -->
<?php tribe_get_template_part('map/gmap-container') ?>

<!-- Tribe Bar -->
<?php tribe_get_template_part('modules/bar'); ?>

<!-- Main Events Content -->
<div id="tribe-events-content" class="tribe-events-list tribe-events-map">
	
	<!-- List Title -->
	<?php do_action( 'tribe_events_map_before_the_title' ); ?>
		<h2 class="tribe-events-page-title"><?php echo tribe_get_events_title() ?></h2>
		<div id="tribe-geo-options">
			<h2><?php _e( 'Refine your search:', 'tribe-events-calendar-pro' )?></h2>
			<div id="tribe-geo-links"></div><!-- #tribe-geo-links -->
		</div><!-- #tribe-geo-options -->
		<?php do_action( 'tribe_events_map_after_the_title' ); ?>

		<!-- Notices -->
		<?php tribe_events_the_notices() ?>

		<!-- List Header -->
		<?php do_action( 'tribe_events_map_before_header' ); ?>
		<div id="tribe-events-header" <?php tribe_events_the_header_attributes() ?>>

			<!-- Header Navigation -->
			<?php do_action( 'tribe_events_map_before_header_nav' ); ?>
			<?php tribe_get_template_part('map/nav', 'header'); ?>
			<?php do_action( 'tribe_events_map_after_header_nav' ); ?>

		</div><!-- #tribe-events-header -->
		<?php do_action( 'tribe_events_map_after_header' ); ?>

		<!-- Events Loop -->
		<?php if ( have_posts() ) : ?>
			<?php do_action( 'tribe_events_map_before_loop' ); ?>
			<div id="tribe-geo-results" class="tribe-events-loop">
			<?php tribe_get_template_part('map/loop', 'map') ?>
			</div> <!-- #tribe-geo-results -->
			<?php do_action('tribe_events_map_after_loop'); ?>
		<?php endif; ?>

		<!-- List Footer -->
		<?php do_action( 'tribe_events_map_before_footer' ); ?>
		<div id="tribe-events-footer">

			<!-- Footer Navigation -->
			<?php do_action( 'tribe_events_map_before_footer_nav' ); ?>
			<?php tribe_get_template_part('map/nav', 'footer'); ?>
			<?php do_action( 'tribe_events_map_after_footer_nav' ); ?>

		</div><!-- #tribe-events-footer -->
		<?php do_action( 'tribe_events_map_after_footer' ) ?>

</div><!-- #tribe-events-content -->

<div class="tribe-clear"></div>

<?php do_action('tribe_events_map_after_template') ?>
