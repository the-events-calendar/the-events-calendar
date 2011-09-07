<?php
/**
 * Event List Widget - Premium version
 *
 * Creates a widget that displays the next upcoming x events
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if( !class_exists( 'TribeEventsAdvancedListWidget' ) ) {
	class TribeEventsAdvancedListWidget extends WP_Widget {
				
		function TribeEventsAdvancedListWidget() {
			/* Widget settings. */
			$widget_ops = array( 'classname' => 'eventsAdvancedListWidget', 'description' => __( 'A widget that displays the next upcoming x events.', TribeEvents::PLUGIN_DOMAIN ) );

			/* Widget control settings. */
			$control_ops = array( 'id_base' => 'events-advanced-list-widget' );

			/* Create the widget. */
			$this->WP_Widget( 'events-advanced-list-widget', 'Events List Advanced Widget', $widget_ops, $control_ops );
	
			/* Add function to look for view in premium directory rather than free. */
			add_filter( 'tribe_events_template_events-advanced-list-load-widget-display.php', array( $this, 'load_premium_view' ) );
		}
	
		function widget( $args, $instance ) {
			global $wp_query, $tribe_ecp, $post;
			$old_post = $post;
			extract( $args, EXTR_SKIP );
			extract( $instance, EXTR_SKIP );
			// extracting $instance provides $title, $limit, $no_upcoming_events, $start, $end, $venue, $address, $city, $state, $province'], $zip, $country, $phone , $cost
			$title = apply_filters('widget_title', $title );
			if ( tribe_get_option('viewOption') == 'upcoming') {
				$event_url = tribe_get_listview_link($category != -1 ? intval($category) : null);
			} else {
				$event_url = tribe_get_gridview_link($category != -1 ? intval($category) : null);
			}

			if( function_exists( 'tribe_get_events' ) ) {
				$posts = tribe_get_events( 'eventDisplay=upcoming&numResults=' . $limit .'&eventCat=' . $category );
				$template = TribeEventsTemplates::getTemplateHierarchy('events-advanced-list-load-widget-display');
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
				echo '<div class="dig-in"><a href="' . $event_url . '">' . __('View All Events', TribeEvents::PLUGIN_DOMAIN ) . '</a></div>';
			} 
			else {
				_e('There are no upcoming events at this time.', TribeEvents::PLUGIN_DOMAIN);
			}

			/* After widget (defined by themes). */
			echo $after_widget;
			$post = $old_post;
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
				$instance['region'] = $new_instance['region'];
				$instance['zip'] = $new_instance['zip'];
				$instance['phone'] = $new_instance['phone'];
				$instance['cost'] = $new_instance['cost'];
				$instance['category'] = $new_instance['category'];
				error_log("new Instance: " . print_r($instance, true));
				error_log("old Instance: " . print_r($old_instance, true));
				return $instance;
		}
	
		function form( $instance ) {				
			/* Set up default widget settings. */
			$defaults = array( 'title' => 'Upcoming Events', 'limit' => '5', 'start' => true, 'end' => false, 'venue' => false, 'country' => true, 'address' => false, 'city' => true, 'region' => true, 'zip' => false, 'phone' => false, 'cost' => false,'category' => false);
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


	/* Add function to the widgets_ hook. */
	add_action( 'widgets_init', 'events_advanced_list_load_widgets',100 );

	/* Function that registers widget. */
	function events_advanced_list_load_widgets() {
		// Unregister the free version of the widget, and register the pro version.
		unregister_widget( 'TribeEventsListWidget' );
		register_widget( 'TribeEventsAdvancedListWidget' );
		// load text domain after class registration
		load_plugin_textdomain( 'tribe-events-calendar', false, basename(dirname(dirname(__FILE__))) . '/lang/');
	}
}
