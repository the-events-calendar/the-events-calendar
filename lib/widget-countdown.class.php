<?php
/*
Event Countdown Widget
*/

// Don't load directly.
if ( !defined('ABSPATH') ) { die('-1'); }

if( !class_exists( 'TribeCountdownWidget') ) {
	class TribeCountdownWidget extends WP_Widget {

		function TribeCountdownWidget() {
			$widget_ops = array( 'classname' => 'countdown_widget', 'description' => __( 'Displays the time remaining until a specified event.', 'tribe-events-calendar-pro' ) );
			$control_ops = array( 'id_base' => 'countdown_widget' );
			$this->WP_Widget( 'countdown_widget', __('Countdown Widget', 'tribe-events-calendar-pro'), $widget_ops, $control_ops );

			wp_enqueue_script( $control_ops['id_base'], TribeEventsPro::instance()->pluginUrl .'lib/widget-countdown.js', array( 'jquery' ), false, true );
			// Add the styles for hiding the info passed to jQuery.
			add_action( 'wp_enqueue_scripts', array( $this, 'custom_css' ) );
		}

		// Hides the necessary elements to be passed to Javascript.
		function custom_css() {
			?>
			<style type="text/css">
			.countdown-timer span.seconds, .countdown-timer span.format, .countdown-timer span.complete {
				display: none;
			}
			</style>
			<?php
		}

		function widget( $args, $instance ) {
			extract( $args );
			extract( $instance );
			$title = apply_filters( 'widget_title', empty( $title ) ? '' : $title );
			// Get the timer data.
			$eventdate = $this->get_output($instance['event_ID'], $instance['complete'], $instance['show_seconds']);
			echo $before_widget;
			if ( !empty( $title ) ) { echo $before_title . $title . $after_title; }
			if ( !empty( $eventdate ) ) { echo $eventdate; }
			echo $after_widget;
		}

		function update( $new_instance, $old_instance ) {
			$instance = $old_instance;
			$instance['title'] = strip_tags( $new_instance['title'] );
			$instance['event_ID'] = $new_instance['event_ID'];
			$instance['show_seconds'] = ( isset( $new_instance['show_seconds'] ) ? 1 : 0 );
			$instance['complete'] = $new_instance['complete'];
			return $instance;
		}

		function form( $instance ) {
			$defaults = array(
				'title' => '',
				'event_ID' => null,
				'show_seconds' => true,
				'complete' => 'Hooray!',
				);
			$instance = wp_parse_args( (array) $instance, $defaults);
			$events = get_posts( array( 'post_type' => TribeEvents::POSTTYPE, 'orderby' => 'title', 'nopaging' => true ) );
			include( TribeEventsPro::instance()->pluginPath . 'admin-views/widget-admin-countdown.php' );
		}

		function get_output($event_ID, $complete, $show_seconds) {
			$ret = $complete;
			if ($show_seconds) {
				$hourformat = "dd d hh h mm m ss s";
			} else {
				$hourformat = "dd d hh h mm m";
			}
			// Get the event start date.
			$startdate = tribe_get_start_date($event_ID, false, 'Y-m-d H:i:s');
			// Get the number of seconds remaining until the date in question.
			$seconds = strtotime( $startdate) - strtotime( 'now' );
			if ( $seconds > 0 ) {
				$ret = $this->generate_countdown_output( $seconds, $complete, $hourformat );
			}
			return $ret;
		}

		// Generate the hidden information to be passed to jQuery.
		function generate_countdown_output( $seconds, $complete, $hourformat ) {
			return "<div class=\"countdown-timer\"><span class=\"seconds\">$seconds</span><span class=\"format\">$hourformat</span><span class=\"complete\">$complete</span></div>";
		}

	}

	add_action('widgets_init', 'tribe_countdown_register_widget');
	function tribe_countdown_register_widget() {
		register_widget ('TribeCountdownWidget');
	}
}