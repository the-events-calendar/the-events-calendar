<?php
if( !class_exists( 'Events_Calendar_Widget') ) {
	
	/**
	* Calendar widget class
	*/
	class Events_Calendar_Widget extends WP_Widget {
		
			public $pluginDomain = 'the-events-calendar';

			function Events_Calendar_Widget() {
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
				//echo get_calendar_custom(); /* 5 is the category id I have for event */
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
				$title = strip_tags($instance['title']);
		?>
				<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
		<?php
			}
		
		}
	
		/* Add function to the widgets_ hook. */
		// hook is commented out until development is finished, allows WP's default calendar widget to work
		//add_action( 'widgets_init', 'events_calendar_load_widgets' );
		//add_action( 'widgets_init', 'get_calendar_custom' );
	
		//function get_calendar_custom(){echo "hi";}

		/* Function that registers widget. */
		function events_calendar_load_widgets() {
			global $pluginDomain;
			register_widget( 'Events_Calendar_Widget' );
			// load text domain after class registration
			load_plugin_textdomain( $pluginDomain, false, basename(dirname(__FILE__)) . '/lang/');
		}
}