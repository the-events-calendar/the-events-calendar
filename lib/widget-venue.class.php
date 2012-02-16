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
			$widget_ops = array('classname' => 'tribe_venue_widget', 'description' => __( 'Displays a list of upcoming events at a specific venue.', 'tribe-events-calendar-pro') );
			// Create the widget.
			$this->WP_Widget('venue-widget', __('Venue Widget', 'tribe-events-calendar-pro'), $widget_ops);
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
				echo '<h3 class="tribe_widget-title">' . $title . '</h3>';
				echo '<div class="tribe_venue-widget-wrapper">';
				// Display the venue information.
				echo '<div class="tribe_venue-widget-venue">';
				echo '<div class="tribe_venue-widget-venue-name">' . tribe_get_venue($venue_ID) . '</div>';
				if (has_post_thumbnail($venue_ID)) {
					echo '<div class="tribe_venue-widget-thumbnail">' . get_the_post_thumbnail($venue_ID, 'related-event-thumbnail' ) . '</div>';
				}
				if (tribe_address_exists($venue_ID)) {
					$address = tribe_get_address($venue_ID);
					$city = tribe_get_city($venue_ID);
					$region = tribe_get_region($venue_ID);
					$zip = tribe_get_zip($venue_ID);
					$country = tribe_get_country($venue_ID);
					echo '<div class="tribe_venue-widget-address">';
					echo $address . '<br />';
					echo $city . ', ' . $region . ' ' . $zip .'<br />';
					echo $country;
					echo '</div>';
				}
				echo '</div>';
				echo '<hr />';
				// Display the events.
				if (count($events) == 0) {
					_e('No upcoming events.', 'tribe-events-calendar-pro');
				}
				echo '<ul class="tribe_venue-widget-list">';
				foreach ($events as $event) {
					echo '<li>';
					echo '<a href="' . get_permalink($event) . '">';
					echo '<div class="tribe_venue-widget-title">' . get_the_title($event->ID) . '</div>';
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
		register_widget('TribeVenueWidget');
	}
}