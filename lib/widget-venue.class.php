<?php
/**
* Related event widget
*/
// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }
 
if( !class_exists( 'TribeVenueWidget') ) {
	class TribeVenueWidget extends WP_Widget {
		function TribeVenueWidget() {
			// Widget settings.
			$widget_ops = array('classname' => 'tribe-events-venue-widget', 'description' => __( 'Displays a list of upcoming events at a specific venue.', 'tribe-events-calendar-pro') );
			// Create the widget.
			$this->WP_Widget('tribe-events-venue-widget', __('Venue Widget', 'tribe-events-calendar-pro'), $widget_ops);
		}
 
		function widget($args, $instance) {
			extract($args);
			extract($instance);
			// Get all the upcoming events for this venue.
			$events = tribe_get_events( array( 'post_type' => TribeEvents::POSTTYPE, 'meta_key' => '_EventVenueID', 'meta_value' => $venue_ID, 'posts_per_page' => $count, 'eventDisplay' => 'upcoming') );
			// If there are events, or if the user has set to show if empty, display the widget.
			if ($show_if_empty || (!$show_if_empty && count($events) > 0)) {
				echo $before_widget;
				$title = $before_title.apply_filters('widget_title', $title).$after_title;
				include( TribeEventsTemplates::getTemplateHierarchy('widgets/venue-widget.php' ) );
				echo $after_widget;
			}
		}
 
		// Include the file for the administration view of the widget.
		function form($instance) {
			$defaults = array(
				'title' => '',
				'venue_ID' => null,
				'count' => 3,
				'show_if_empty' => true,
			);
			$venues = get_posts( array( 'post_type' => TribeEvents::VENUE_POST_TYPE, 'orderby' => 'post_title', 'nopaging' => true ) );
			$instance = wp_parse_args( (array) $instance, $defaults );
			include( TribeEventsPro::instance()->pluginPath . 'admin-views/widget-admin-venue.php' );
		}
 
		// Function allowing updating of widget information.
		function update($new_instance, $old_instance) {
			$instance = parent::update( $new_instance, $old_instance );
 
			$instance['title'] = $new_instance['title'];
			$instance['venue_ID'] = $new_instance['venue_ID'];
			$instance['count'] = $new_instance['count'];
			$instance['show_if_empty'] = $new_instance['show_if_empty'];
 
			return $instance;
		}
	}
	// Load the widget with the 'widgets_init' action.
	add_action( 'widgets_init', 'tribe_venue_register_widget', 100 );
 
	function tribe_venue_register_widget() {
		register_widget('TribeVenueWidget');
	}
}
