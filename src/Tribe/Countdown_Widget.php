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

			add_action( 'admin_enqueue_scripts', array( $this, 'load_assets' ) );
		}

		public function load_assets( $hook ) {
			if ( 'widgets.php' !== $hook ) {
				return;
			}

			Tribe__Events__Template_Factory::asset_package( 'select2' );
			wp_enqueue_script( 'tribe-admin-widget-countdown', tribe_events_pro_resource_url( 'admin-widget-countdown.js' ), array( 'jquery' ), apply_filters( 'tribe_events_pro_js_version', Tribe__Events__Pro__Main::VERSION ) );
		}

		public function update( $new_instance, $old_instance ) {
			$instance = $old_instance;
			$instance['title'] = strip_tags( $new_instance['title'] );
			$instance['show_seconds'] = ( isset( $new_instance['show_seconds'] ) ? 1 : 0 );
			if ( isset( $new_instance['type'] ) && in_array( $new_instance['type'], array( 'next-event', 'single-event' ) ) ){
				$instance['type'] = $new_instance['type'];
			} else {
				$instance['type'] = 'single-event';
			}
			$instance['complete'] = $new_instance['complete'] == '' ? $old_instance['complete'] : $new_instance['complete'];

			$instance['event_ID'] = $instance['event'] = absint( $new_instance['event'] );
			$instance['event_date'] = $event_data[1];

			return $instance;
		}

		public function form( $instance ) {
			$defaults = array(
				'title' => '',
				'type' => 'single-event',
				'event' => null,
				'show_seconds' => true,
				'complete' => esc_attr__( 'Hooray!', 'tribe-events-calendar-pro' ),

				// Legacy Elements
				'event_ID' => null,
				'event_date' => null,
			);

			$instance = wp_parse_args( (array) $instance, $defaults );
			if ( empty( $instance['event'] ) ){
				$instance['event'] = $instance['event_ID'];
			}

			$limit = apply_filters( 'tribe_events_pro_countdown_widget_limit', 250 );
			$paged = apply_filters( 'tribe_events_pro_countdown_widget_paged', 1 );

			$events = tribe_get_events( array(
				'eventDisplay' => 'list',
				'posts_per_page' => $limit,
				'paged' => $paged,
			) );

			if ( is_numeric( $instance['event'] ) ){
				$event = get_post( $instance['event'] );
				if ( $event instanceof WP_Post && ! in_array( $event->ID, wp_list_pluck( $events, 'ID' ) ) ){
					$event->EventStartDate = tribe_get_start_date( $event->ID, false, Tribe__Date_Utils::DBDATETIMEFORMAT );
					$event->EventEndDate = tribe_get_end_date( $event->ID, false, Tribe__Date_Utils::DBDATETIMEFORMAT );
					$events = array_merge( array( $event ), $events );
				}
			}

			include( Tribe__Events__Pro__Main::instance()->pluginPath . 'src/admin-views/widget-admin-countdown.php' );
		}

		public function widget( $args, $instance ) {
			$defaults = array(
				'title' => null,
				'type' => 'single-event',
				'event' => null,
				'show_seconds' => true,
				'complete' => esc_attr__( 'Hooray!', 'tribe-events-calendar-pro' ),

				// Legacy Elements
				'event_ID' => null,
				'event_date' => null,
			);

			$instance = wp_parse_args( (array) $instance, $defaults );
			wp_enqueue_script( 'tribe-events-countdown-widget', tribe_events_pro_resource_url( 'widget-countdown.js' ), array( 'jquery' ), apply_filters( 'tribe_events_pro_js_version', Tribe__Events__Pro__Main::VERSION ), true );

			// Setup required variables
			if ( empty( $instance['event'] ) ){
				$instance['event'] = $instance['event_ID'];
			}

			$title = apply_filters( 'widget_title', $instance['title'] );

			if ( $instance['complete'] ) {
				$instance['complete'] = '<h3 class="tribe-countdown-complete">' . $instance['complete'] . '</h3>';
			}

			echo $args['before_widget'];
			if ( ! empty( $title ) ) {
				echo $args['before_title'] . $title . $args['after_title'];
			}

			echo $this->get_output( $instance );

			echo $args['after_widget'];
		}

		/**
		 * Get the Output of the Widget based on the Instance from the Database
		 *
		 * @param  array $instance     The Array of arguments that will build the HTML
		 * @param  null $deprecated    Deprecated Argument
		 * @param  null $deprecated_   Deprecated Argument
		 * @param  null $deprecated__  Deprecated Argument
		 * @return string
		 */
		public function get_output( $instance, $deprecated = null, $deprecated_ = null, $deprecated__ = null ) {
			if ( 'next-event' === $instance['type'] ) {
				$event = tribe_get_events( array(
					'eventDisplay' => 'list',
					'posts_per_page' => 1,
				) );
				$event = reset( $event );
			} else {
				$event = get_post( $instance['event'] );
			}
			$ret = $instance['complete'];
			$show_seconds = $instance['show_seconds'];

			ob_start();
			include Tribe__Events__Templates::getTemplateHierarchy( 'pro/widgets/countdown-widget' );
			$hourformat = ob_get_clean();

			if ( $event instanceof WP_Post ) {
				// Get the event start date.
				$startdate = tribe_get_start_date( $event->ID, false, Tribe__Date_Utils::DBDATETIMEFORMAT );

				// Get the number of seconds remaining until the date in question.
				$seconds = strtotime( $startdate ) - current_time( 'timestamp' );
			} else {
				$seconds = 0;
			}

			if ( $seconds > 0 ) {
				$ret = $this->generate_countdown_output( $seconds, $instance['complete'], $hourformat, $event );
			}

			return $ret;
		}

		/**
		 * Generate the hidden information to be passed to jQuery
		 *
		 * @param  int $seconds             The amount of seconds to show
		 * @param  string $complete         HTML for when the countdown is over
		 * @param  string $hourformat       HTML from View
		 * @param  WP_Post|int|null $event  Event Instance of WP_Post
		 * @param  null $deprecated         Deprecated Argument
		 * @return string
		 */
		public function generate_countdown_output( $seconds, $complete, $hourformat, $event, $deprecated = null ) {
			$event = get_post( $event );
			$link = tribe_get_event_link( $event );

			$output = '';

			if ( $event ) {
				$output .= '<div class="tribe-countdown-text"><a href="' . esc_url( $link ) . '">' . esc_attr( $event->post_title ) . '</a></div>';
			}

			return $output . '
			<div class="tribe-countdown-timer">
				<span class="tribe-countdown-seconds">' . $seconds . '</span>
				<span class="tribe-countdown-format">' . $hourformat . '</span>
				' . $complete . '
			</div>';
		}

	}

}
