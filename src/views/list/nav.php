<?php
/**
 * List View Nav Template
 * This file loads the list view navigation.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/list/nav.php
 *
 * @package TribeEventsCalendar
 * @version 4.6.12
 *
 */
if ( ! $wp_query = tribe_get_global_query_object() ) {
	return;
}

$events_label_plural = tribe_get_event_label_plural();

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
} ?>

<h3 class="screen-reader-text" tabindex="0"><?php echo esc_html( sprintf( esc_html__( '%s List Navigation', 'the-events-calendar' ), $events_label_plural ) ); ?></h3>
<ul class="tribe-events-sub-nav">
	<!-- Left Navigation -->

	<?php if ( tribe_has_previous_event() ) : ?>
		<li class="<?php echo esc_attr( tribe_left_navigation_classes() ); ?>" aria-label="previous events link">
			<a href="<?php echo esc_url( tribe_get_listview_prev_link() ); ?>" rel="prev"><span>&laquo;</span> <?php echo esc_html( sprintf( __( 'Previous %s', 'the-events-calendar' ), $events_label_plural ) ); ?></a>

		</li><!-- .tribe-events-nav-left -->
	<?php endif; ?>

	<!-- Right Navigation -->
	<?php if ( tribe_has_next_event() ) : ?>
		<li class="<?php echo esc_attr( tribe_right_navigation_classes() ); ?>" aria-label="next events link">
			<a href="<?php echo esc_url( tribe_get_listview_next_link() ); ?>" rel="next"><?php echo esc_html( sprintf( __( 'Next %s', 'the-events-calendar' ), $events_label_plural ) ); ?> <span>&raquo;</span></a>
		</li><!-- .tribe-events-nav-right -->
	<?php endif; ?>
</ul>
