<?php

class Tribe__Events__Pro__Mini_Calendar_Widget extends WP_Widget {

	public function __construct() {
		$widget_ops = array(
			'classname'   => 'tribe_mini_calendar_widget',
			'description' => __( 'The events calendar mini calendar widget', 'tribe-events-calendar-pro' ),
		);

		parent::__construct( 'tribe-mini-calendar', __( 'Events Calendar', 'tribe-events-calendar-pro' ), $widget_ops );

		add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts' ) );
	}

	public function load_scripts( $hook ) {

		if ( $hook != 'widgets.php' ) {
			return;
		}

		Tribe__Events__Template_Factory::asset_package( 'select2' );
		wp_enqueue_script( 'calendar-widget-admin', tribe_events_pro_resource_url( 'calendar-widget-admin.js' ), array(), apply_filters( 'tribe_events_pro_js_version', Tribe__Events__Pro__Main::VERSION ) );
	}

	public function widget( $args, $instance ) {
		$ecp = Tribe__Events__Pro__Main::instance();
		$tooltip_status = $ecp->recurring_info_tooltip_status();
		$ecp->disable_recurring_info_tooltip();

		add_filter( 'tribe_events_list_show_ical_link', '__return_false' );

		echo $args['before_widget'];

		$defaults = array(
			'title'   => __( 'Events Calendar', 'tribe-events-calendar-pro' ),
			'count'   => 5,
			'filters' => null,
			'operand' => 'OR',
		);
		$instance = wp_parse_args( (array) $instance, $defaults );

		$filters   = isset( $instance['raw_filters'] ) ? $instance['raw_filters'] : json_decode( $instance['filters'] );
		$tax_query = Tribe__Events__Pro__Widgets::form_tax_query( $filters, $instance['operand'] );

		do_action( 'tribe_events_mini_cal_before_the_title' );

		echo ( $instance['title'] ) ? $args['before_title'] . $instance['title'] . $args['after_title'] : '';

		do_action( 'tribe_events_mini_cal_after_the_title' );

		$instance['tax_query'] = $tax_query;

		Tribe__Events__Pro__Mini_Calendar::instance()->do_calendar( $instance );

		echo $args['after_widget'];

		remove_filter( 'tribe_events_list_show_ical_link', '__return_false' );

		if ( $tooltip_status ) {
			$ecp->enable_recurring_info_tooltip();
		}

	}

	public function update( $new_instance, $old_instance ) {
		$instance            = $old_instance;
		$instance['title']   = strip_tags( $new_instance['title'] );
		$instance['count']   = intval( strip_tags( $new_instance['count'] ) );
		$instance['operand'] = strip_tags( $new_instance['operand'] );
		$instance['filters'] = maybe_unserialize( $new_instance['filters'] );

		return $instance;
	}

	public function form( $instance ) {
		$defaults = array(
			'title'   => __( 'Events Calendar', 'tribe-events-calendar-pro' ),
			'layout'  => 'tall',
			'count'   => 5,
			'operand' => 'OR',
			'filters' => null,
		);
		$instance = wp_parse_args( (array) $instance, $defaults );

		$taxonomies = get_object_taxonomies( Tribe__Events__Main::POSTTYPE, 'objects' );
		$taxonomies = array_reverse( $taxonomies );

		$ts = Tribe__Events__Pro__Main::instance();

		include $ts->pluginPath . 'src/admin-views/widget-calendar.php';
	}
}
