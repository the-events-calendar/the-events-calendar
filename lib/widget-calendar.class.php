<?php

class TribeEventsMiniCalendarWidget extends WP_Widget {

	function __construct() {

		$widget_ops = array( 'classname'   => 'tribe_mini_calendar_widget',
		                     'description' => __( 'The events calendar mini calendar widget', 'tribe-events-calendar-pro' ) );

		parent::__construct( 'tribe-mini-calendar', __( 'Events Calendar', 'tribe-events-calendar-pro' ), $widget_ops );

		add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts' ) );

	}

	public function load_scripts( $hook ) {

		if ( $hook != 'widgets.php' )
			return;

		Tribe_Template_Factory::asset_package( 'select2' );
		wp_enqueue_script( 'calendar-widget-admin', TribeEventsPro::instance()->pluginUrl . 'resources/calendar-widget-admin.js', array(), apply_filters( 'tribe_events_pro_js_version', TribeEventsPro::VERSION ) );
	}

	function widget( $args, $instance ) {

		add_filter( 'tribe_events_list_show_ical_link', '__return_false' );

		echo $args['before_widget'];

		$defaults = array( 'title' => __( 'Events Calendar', 'tribe-events-calendar-pro' ), 'count' => 5, 'filters' => null, 'operand' => 'OR' );
		$instance = wp_parse_args( (array) $instance, $defaults );

		$tax_query = TribeEventsMiniCalendar::instance()->get_tax_query_from_widget_options( json_decode( $instance['filters'] ), $instance['operand'] );

		echo ( $instance['title'] ) ? $args['before_title'] . $instance['title'] . $args['after_title'] : '';

		$instance['tax_query'] = $tax_query;

		TribeEventsMiniCalendar::instance()->do_calendar( $instance );

		echo $args['after_widget'];

		remove_filter( 'tribe_events_list_show_ical_link', '__return_false' );

	}

	function update( $new_instance, $old_instance ) {
		$instance            = $old_instance;
		$instance['title']   = strip_tags( $new_instance['title'] );
		$instance['count']   = intval( strip_tags( $new_instance['count'] ) );
		$instance['operand'] = strip_tags( $new_instance['operand'] );
		$instance['filters'] = maybe_unserialize( $new_instance['filters'] );

		return $instance;
	}

	function form( $instance ) {
		$defaults = array( 'title' => __( 'Events Calendar', 'tribe-events-calendar-pro' ), 'layout' => "tall", 'count' => 5, 'operand' => 'OR', 'filters' => null );
		$instance = wp_parse_args( (array) $instance, $defaults );

		$taxonomies = get_object_taxonomies( TribeEvents::POSTTYPE, 'objects' );
		$taxonomies = array_reverse( $taxonomies );

		$ts = TribeEventsPro::instance();

		include $ts->pluginPath . 'admin-views/widget-calendar.php';
	}

}