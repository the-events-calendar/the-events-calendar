<?php
/*
Event Countdown Widget
*/
 
// Don't load directly.
if ( !defined('ABSPATH') ) { die('-1'); }
 
if( !class_exists( 'TribeCountdownWidget') ) {
	class TribeCountdownWidget extends WP_Widget {
 
		function TribeCountdownWidget() {
			$widget_ops = array( 'classname' => 'tribe_countdown_widget', 'description' => __( 'Displays the time remaining until a specified event.', 'tribe-events-calendar-pro' ) );
			$control_ops = array( 'id_base' => 'tribe_countdown_widget' );
			$this->WP_Widget( 'tribe_countdown_widget', __('Countdown Widget', 'tribe-events-calendar-pro'), $widget_ops, $control_ops );
		}
 
		function widget( $args, $instance ) {
			extract( $args );
			extract( $instance );
			$title = apply_filters( 'widget_title', $title );
			wp_enqueue_script( 'tribe_countdown_widget', TribeEventsPro::instance()->pluginUrl .'resources/widget-countdown.js', array( 'jquery' ), false, true );
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
			if ($show_seconds) {
				$hourformat = '
				<div class="tribe-countdown-timer tribe-clearfix">
					<div class="tribe-countdown-days tribe-countdown-number">DD<br />
						<span class="tribe-countdown-under">'.__('days', 'tribe-events-calendar-pro').'</span>
					</div>
					<div class="tribe-countdown-colon">:</div>
					<div class="tribe-countdown-hours tribe-countdown-number">HH<br />
						<span class="tribe-countdown-under">'.__('hours', 'tribe-events-calendar-pro').'</span>
					</div>
					<div class="tribe-countdown-colon">:</div>
					<div class="tribe-countdown-minutes tribe-countdown-number">MM<br />
						<span class="tribe-countdown-under">'.__('min', 'tribe-events-calendar-pro').'</span>
					</div>
					<div class="tribe-countdown-colon">:</div>
					<div class="tribe-countdown-seconds tribe-countdown-number tribe-countdown-right">SS<br />
						<span class="tribe-countdown-under">'.__('sec', 'tribe-events-calendar-pro').'</span>
					</div>
				</div>';
			} else {
				$hourformat = 'dd days hh:mm';
				$hourformat = '
				<div class="tribe-countdown-timer">
					<div class="tribe-countdown-days tribe-countdown-number">DD<br />
						<span class="tribe-countdown-under">'.__('days', 'tribe-events-calendar-pro').'</span>
					</div>
					<div class="tribe-countdown-colon">:</div>
					<div class="tribe-countdown-hours tribe-countdown-number">HH<br />
						<span class="tribe-countdown-under">'.__('hours', 'tribe-events-calendar-pro').'</span>
					</div>
					<div class="tribe-countdown-colon">:</div>
					<div class="tribe-countdown-minutes tribe-countdown-number tribe-countdown-right">MM<br />
						<span class="tribe-countdown-under">'.__('min', 'tribe-events-calendar-pro').'</span>
					</div>
				</div>';
			}
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
			$link = tribe_is_recurring_event( $event ) ? TribeEvents::addDateToRecurringEvents( tribe_get_event_link($event_ID), $event ) : tribe_get_event_link($event_ID) ;
			return '
			<div class="tribe-countdown-text">'.__('Counting down to: ', 'tribe-events-calendar-pro').'<br /><a href="' .esc_url($link) . '">' . esc_attr($event->post_title) . '</a></div>
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
