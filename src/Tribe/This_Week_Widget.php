<?php
/**
 * This Week Event Widget
 */
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class Tribe__Events__Pro__This_Week_Widget extends WP_Widget {

	/**
	 *  This Week Widget - Construct
	 */
	public function __construct() {
		// Widget settings.
		$widget_ops = array(
			'classname'   => 'tribe-this-week-events-widget',
			'description' => __( 'Displays events by day for the week.', 'tribe-events-calendar-pro' ),
		);
		// Create the widget.
		parent::__construct( 'tribe-this-week-events-widget', __( 'This Week Events', 'tribe-events-calendar-pro' ), $widget_ops );

		add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts' ) );

	}

	/**
	 * @param $hook
	 */
	public function load_scripts( $hook ) {

		if ( 'widgets.php' != $hook ) {
			return;
		}

		//JS for Taxonomy Filter Select
		Tribe__Events__Template_Factory::asset_package( 'select2' );
		wp_enqueue_script( 'calendar-widget-admin', tribe_events_pro_resource_url( 'calendar-widget-admin.js' ), array(), apply_filters( 'tribe_events_pro_js_version', Tribe__Events__Pro__Main::VERSION ) );

		//Need for Customizer and to prevent errors in Widgets Section with Color Picker
		wp_enqueue_script( 'underscore' );

		//Colorpicker
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
	}

	/**
	 * @param $args
	 * @param $instance
	 */
	public function widget( $args, $instance ) {
		// Initialize defaults. When the widget is added via the Customizer, the widget is rendered
		// prior to being saved and the instance is empty. This ensures that $instance holds the
		// defaults so the behavior is expected and doesn't throw notices.
		$instance = $this->instance_defaults( $instance );

		//Disable Tooltips
		$ecp = Tribe__Events__Pro__Main::instance();
		$tooltip_status = $ecp->recurring_info_tooltip_status();
		$ecp->disable_recurring_info_tooltip();

		//Check If a Taxonomy is set
		if ( ! empty( $instance['raw_filters'] ) || isset( $instance['filters'] ) ) {
			$filters = isset( $instance['raw_filters'] ) ? $instance['raw_filters'] : json_decode( $instance['filters'] );
		} else {
			$filters = null;
		}

		//Prepare Categories for Query
		$tax_query = Tribe__Events__Pro__Widgets::form_tax_query( $filters, $instance['operand'] );

		//Use Date to find start of week if provided in shortcode
		$start_date = isset( $instance['start_date'] ) ? $instance['start_date'] : null;

		//Use Date to find start of week if provided in shortcode
		$week_offset = isset( $instance['week_offset'] ) ? $instance['week_offset'] : null;

		//Array of Variables to use for Data Attributes and
		$this_week_query_vars['start_date'] = tribe_get_this_week_first_week_day( $start_date, $week_offset );
		$this_week_query_vars['end_date'] = tribe_get_this_week_last_week_day( $this_week_query_vars['start_date'] );
		$this_week_query_vars['count'] = $instance['count'];
		$this_week_query_vars['layout'] = $instance['layout'];
		$this_week_query_vars['tax_query'] = $tax_query;
		$this_week_query_vars['hide_weekends'] = isset( $instance['hide_weekends'] ) ? $instance['hide_weekends'] : false;

		//Setup Variables for Template
		$this_week_template_vars = Tribe__Events__Pro__This_Week::this_week_template_vars( $this_week_query_vars );

		//Setup Attributes for Ajax
		$this_week_data_attrs = Tribe__Events__Pro__This_Week::this_week_data_attr( $this_week_query_vars );

		//Setups This Week Object for Each Day
		$week_days = Tribe__Events__Pro__This_Week::this_week_query( $this_week_query_vars );

		echo $args['before_widget'];

		do_action( 'tribe_events_this_week_widget_before_the_title' );

		echo ( ! empty( $instance['title'] ) ) ? $args['before_title'] . $instance['title'] . $args['after_title'] : '';

		do_action( 'tribe_events_this_week_widget_after_the_title' );

		include Tribe__Events__Templates::getTemplateHierarchy( 'pro/widgets/this-week-widget.php' );

		echo $args['after_widget'];

		// Re-enable recurring event info
		if ( $tooltip_status ) {
			$ecp->enable_recurring_info_tooltip();
		}

		wp_reset_postdata();
	}

	/**
	 *  Include the file for the administration view of the widget.
	 *
	 * @param $instance
	 */
	public function form( $instance ) {
		$this->instance_defaults( $instance );

		$taxonomies = get_object_taxonomies( Tribe__Events__Main::POSTTYPE, 'objects' );
		$taxonomies = array_reverse( $taxonomies );

		$instance = $this->instance;
		include( Tribe__Events__Pro__Main::instance()->pluginPath . 'src/admin-views/widget-admin-this-week.php' );
	}

	/**
	 * @param $instance
	 */
	protected function instance_defaults( $instance ) {
		$this->instance = wp_parse_args( (array) $instance, array(
			'title'             => '',
			'layout'            => 'vertical',
			'highlight_color'   => '',
			'count'             => 3,
			'widget_id'         => 3,
			'filters'           => '',
			'operand'           => 'OR',
			'start_date'        => '',
			'week_offset'       => '',
			'hide_weekends'     => false,
			'instance'           => &$this->instance,
		) );

		return $this->instance;
	}

	/**
	 * Function allowing updating of widget information.
	 *
	 * @param $new_instance
	 * @param $old_instance
	 *
	 * @return mixed
	 */
	public function update( $new_instance, $old_instance ) {

		$instance['title']               = sanitize_text_field( $new_instance['title'] );
		$instance['layout']              = sanitize_text_field( $new_instance['layout'] );
		$instance['highlight_color']     = sanitize_text_field( $new_instance['highlight_color'] );
		$instance['count']               = absint( $new_instance['count'] );
		$instance['filters']             = maybe_unserialize( sanitize_text_field( $new_instance['filters'] ) );
		$instance['operand']             = sanitize_text_field( $new_instance['operand'] );

		return $instance;
	}
}
