<?php
if( !class_exists( 'Events_List_Widget' ) ) {
	/**
	 * Event List Widget
	 *
	 * Creates a widget that displays the next upcoming x events
	 */

	class Events_List_Widget extends WP_Widget {
		
		public $pluginDomain = 'the-events-calendar';
		
		function Events_List_Widget() {
				/* Widget settings. */
				$widget_ops = array( 'classname' => 'eventsListWidget', 'description' => __( 'A widget that displays the next upcoming x events.', $this->pluginDomain) );

				/* Widget control settings. */
				$control_ops = array( 'id_base' => 'events-list-widget' );

				/* Create the widget. */
				$this->WP_Widget( 'events-list-widget', 'Events List Widget', $widget_ops, $control_ops );
			}
		
			function widget( $args, $instance ) {
				global $wp_query, $spEvents;
				extract( $args, EXTR_SKIP );
				extract( $instance, EXTR_SKIP );
				// extracting $instance provides $title, $limit, $no_upcoming_events, $start, $end, $venue, $address, $city, $state, $province'], $zip, $country, $phone , $cost
				$title = apply_filters('widget_title', $title );
				
				if ( sp_get_option('viewOption') == 'upcoming') {
					$event_url = sp_get_listview_link();
				} else {
					$event_url = sp_get_gridview_link();
				}

				if( function_exists( 'sp_get_events' ) ) {
					$old_display = $wp_query->get('eventDisplay');
					$wp_query->set('eventDisplay', 'upcoming');
					$posts = sp_get_events( 'numResults=' . $limit );
					$template = $spEvents->getTemplateHierarchy('events-list-load-widget-display');
				}
				
				// if no posts, and the don't show if no posts checked, let's bail
				if ( ! $posts && $no_upcoming_events ) {
					return;
				}
				
				/* Before widget (defined by themes). */
				echo $before_widget;
				
				/* Title of widget (before and after defined by themes). */
				echo ( $title ) ? $before_title . $title . $after_title : '';
									
				if ( $posts ) {
					/* Display list of events. */
					echo "<ul class='upcoming'>";
					foreach( $posts as $post ) : 
						setup_postdata($post);
						include $template;
					endforeach;
					echo "</ul>";

					$wp_query->set('eventDisplay', $old_display);
					
					/* Display link to all events */
					echo '<div class="dig-in"><a href="' . $event_url . '">' . __('View All Events', $this->pluginDomain ) . '</a></div>';
				} 
				else {
					_e('There are no upcoming events at this time.', $this->pluginDomain);
				}

				/* After widget (defined by themes). */
				echo $after_widget;
			}	
		
			function update( $new_instance, $old_instance ) {
					$instance = $old_instance;

					/* Strip tags (if needed) and update the widget settings. */
					$instance['title'] = strip_tags( $new_instance['title'] );
					$instance['limit'] = $new_instance['limit'];
					$instance['no_upcoming_events'] = $new_instance['no_upcoming_events'];
					$instance['start'] = $new_instance['start'];
					$instance['end'] = $new_instance['end'];
					$instance['venue'] = $new_instance['venue'];
					$instance['country'] = $new_instance['country'];
					$instance['address'] = $new_instance['address'];
					$instance['city'] = $new_instance['city'];
					$instance['state'] = $new_instance['state'];
					$instance['province'] = $new_instance['province'];
					$instance['zip'] = $new_instance['zip'];
					$instance['phone'] = $new_instance['phone'];
					$instance['cost'] = $new_instance['cost'];

					return $instance;
			}
		
			function form( $instance ) {
				/* Set up default widget settings. */
				$defaults = array( 'title' => 'Upcoming Events', 'limit' => '5', 'start' => true, 'end' => false, 'venue' => false, 'country' => true, 'address' => false, 'city' => true, 'state' => true, 'province' => true, 'zip' => false, 'phone' => false, 'cost' => false);
				$instance = wp_parse_args( (array) $instance, $defaults );			
				include( $spEvents->pluginPath . 'views/events-list-load-widget-admin.php' );
			}
	}

	/* Add function to the widgets_ hook. */
	add_action( 'widgets_init', 'events_list_load_widgets' );

	/* Function that registers widget. */
	function events_list_load_widgets() {
		global $pluginDomain;
		register_widget( 'Events_List_Widget' );
		// load text domain after class registration
		load_plugin_textdomain( 'the-events-calendar', false, basename(dirname(__FILE__)) . '/lang/');
	}
}