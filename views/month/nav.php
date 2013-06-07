<?php 
/**
 * Calendar Nav Template
 * This file loads the month view navigation.
 *
 * You can recreate an ENTIRELY new calendar nav by doing a template override, and placing
 * a calendar/nav.php file in a tribe-events/ directory within your theme directory, which
 * will override /views/calendar/nav.php. 
 * *
 * @package TribeEventsCalendar
 * @since  3.0
 * @author Modern Tribe Inc.
 *
 */
?>

<?php do_action( 'tribe_events_before_nav' ) ?>

<h3 class="tribe-events-visuallyhidden"><?php _e( 'Calendar Month Navigation', 'tribe-events-calendar' ) ?></h3>

<ul class="tribe-events-sub-nav">
	<li class="tribe-events-nav-previous">
			<?php tribe_events_the_previous_month_link(); ?>
	</li><!-- .tribe-events-nav-previous -->
	<li class="tribe-events-nav-next">
		<?php tribe_events_the_next_month_link(); ?>
	</li><!-- .tribe-events-nav-next -->
</ul><!-- .tribe-events-sub-nav -->

<?php do_action( 'tribe_events_after_nav' ) ?>
