<?php
/**
 * Event List Widget - Premium version
 *
 * Creates a widget that displays the next upcoming x events
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if( !class_exists( 'TribeEventsAdvancedListWidget' ) ) {
	class TribeEventsAdvancedListWidget extends TribeEventsListWidget {

		static $params = array();

		function TribeEventsAdvancedListWidget() {
			/* Widget settings. */
			$widget_ops = array( 'classname' => 'tribe-events-adv-list-widget', 'description' => __( 'A widget that displays the next upcoming x events.', 'tribe-events-calendar-pro' ) );

			/* Widget control settings. */
			$control_ops = array( 'id_base' => 'tribe-events-adv-list-widget' );

			/* Create the widget. */
			$this->WP_Widget( 'tribe-events-adv-list-widget', __( 'Events List', 'tribe-events-calendar-pro' ), $widget_ops, $control_ops );

		}

		function widget( $args, $instance ) {
			// Use parent's output function with the premium template.
			self::$params = $instance;
			return parent::widget_output( $args, $instance, 'pro/widgets/list-widget' );
		}

		function update( $new_instance, $old_instance ) {
			$instance = parent::update( $new_instance, $old_instance );

			/* Process remaining options. */
			/* Strip tags (if needed) and update the widget settings. */
			$instance['venue']     = $new_instance['venue'];
			$instance['country']   = $new_instance['country'];
			$instance['address']   = $new_instance['address'];
			$instance['city']      = $new_instance['city'];
			$instance['region']    = $new_instance['region'];
			$instance['zip']       = $new_instance['zip'];
			$instance['phone']     = $new_instance['phone'];
			$instance['cost']      = $new_instance['cost'];
			$instance['category']  = $new_instance['category'];
			$instance['organizer'] = $new_instance['organizer'];
			return $instance;
		}

		function form( $instance ) {
			/* Set up default widget settings. */
			$defaults = array( 'title' => __( 'Upcoming Events', 'tribe-events-calendar-pro' ), 'limit' => '5', 'no_upcoming_events' => false, 'venue' => false, 'country' => true, 'address' => false, 'city' => true, 'region' => true, 'zip' => false, 'phone' => false, 'cost' => false,'category' => false, 'organizer' => false);
			$instance = wp_parse_args( (array) $instance, $defaults );
			include( TribeEventsPro::instance()->pluginPath . 'admin-views/widget-admin-advanced-list.php' );
		}

	}
}
