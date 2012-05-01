<?php
/**
 * Event List Widget
 *
 * Creates a widget that displays the next upcoming x events
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if( !class_exists( 'TribeEventsListWidget' ) ) {
	class TribeEventsListWidget extends WP_Widget {
				
		function TribeEventsListWidget() {
			/* Widget settings. */
			$widget_ops = array( 'classname' => 'eventsListWidget', 'description' => __( 'A widget that displays the next upcoming x events.', 'tribe-events-calendar' ) );

			/* Widget control settings. */
			$control_ops = array( 'id_base' => 'events-list-widget' );

			/* Create the widget. */
			$this->WP_Widget( 'events-list-widget', 'Events List Widget', $widget_ops, $control_ops );
		}

		function widget( $args, $instance ) {
			return $this->widget_output( $args, $instance );
		}

		function widget_output( $args, $instance, $template_name='events-list-load-widget-display' ) {
			global $wp_query, $tribe_ecp, $post;
			extract( $args, EXTR_SKIP );
			extract( $instance, EXTR_SKIP );
			// extracting $instance provides $title, $limit
			$title = apply_filters('widget_title', $title );
			if (!isset($category)) {
				$category = null;
			}
			if ( tribe_get_option('viewOption') == 'upcoming') {
				$event_url = tribe_get_listview_link($category != -1 ? intval($category) : null);
			} else {
				$event_url = tribe_get_gridview_link($category != -1 ? intval($category) : null);
			}

			if( function_exists( 'tribe_get_events' ) ) {
				$posts = tribe_get_events( 'eventDisplay=upcoming&posts_per_page=' . $limit .'&eventCat=' . $category );
				$template = TribeEventsTemplates::getTemplateHierarchy( $template_name );
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

				/* Display link to all events */
				echo '<div class="dig-in"><a href="' . $event_url . '">' . __('View All Events', 'tribe-events-calendar' ) . '</a></div>';
			} 
			else {
				_e('There are no upcoming events at this time.', 'tribe-events-calendar');
			}

			/* After widget (defined by themes). */
			echo $after_widget;
			wp_reset_query();
		}	
	
		function update( $new_instance, $old_instance ) {
				$instance = $old_instance;

				/* Strip tags (if needed) and update the widget settings. */
				$instance['title'] = strip_tags( $new_instance['title'] );
				$instance['limit'] = $new_instance['limit'];
				$instance['no_upcoming_events'] = $new_instance['no_upcoming_events'];

				return $instance;
		}
	
		function form( $instance ) {				
			/* Set up default widget settings. */
			$defaults = array( 'title' => 'Upcoming Events', 'limit' => '5', 'no_upcoming_events' => false);
			$instance = wp_parse_args( (array) $instance, $defaults );
			$tribe_ecp = TribeEvents::instance();		
			include( $tribe_ecp->pluginPath . 'admin-views/widget-admin-list.php' );
		}
	}

	/* Add function to the widgets_ hook. */
	add_action( 'widgets_init', 'events_list_load_widgets', 90 );

	/* Function that registers widget. */
	function events_list_load_widgets() {
		register_widget( 'TribeEventsListWidget' );
		// load text domain after class registration
		load_plugin_textdomain( 'tribe-events-calendar', false, basename(dirname(dirname(__FILE__))) . '/lang/');
	}
}
