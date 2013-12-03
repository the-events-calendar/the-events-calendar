<?php
/*
Event Countdown Widget
*/
 
// Don't load directly.
if ( !defined('ABSPATH') ) { die('-1'); }
 
if( !class_exists( 'TribeCountdownWidget') ) {
	class TribeCountdownWidget extends WP_Widget {
 
		function TribeCountdownWidget() {
			$widget_ops = array( 'classname' => 'tribe-events-countdown-widget', 'description' => __( 'Displays the time remaining until a specified event.', 'tribe-events-calendar-pro' ) );
			$control_ops = array( 'id_base' => 'tribe-events-countdown-widget' );
			$this->WP_Widget( 'tribe-events-countdown-widget', __('Events Countdown', 'tribe-events-calendar-pro'), $widget_ops, $control_ops );
		}
 
		function widget( $args, $instance ) {
			extract( $args );
			extract( $instance );
			$title = apply_filters( 'widget_title', $title );
			wp_enqueue_script( 'tribe-events-countdown-widget', TribeEventsPro::instance()->pluginUrl .'resources/widget-countdown.js', array( 'jquery' ), apply_filters( 'tribe_events_pro_js_version', TribeEventsPro::VERSION ), true );
			// Get the timer data.
			$complete = '<h3 class="tribe-countdown-complete">' . $complete . '</h3>';
			$event_countdown_date = $this->get_output($event_ID, $complete, $show_seconds, $event_date);
			echo $before_widget;
			if ( !empty( $title ) ) echo $before_title.$title.$after_title;
			if ( !empty( $event_countdown_date ) ) echo $event_countdown_date;
			echo $after_widget;
		}
 
		function update( $new_instance, $old_instance ) {
			$instance = $old_instance;
			$instance['title'] = strip_tags( $new_instance['title'] );
			$event_data = explode( '|', $new_instance['event'] );
			$instance['event_date'] = $event_data[1];
			$instance['event_ID'] = $event_data[0];
			$instance['show_seconds'] = ( isset( $new_instance['show_seconds'] ) ? 1 : 0 );
			$instance['complete'] = $new_instance['complete'] == '' ? $old_instance['complete'] : $new_instance['complete'];
			return $instance;
		}
 
		function form( $instance ) {
			$defaults = array(
				'title' => '',
				'event_ID' => null,
				'event_date' => null,
				'show_seconds' => true,
				'complete' => 'Hooray!',
			);
			$instance = wp_parse_args( (array) $instance, $defaults);
			$events = tribe_get_events( array( 'eventDisplay' => 'upcoming', 'posts_per_page' => '-1' ) );
			include( TribeEventsPro::instance()->pluginPath . 'admin-views/widget-admin-countdown.php' );
		}
 
		function get_output($event_ID, $complete, $show_seconds, $event_date = null ) {
			$ret = $complete;
			
			ob_start();
			include TribeEventsTemplates::getTemplateHierarchy( 'pro/widgets/countdown-widget' );
			$hourformat = ob_get_clean();
			
			// Get the event start date.
			$startdate = tribe_is_recurring_event( $event_ID ) ? $event_date . ' ' . tribe_get_start_date( $event_ID, false, TribeDateUtils::DBTIMEFORMAT ) : tribe_get_start_date( $event_ID, false, TribeDateUtils::DBDATETIMEFORMAT );
			// Get the number of seconds remaining until the date in question.
			$seconds = strtotime( $startdate ) - current_time( 'timestamp' );
			if ( $seconds > 0 ) {
				$ret = $this->generate_countdown_output( $seconds, $complete, $hourformat, $event_ID, $event_date );
			}
			return $ret;
		}
 
		// Generate the hidden information to be passed to jQuery.
		function generate_countdown_output( $seconds, $complete, $hourformat, $event_ID, $event_date = null ) {
			$event = get_post( $event_ID );
			if ( !is_null( $event_date ) )
				$event->EventStartDate = $event_date;
			$link = tribe_get_event_link( $event );
			return '
			<div class="tribe-countdown-text"><a href="' .esc_url($link) . '">' . esc_attr($event->post_title) . '</a></div>
			<div class="tribe-countdown-timer">
				<span class="tribe-countdown-seconds">'.$seconds.'</span>
				<span class="tribe-countdown-format">'.$hourformat.'</span>
				'.$complete.'
			</div>';
		}
 
	}
 
	add_action('widgets_init', 'tribe_countdown_register_widget');
	function tribe_countdown_register_widget() {
		register_widget('TribeCountdownWidget');
	}
}
