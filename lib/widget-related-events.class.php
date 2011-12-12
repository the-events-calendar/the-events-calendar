<?php
/**
* Related event widget
*/
// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if( !class_exists( 'TribeRelatedEventsWidget') ) {
	class TribeRelatedEventsWidget extends WP_Widget {
		function TribeRelatedEventsWidget() {
			// Widget settings.
			$widget_ops = array('classname' => 'related_events_widget', 'description' => __( 'Displays events related to the post.', 'tribe-events-calendar-pro') );
			// Create the widget.
			$this->WP_Widget('related-events-widget', __('Related Events', 'tribe-events-calendar-pro'), $widget_ops);
		}
		
		function widget($args, $instance) {
			extract($args);
			echo $before_widget;
			$title = apply_filters('widget_title', empty($instance['title']) ? '&nbsp;' : $instance['title']);
			$events = tribe_get_related_events($instance['count']);
			tribe_related_events($title, $instance['count'], $instance['thumbnails'], $instance['start_date']);
			echo $after_widget;
		}
		
		// Include the file for the administration view of the widget.
		function form($instance) {
			$defaults = array(
				'title' => '',
				'count' => 3,
				'thumbnails' => false,
				'start_date' => false
			);
			$instance = wp_parse_args( (array) $instance, $defaults );
			include( TribeEventsPro::instance()->pluginPath . 'admin-views/widget-admin-related-events.php' );
		}
		
		// Function allowing updating of widget information.
		function update($new_instance, $old_instance) {
			$instance = parent::update( $new_instance, $old_instance );
			
			$instance['title'] = $new_instance['title'];
			$instance['count'] = $new_instance['count'];
			$instance['thumbnails'] = $new_instance['thumbnails'];
			$instance['start_date'] = $new_instance['start_date'];
			
			return $instance;
		}
	
	}
	
	// Load the widget with the 'widgets_init' action.
	add_action( 'widgets_init', 'tribe_related_events_register_widget', 100 );
		
	function tribe_related_events_register_widget() {
		register_widget ('TribeRelatedEventsWidget');
	}
}