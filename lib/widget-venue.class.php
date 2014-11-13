<?php
/**
 * Related event widget
 */
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( ! class_exists( 'TribeVenueWidget' ) ) {
	class TribeVenueWidget extends WP_Widget {
		function TribeVenueWidget() {
			// Widget settings.
			$widget_ops = array(
				'classname'   => 'tribe-events-venue-widget',
				'description' => __( 'Displays a list of upcoming events at a specific venue.', 'tribe-events-calendar-pro' )
			);
			// Create the widget.
			$this->WP_Widget( 'tribe-events-venue-widget', __( 'Events Featured Venue', 'tribe-events-calendar-pro' ), $widget_ops );
		}

		function widget( $args, $instance ) {
			extract( $args );
			extract( $instance );

			if ( empty( $hide_if_empty ) ) {
				$hide_if_empty = false;
			}

			// Get all the upcoming events for this venue.
			$events = tribe_get_events( array(
				'post_type'      => TribeEvents::POSTTYPE,
				'venue'          => $venue_ID,
				'posts_per_page' => $count,
				'eventDisplay'   => 'list'
			), true );

			// If there are no events, and the user has set to hide if empty, don't display the widget.
			if ( $hide_if_empty && ! $events->have_posts() ) {
				return;
			}

			$ecp            = TribeEventsPro::instance();
			$tooltip_status = $ecp->recurring_info_tooltip_status();
			$ecp->disable_recurring_info_tooltip();

			echo $before_widget;

			do_action( 'tribe_events_venue_widget_before_the_title' );

			echo ( $instance['title'] ) ? $args['before_title'] . $instance['title'] . $args['after_title'] : '';

			do_action( 'tribe_events_venue_widget_after_the_title' );

			include( TribeEventsTemplates::getTemplateHierarchy( 'pro/widgets/venue-widget.php' ) );
			echo $after_widget;

			if ( $tooltip_status ) {
				$ecp->enable_recurring_info_tooltip();
			}

			wp_reset_postdata();
		}

		// Include the file for the administration view of the widget.
		function form( $instance ) {
			$defaults = array(
				'title'         => '',
				'venue_ID'      => null,
				'count'         => 3,
				'hide_if_empty' => true,
			);
			$venues   = get_posts( array(
					'post_type' => TribeEvents::VENUE_POST_TYPE,
					'orderby'   => 'title',
					'nopaging'  => true
				) );
			$instance = wp_parse_args( (array) $instance, $defaults );
			include( TribeEventsPro::instance()->pluginPath . 'admin-views/widget-admin-venue.php' );
		}

		// Function allowing updating of widget information.
		function update( $new_instance, $old_instance ) {
			$instance = parent::update( $new_instance, $old_instance );

			$instance['title']         = $new_instance['title'];
			$instance['venue_ID']      = $new_instance['venue_ID'];
			$instance['count']         = $new_instance['count'];
			$instance['hide_if_empty'] = $new_instance['hide_if_empty'];

			return $instance;
		}
	}

	// Load the widget with the 'widgets_init' action.
	add_action( 'widgets_init', 'tribe_venue_register_widget', 100 );

	function tribe_venue_register_widget() {
		register_widget( 'TribeVenueWidget' );
	}
}
