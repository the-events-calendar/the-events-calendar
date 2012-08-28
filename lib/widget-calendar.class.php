<?php
/**
 * Events Calendar widget class
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if( !class_exists( 'TribeEventsCalendarWidget') ) {

	class TribeEventsCalendarWidget extends WP_Widget {
		
		function TribeEventsCalendarWidget() {
			$widget_ops = array('classname' => 'events_calendar_widget', 'description' => __( 'A calendar of your events') );
			$this->WP_Widget('calendar', __('Events Calendar'), $widget_ops);

			add_action('wp_enqueue_scripts', array($this, 'maybe_load_scripts') );

		}

		function maybe_load_scripts() {

			if ( is_active_widget( false, false, $this->id_base ) ) {

				$widget_data = array( "ajaxurl" => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ) );

				wp_enqueue_script( 'tribe-events-mini-calendar', TribeEventsPro::instance()->pluginUrl . 'resources/events-mini-ajax.js' );
				wp_enqueue_style( 'tribe-events-mini-calendar', TribeEventsPro::instance()->pluginUrl . 'resources/events-mini-ajax.css' );
				wp_localize_script( 'tribe-events-mini-calendar', 'TribeMiniCalendar', $widget_data );
			}
		}

		function widget( $args, $instance ) {

			extract($args);
			$title = apply_filters('widget_title', empty($instance['title']) ? '&nbsp;' : $instance['title']);
			echo $before_widget;
			if ( $title ) { echo $before_title . $title . $after_title; }
			echo '<div id="calendar_wrap">';
			tribe_calendar_mini_grid();
			echo '</div>';
			echo $after_widget;
		}
	
		function update( $new_instance, $old_instance ) {
			$instance = $old_instance;
			$instance['title'] = strip_tags($new_instance['title']);

			return $instance;
		}

		function form( $instance ) {
			$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
			$tribe_ecp = TribeEvents::instance();		
			require_once( TribeEventsPro::instance()->pluginPath . 'admin-views/widget-admin-calendar.php' );
		}
	
	}

	/* Add function to the widgets_ hook. */
	// hook is commented out until development is finished, allows WP's default calendar widget to work
	add_action( 'widgets_init', 'events_calendar_load_widgets',100);
	//add_action( 'widgets_init', 'get_calendar_custom' );

	//function get_calendar_custom(){echo "hi";}

	/* Function that registers widget. */
	function events_calendar_load_widgets() {
		register_widget( 'TribeEventsCalendarWidget' );
		// load text domain after class registration
		load_plugin_textdomain( 'tribe-events-calendar-pro', false, basename(dirname(dirname(__FILE__))) . '/lang/');
	}

	// AJAX functionality for the mini calendar
	add_action( 'wp_ajax_calendar-mini', 'tribe_calendar_mini_ajax_call' );
	add_action( 'wp_ajax_nopriv_calendar-mini', 'tribe_calendar_mini_ajax_call' );
	add_action( 'pre_get_posts', 'tribe_calendar_mini_ajax_call_set_date', 9 );

	function tribe_calendar_mini_ajax_call_set_date( $query ) {
		if ( defined( "DOING_AJAX" ) && DOING_AJAX && isset( $_POST["eventDate"] ) && isset( $_POST["action"] ) && $_POST["action"] == "calendar-mini" ) {
			global $wp_query;
			$date                              = $_POST["eventDate"] . '-01';
			$query->query_vars['eventDate']    = $date;
			$wp_query->query_vars['eventDate'] = $date;

			$query->query_vars['suppress_filters'] = false;

			add_filter( 'parse_tribe_event_query', array( 'TribeEventsQuery', 'setupQueryArgs' ) );
			add_filter( 'parse_tribe_event_query', array( 'TribeEventsQuery', 'setArgsFromDisplayType' ) );

			apply_filters( 'parse_tribe_event_query', $query );

			add_filter( 'posts_join', array( 'TribeEventsQuery', 'setupJoins' ), 10, 2 );
			add_filter( 'posts_where', array( 'TribeEventsQuery', 'addEventConditions' ), 10, 2 );
			add_filter( 'posts_fields', array( 'TribeEventsQuery', 'setupFields' ) );
			add_filter( 'posts_groupby', array( 'TribeEventsQuery', 'addStartDateToGroupBy' ) );
			add_filter( 'posts_orderby', array( 'TribeEventsQuery', 'dateOrderBy' ), 10, 2 );
		}

		return $query;
	}

	function tribe_calendar_mini_ajax_call() {
		if ( isset( $_POST["eventDate"] ) && $_POST["eventDate"] ) {
			$date = $_POST["eventDate"];
			header( "Content-Type: text/html" );
			tribe_calendar_mini_grid( $date );
		}
		die();
	}


}
?>