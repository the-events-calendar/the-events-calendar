<?php
/*
Event Countdown Widget
*/

// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( ! class_exists( 'Tribe__Events__Pro__Countdown_Widget' ) ) {
	class Tribe__Events__Pro__Countdown_Widget extends WP_Widget {

		public function __construct() {
			$widget_ops  = array(
				'classname'   => 'tribe-events-countdown-widget',
				'description' => __( 'Displays the time remaining until a specified event.', 'tribe-events-calendar-pro' ),
			);
			$control_ops = array( 'id_base' => 'tribe-events-countdown-widget' );
			parent::__construct( 'tribe-events-countdown-widget', __( 'Events Countdown', 'tribe-events-calendar-pro' ), $widget_ops, $control_ops );
		}

		public function widget( $args, $instance ) {
			$title = empty( $instance['title'] ) ? null : $instance['title'];
			$event_date = empty( $instance['event_date'] ) ? null : $instance['event_date'];
			$event_ID = empty( $instance['event_ID'] ) ? null : $instance['event_ID'];
			$show_seconds = empty( $instance['show_seconds'] ) ? null : $instance['show_seconds'];
			$complete = empty( $instance['complete'] ) ? null : $instance['complete'];
			$event_countdown_date = null;

			$title = apply_filters( 'widget_title', $title );
			wp_enqueue_script( 'tribe-events-countdown-widget', tribe_events_pro_resource_url( 'widget-countdown.js' ), array( 'jquery' ), apply_filters( 'tribe_events_pro_js_version', Tribe__Events__Pro__Main::VERSION ), true );
			// Get the timer data.

			if ( $complete ) {
				$complete = '<h3 class="tribe-countdown-complete">' . $complete . '</h3>';
			}

			if ( $event_ID ) {
				$event_countdown_date = $this->get_output( $event_ID, $complete, $show_seconds, $event_date );
			}

			echo $args['before_widget'];
			if ( ! empty( $title ) ) {
				echo $args['before_title'] . $title . $args['after_title'];
			}

			if ( ! empty( $event_countdown_date ) ) {
				echo $event_countdown_date;
			}
			echo $args['after_widget'];
		}

		public function update( $new_instance, $old_instance ) {
			$instance = $old_instance;
			$instance['title'] = strip_tags( $new_instance['title'] );
			$event_data = explode( '|', $new_instance['event'] );
			$instance['event_date'] = $event_data[1];
			$instance['event_ID'] = $event_data[0];
			$instance['show_seconds'] = ( isset( $new_instance['show_seconds'] ) ? 1 : 0 );
			$instance['complete'] = $new_instance['complete'] == '' ? $old_instance['complete'] : $new_instance['complete'];

			return $instance;
		}

		public function form( $instance ) {
			$defaults = array(
				'title' => '',
				'event_ID' => null,
				'event_date' => null,
				'show_seconds' => true,
				'complete' => 'Hooray!',
			);

			$instance = wp_parse_args( (array) $instance, $defaults );
			$limit = apply_filters( 'tribe_events_pro_countdown_widget_limit', 250 );
			$paged = apply_filters( 'tribe_events_pro_countdown_widget_paged', 1 );

			$events = tribe_get_events( array(
				'eventDisplay' => 'list',
				'posts_per_page' => $limit,
				'paged' => $paged,
			) );

			include( Tribe__Events__Pro__Main::instance()->pluginPath . 'src/admin-views/widget-admin-countdown.php' );
		}

		public function get_output( $event_ID, $complete, $show_seconds, $event_date = null ) {
			$ret = $complete;

			ob_start();
			include Tribe__Events__Templates::getTemplateHierarchy( 'pro/widgets/countdown-widget' );
			$hourformat = ob_get_clean();

			// Get the event start date.
			$startdate = tribe_is_recurring_event( $event_ID ) ? $event_date . ' ' . tribe_get_start_date( $event_ID, false, Tribe__Events__Date_Utils::DBTIMEFORMAT ) : tribe_get_start_date( $event_ID, false, Tribe__Events__Date_Utils::DBDATETIMEFORMAT );
			// Get the number of seconds remaining until the date in question.
			$seconds = strtotime( $startdate ) - current_time( 'timestamp' );
			if ( $seconds > 0 ) {
				$ret = $this->generate_countdown_output( $seconds, $complete, $hourformat, $event_ID, $event_date );
			}

			return $ret;
		}

		/**
		 * Generate the hidden information to be passed to jQuery.
		 */
		public function generate_countdown_output( $seconds, $complete, $hourformat, $event_ID, $event_date = null ) {
			$event = get_post( $event_ID );
			if ( ! is_null( $event_date ) ) {
				$event->EventStartDate = $event_date;
			}
			$link = tribe_get_event_link( $event );

			return '
			<div class="tribe-countdown-text"><a href="' . esc_url( $link ) . '">' . esc_attr( $event->post_title ) . '</a></div>
			<div class="tribe-countdown-timer">
				<span class="tribe-countdown-seconds">'.$seconds.'</span>
				<span class="tribe-countdown-format">'.$hourformat.'</span>
				'.$complete.'
			</div>';
		}

	}

}
