<?php
/**
 * Event List Widget
 *
 * Creates a widget that displays the next upcoming x events
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class Tribe__Events__List_Widget extends WP_Widget {

	private static $limit = 5;
	public static $posts = array();

	/**
	 * Allows widgets extending this one to pass through their own unique name, ID base etc.
	 *
	 * @param string $id_base
	 * @param string $name
	 * @param array  $widget_options
	 * @param array  $control_options
	 */
	public function __construct( $id_base = '', $name = '', $widget_options = array(), $control_options = array() ) {
		$widget_options = array_merge(
			array(
				'classname'   => 'tribe-events-list-widget',
				'description' => __( 'A widget that displays upcoming events.', 'tribe-events-calendar' ),
			),
			$widget_options
		);

		$control_options = array_merge( array( 'id_base' => 'tribe-events-list-widget' ), $control_options );

		$id_base = empty( $id_base ) ? 'tribe-events-list-widget' : $id_base;
		$name    = empty( $name ) ? __( 'Events List', 'tribe-events-calendar' ) : $name;

		parent::__construct( $id_base, $name, $widget_options, $control_options );
	}

	/**
	 * The main widget output function.
	 *
	 * @param array $args
	 * @param array $instance
	 *
	 * @return string The widget output (html).
	 */
	public function widget( $args, $instance ) {
		return $this->widget_output( $args, $instance );
	}

	/**
	 * The main widget output function (called by the class's widget() function).
	 *
	 * @param array  $args
	 * @param array  $instance
	 * @param string $template_name The template name.
	 * @param string $subfolder     The subfolder where the template can be found.
	 * @param string $namespace     The namespace for the widget template stuff.
	 * @param string $pluginPath    The pluginpath so we can locate the template stuff.
	 */
	public function widget_output( $args, $instance, $template_name = 'widgets/list-widget' ) {
		global $wp_query, $tribe_ecp, $post;

		$instance = wp_parse_args(
			$instance, array(
				'limit' => self::$limit,
				'title' => '',
			)
		);

		/**
		 * @var $after_title
		 * @var $after_widget
		 * @var $before_title
		 * @var $before_widget
		 * @var $limit
		 * @var $no_upcoming_events
		 * @var $title
		 */
		extract( $args, EXTR_SKIP );
		extract( $instance, EXTR_SKIP );

		// Temporarily unset the tribe bar params so they don't apply
		$hold_tribe_bar_args = array();
		foreach ( $_REQUEST as $key => $value ) {
			if ( $value && strpos( $key, 'tribe-bar-' ) === 0 ) {
				$hold_tribe_bar_args[ $key ] = $value;
				unset( $_REQUEST[ $key ] );
			}
		}

		$title = apply_filters( 'widget_title', $title );

		self::$limit = absint( $limit );

		if ( ! function_exists( 'tribe_get_events' ) ) {
			return;
		}

		self::$posts = tribe_get_events(
			apply_filters(
				'tribe_events_list_widget_query_args', array(
					'eventDisplay'   => 'list',
					'posts_per_page' => self::$limit,
					'tribe_render_context' => 'widget',
				)
			)
		);

		// If no posts, and the don't show if no posts checked, let's bail
		if ( empty( self::$posts ) && $no_upcoming_events ) {
			return;
		}

		echo $before_widget;
		do_action( 'tribe_events_before_list_widget' );

		if ( $title ){
			do_action( 'tribe_events_list_widget_before_the_title' );
			echo $before_title . $title . $after_title;
			do_action( 'tribe_events_list_widget_after_the_title' );
		}

		// Include template file
		include Tribe__Events__Templates::getTemplateHierarchy( $template_name );
		do_action( 'tribe_events_after_list_widget' );

		echo $after_widget;
		wp_reset_query();

		// Reinstate the tribe bar params
		if ( ! empty( $hold_tribe_bar_args ) ) {
			foreach ( $hold_tribe_bar_args as $key => $value ) {
				$_REQUEST[ $key ] = $value;
			}
		}
	}

	/**
	 * The function for saving widget updates in the admin section.
	 *
	 * @param array $new_instance
	 * @param array $old_instance
	 *
	 * @return array The new widget settings.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags (if needed) and update the widget settings. */
		$instance['title']              = strip_tags( $new_instance['title'] );
		$instance['limit']              = $new_instance['limit'];
		$instance['no_upcoming_events'] = $new_instance['no_upcoming_events'];

		return $instance;
	}

	/**
	 * Output the admin form for the widget.
	 *
	 * @param array $instance
	 *
	 * @return string The output for the admin widget form.
	 */
	public function form( $instance ) {
		/* Set up default widget settings. */
		$defaults  = array(
			'title'              => __( 'Upcoming Events', 'tribe-events-calendar' ),
			'limit'              => '5',
			'no_upcoming_events' => false,
		);
		$instance  = wp_parse_args( (array) $instance, $defaults );
		$tribe_ecp = Tribe__Events__Main::instance();
		include( $tribe_ecp->pluginPath . 'src/admin-views/widget-admin-list.php' );
	}
}
