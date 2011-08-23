<?php
/**
 * Events Calendar widget class
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if( !class_exists( 'TribeEventsCalendarWidget') ) {

	class TribeEventsCalendarWidget extends WP_Widget {
		
		function TribeEventsCalendarWidget() {
			$widget_ops = array('classname' => 'events_calendar_widget', 'description' => __( 'A calendar of your events') );
			$this->WP_Widget('calendar', __('Events Calendar'), $widget_ops);
		}

		function widget( $args, $instance ) {
			extract($args);
			$title = apply_filters('widget_title', empty($instance['title']) ? '&nbsp;' : $instance['title']);
			echo $before_widget;
			if ( $title )
				echo $before_title . $title . $after_title;
			echo '<div id="calendar_wrap">';
				tribe_calendar_mini_grid();
			echo '</div>';
			echo $after_widget;
		}
	
		function update( $new_instance, $old_instance ) {
			$instance = $old_instance;
			$instance['title'] = strip_tags($new_instance['title']);

			return $instance;
		}

		function form( $instance ) {
			$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
			$tribe_ecp = TribeEvents::instance();		
			include( $tribe_ecp->pluginPath . 'admin-views/widget-admin-calendar.php' );
		}
	
	}

	/* Add function to the widgets_ hook. */
	// hook is commented out until development is finished, allows WP's default calendar widget to work
	add_action( 'widgets_init', 'events_calendar_load_widgets',100);
	//add_action( 'widgets_init', 'get_calendar_custom' );

	//function get_calendar_custom(){echo "hi";}

	/* Function that registers widget. */
	function events_calendar_load_widgets() {
		register_widget( 'TribeEventsCalendarWidget' );
		// load text domain after class registration
		load_plugin_textdomain( 'tribe-events-calendar', false, basename(dirname(dirname(__FILE__))) . '/lang/');
	}
}
?>