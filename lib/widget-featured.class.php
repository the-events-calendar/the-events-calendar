<?php
/**
 * Featured event widget
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if( !class_exists( 'TribeEventsFeatureWidget') ) {
	class TribeEventsFeatureWidget extends WP_Widget {
		
		function TribeEventsFeatureWidget() {
			$widget_ops = array('classname' => 'TribeEventsFeatureWidget', 'description' => __( 'Your next upcoming event') );
			$this->WP_Widget('next_event', __('Next Event Widget'), $widget_ops);
			/* Add function to look for view in premium directory rather than free. */
			add_filter( 'tribe_events_template_widget-featured-display.php', array( $this, 'load_premium_view' ) );
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
				$posts = tribe_get_events( 'eventDisplay=upcoming&numResults=1&eventCat=' . $category );
				$template = TribeEventsTemplates::getTemplateHierarchy('widget-featured-display');
			}
			
			// if no posts, and the don't show if no posts checked, let's bail
			if ( ! $posts && isset($no_upcoming_events) && $no_upcoming_events ) {
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
			} 
			else {
            echo "<p>";
				_e('There are no upcoming events at this time.', 'tribe-events-calendar-pro');
            echo "</p>";
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
			$tribe_ecp = TribeEventsPro::instance();
			include( $tribe_ecp->pluginPath . 'admin-views/widget-admin-featured.php' );
		}

		function load_premium_view($file) {
                        if ( !file_exists($file) ) {
                                $file = TribeEventsPro::instance()->pluginPath . 'views/widget-featured-display.php';
                        }

                        return $file;
                }
	}

	/* Add function to the widgets_ hook. */
	// hook is commented out until development is finished, allows WP's default calendar widget to work
	add_action( 'widgets_init', 'events_calendar_load_featured_widget',100);
	//add_action( 'widgets_init', 'get_calendar_custom' );

	//function get_calendar_custom(){echo "hi";}

	/* Function that registers widget. */
	function events_calendar_load_featured_widget() {
		register_widget( 'TribeEventsFeatureWidget' );
		// load text domain after class registration
		load_plugin_textdomain( 'tribe-events-calendar-pro', false, basename(dirname(dirname(__FILE__))) . '/lang/');
	}
}
?>
