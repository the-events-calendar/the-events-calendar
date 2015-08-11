<?php
/**
 * Related event widget
 */
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( ! class_exists( 'Tribe__Events__Pro__Venue_Widget' ) ) {
	class Tribe__Events__Pro__Venue_Widget extends WP_Widget {
		public function __construct() {
			// Widget settings.
			$widget_ops = array(
				'classname'   => 'tribe-events-venue-widget',
				'description' => __( 'Displays a list of upcoming events at a specific venue.', 'tribe-events-calendar-pro' ),
			);
			// Create the widget.
			parent::__construct( 'tribe-events-venue-widget', __( 'Events Featured Venue', 'tribe-events-calendar-pro' ), $widget_ops );
		}

		public function widget( $args, $instance ) {
			extract( $args );
			extract( $instance );

			if ( empty( $hide_if_empty ) ) {
				$hide_if_empty = false;
			}

			$event_args = array(
				'post_type'      => Tribe__Events__Main::POSTTYPE,
				'venue'          => $venue_ID,
				'posts_per_page' => $count,
				'eventDisplay'   => 'list',
				'tribe_render_context' => 'widget',
			);

			/**
			 * Filter Venue Widget tribe_get_event args
			 *
			 * @param array $event_args Arguments for the Venue Widget's call to tribe_get_events
			 */
			$event_args = apply_filters( 'tribe_events_pro_venue_widget_event_query_args', $event_args );

			// Get all the upcoming events for this venue.
			$events = tribe_get_events( $event_args, true );

			// If there are no events, and the user has set to hide if empty, don't display the widget.
			if ( $hide_if_empty && ! $events->have_posts() ) {
				return;
			}

			$ecp            = Tribe__Events__Pro__Main::instance();
			$tooltip_status = $ecp->recurring_info_tooltip_status();
			$ecp->disable_recurring_info_tooltip();

			echo $before_widget;

			do_action( 'tribe_events_venue_widget_before_the_title' );

			echo ( $instance['title'] ) ? $args['before_title'] . $instance['title'] . $args['after_title'] : '';

			do_action( 'tribe_events_venue_widget_after_the_title' );

			include( Tribe__Events__Templates::getTemplateHierarchy( 'pro/widgets/venue-widget.php' ) );
			echo $after_widget;

			if ( $tooltip_status ) {
				$ecp->enable_recurring_info_tooltip();
			}

			wp_reset_postdata();
		}

		// Include the file for the administration view of the widget.
		public function form( $instance ) {
			$defaults = array(
				'title'         => '',
				'venue_ID'      => null,
				'count'         => 3,
				'hide_if_empty' => true,
			);
			$venues   = get_posts( array(
					'post_type' => Tribe__Events__Main::VENUE_POST_TYPE,
					'orderby'   => 'title',
					'nopaging'  => true,
				) );
			$instance = wp_parse_args( (array) $instance, $defaults );
			include( Tribe__Events__Pro__Main::instance()->pluginPath . 'src/admin-views/widget-admin-venue.php' );
		}

		// Function allowing updating of widget information.
		public function update( $new_instance, $old_instance ) {
			$instance = parent::update( $new_instance, $old_instance );

			$instance['title']         = $new_instance['title'];
			$instance['venue_ID']      = $new_instance['venue_ID'];
			$instance['count']         = $new_instance['count'];
			$instance['hide_if_empty'] = $new_instance['hide_if_empty'];

			return $instance;
		}
	}
}
