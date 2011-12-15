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
			$widget_ops = array('classname' => 'venue_widget', 'description' => __( 'Displays a list of upcoming events at a specific venue.', 'tribe-events-calendar-pro') );
			// Create the widget.
			$this->WP_Widget('venue-widget', __('Venue Widget', 'tribe-events-calendar-pro'), $widget_ops);
		}
		
		function widget($args, $instance) {
			extract($args);
			// Get all the upcoming events for this venue.
			$events = tribe_get_events( array( 'post_type' => TribeEvents::POSTTYPE, 'meta_key' => '_EventVenueID', 'meta_value' => $instance['venue_ID'], 'posts_per_page' => $instance['count'], 'eventDisplay' => 'upcoming') );
			// If there are events, or if the user has set to show if empty, display the widget.
			if ($instance['show_if_empty'] == true || ($instance['show_if_empty'] == false && count($events) > 0)) {
				echo $before_widget;
				$title = apply_filters('widget_title', empty($instance['title']) ? '&nbsp;' : $instance['title']);
				echo '<h3 class="widget-title">' . $title . '</h3>';
				echo '<div class="venue-widget-wrapper">';
				// Display the venue information.
				echo '<div class="venue-widget-venue">';
				echo '<div class="venue-widget-venue-name">' . tribe_get_venue($instance['venue_ID']) . '</div>';
				if (has_post_thumbnail($instance['venue_ID'])) {
					echo '<div class="venue-widget-thumbnail">' . get_the_post_thumbnail($instance['venue_ID'], 'related-event-thumbnail' ) . '</div>';
				}
				if (tribe_address_exists($instance['venue_ID'])) {
					$address = tribe_get_address($instance['venue_ID']);
					$city = tribe_get_city($instance['venue_ID']);
					$region = tribe_get_region($instance['venue_ID']);
					$zip = tribe_get_zip($instance['venue_ID']);
					$country = tribe_get_country($instance['venue_ID']);
					echo '<div class="venue-widget-address">';
					echo $address . '<br />';
					echo $city . ', ' . $region . ' ' . $zip .'<br />';
					echo $country;
					echo '</div>';
				}
				echo '</div>';
				echo '<hr />';
				// Display the events.
				if (count($events) == 0) {
					echo "No upcoming events.";
				}
				echo '<ul class="venue-widget-list">';
				foreach ($events as $event) {
					echo '<li>';
					echo '<a href="' . get_permalink($event) . '">';
					echo '<div class="venue-widget-title">' . get_the_title($event->ID) . '</div>';
					echo '</a>';
					echo '</li>';
				}
				echo '</ul>';
				echo '</div>';
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
		register_widget ('TribeVenueWidget');
	}
}