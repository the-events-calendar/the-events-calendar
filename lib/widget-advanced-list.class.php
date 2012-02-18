<?php
/**
 * Event List Widget - Premium version
 *
 * Creates a widget that displays the next upcoming x events
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if( !class_exists( 'TribeEventsAdvancedListWidget' ) ) {
	class TribeEventsAdvancedListWidget extends TribeEventsListWidget {
				
		function TribeEventsAdvancedListWidget() {
			/* Widget settings. */
			$widget_ops = array( 'classname' => 'eventsAdvancedListWidget', 'description' => __( 'A widget that displays the next upcoming x events.', 'tribe-events-calendar-pro' ) );

			/* Widget control settings. */
			$control_ops = array( 'id_base' => 'events-advanced-list-widget' );

			/* Create the widget. */
			$this->WP_Widget( 'events-advanced-list-widget', 'Events List Advanced Widget', $widget_ops, $control_ops );
	
			/* Add function to look for view in premium directory rather than free. */
			add_filter( 'tribe_events_template_events-advanced-list-load-widget-display.php', array( $this, 'load_premium_view' ) );
		}
	
		function widget( $args, $instance ) {
			// Use parent's output function with the premium template.
			return parent::widget_output( $args, $instance, 'events-advanced-list-load-widget-display' );
		}

		function update( $new_instance, $old_instance ) {
				$instance = parent::update( $new_instance, $old_instance );

				/* Process remaining options. */
				/* Strip tags (if needed) and update the widget settings. */
				$instance['start'] = $new_instance['start'];
				$instance['end'] = $new_instance['end'];
				$instance['venue'] = $new_instance['venue'];
				$instance['country'] = $new_instance['country'];
				$instance['address'] = $new_instance['address'];
				$instance['city'] = $new_instance['city'];
				$instance['region'] = $new_instance['region'];
				$instance['zip'] = $new_instance['zip'];
				$instance['phone'] = $new_instance['phone'];
				$instance['cost'] = $new_instance['cost'];
				$instance['category'] = $new_instance['category'];
				return $instance;
		}
	
		function form( $instance ) {				
			/* Set up default widget settings. */
			$defaults = array( 'title' => 'Upcoming Events', 'limit' => '5', 'no_upcoming_events' => false, 'start' => true, 'end' => false, 'venue' => false, 'country' => true, 'address' => false, 'city' => true, 'region' => true, 'zip' => false, 'phone' => false, 'cost' => false,'category' => false);
			$instance = wp_parse_args( (array) $instance, $defaults );
			include( TribeEventsPro::instance()->pluginPath . 'admin-views/widget-admin-advanced-list.php' );
		}

		function load_premium_view($file) {
			if ( !file_exists($file) ) {
				$file = TribeEventsPro::instance()->pluginPath . 'views/events-advanced-list-load-widget-display.php';
			}

			return $file;
		}
	}
}
