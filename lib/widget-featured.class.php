<?php
/**
 * Featured event widget
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if( !class_exists( 'Events_Featured_Widget') ) {
	class Events_Featured_Widget extends WP_Widget {
		
		function Events_Featured_Widget() {
			$widget_ops = array('classname' => 'Events_Featured_Widget', 'description' => __( 'Your next upcoming event') );
			$this->WP_Widget('featured_event', __('Featured Event'), $widget_ops);
		}


		function widget( $args, $instance ) {
			global $wp_query, $post;
			$old_post = $post;
			extract( $args, EXTR_SKIP );
			extract( $instance, EXTR_SKIP );
			// extracting $instance provides $title, $limit, $no_upcoming_events, $start, $end, $venue, $address, $city, $state, $province'], $zip, $country, $phone , $cost
			$title = apply_filters('widget_title', $title );
			
			if ( tribe_get_option('viewOption') == 'upcoming') {
				$event_url = tribe_get_listview_link();
			} else {
				$event_url = tribe_get_gridview_link();
			}

			if( function_exists( 'tribe_get_events' ) ) {
				$old_display = $wp_query->get('eventDisplay');
				$wp_query->set('eventDisplay', 'upcoming');
				$posts = tribe_get_events( 'numResults=1&eventCat=' . $category );				
				$template = TribeEventsTemplates::getTemplateHierarchy('widget-featured-display');
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
				foreach( $posts as $post ) : 
					setup_postdata($post);
					include $template;
				endforeach;

				$wp_query->set('eventDisplay', $old_display);
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
			$instance['title'] = strip_tags($new_instance['title']);
			$instance['category'] = $new_instance['category'];

			return $instance;
		}

		function form( $instance ) {
			$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
			$tribe_ecp = TribeEvents::instance();
			include( $tribe_ecp->pluginPath . 'admin-views/widget-admin-featured.php' );
		}
	
	}

	/* Add function to the widgets_ hook. */
	// hook is commented out until development is finished, allows WP's default calendar widget to work
	add_action( 'widgets_init', 'events_calendar_load_featured_widget',100);
	//add_action( 'widgets_init', 'get_calendar_custom' );

	//function get_calendar_custom(){echo "hi";}

	/* Function that registers widget. */
	function events_calendar_load_featured_widget() {
		register_widget( 'Events_Featured_Widget' );
		// load text domain after class registration
		load_plugin_textdomain( 'tribe-events-calendar', false, basename(dirname(dirname(__FILE__))) . '/lang/');
	}
}
?>