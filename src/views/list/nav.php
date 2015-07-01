<?php
/**
 * List View Nav Template
 * This file loads the list view navigation.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/list/nav.php
 *
 * @package TribeEventsCalendar
 *
 */
global $wp_query;

$events_label_plural = tribe_get_event_label_plural();

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
} ?>

<h3 class="tribe-events-visuallyhidden"><?php echo esc_html( sprintf( __( '%s List Navigation', 'tribe-events-calendar' ), $events_label_plural ) ); ?></h3>
<ul class="tribe-events-sub-nav">
	<!-- Left Navigation -->

	<?php if ( tribe_has_previous_event() ) : ?>
		<li class="<?php echo esc_attr( tribe_left_navigation_classes() ); ?>">
			<a href="<?php echo esc_url( tribe_get_listview_prev_link() ); ?>" rel="prev"><?php printf( __( '<span>&laquo;</span> Previous %s', 'tribe-events-calendar' ), $events_label_plural ); ?></a>
		</li><!-- .tribe-events-nav-left -->
	<?php endif; ?>

	<!-- Right Navigation -->
	<?php if ( tribe_has_next_event() ) : ?>
		<li class="<?php echo esc_attr( tribe_right_navigation_classes() ); ?>">
			<a href="<?php echo esc_url( tribe_get_listview_next_link() ); ?>" rel="next"><?php printf( __( 'Next %s <span>&raquo;</span>', 'tribe-events-calendar' ), $events_label_plural ); ?></a>
		</li><!-- .tribe-events-nav-right -->
	<?php endif; ?>
</ul>
