<?php
/**
 * Event List Widget - Premium version
 *
 * Creates a widget that displays the next upcoming x events
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class Tribe__Events__Pro__Advanced_List_Widget extends Tribe__Events__List_Widget {
	/**
	 * @var array
	 */
	public $instance = array();


	public function __construct() {
		$widget_ops = array(
			'classname'   => 'tribe-events-adv-list-widget',
			'description' => __( 'A widget that displays the next upcoming x events.', 'tribe-events-calendar-pro' ),
		);

		$control_ops = array( 'id_base' => 'tribe-events-adv-list-widget' );

		parent::__construct( 'tribe-events-adv-list-widget', __( 'Events List', 'tribe-events-calendar-pro' ), $widget_ops, $control_ops );
		add_filter( 'tribe_events_list_widget_query_args', array( $this, 'taxonomy_filters' ) );

		// Do not enqueue if the widget is inactive
		if ( is_active_widget( false, false, $this->id_base, true ) ) {
			add_action( 'init', array( $this, 'enqueue_stylesheet' ), 100 );
		}
	}

	/**
	 * If the widget is active then enqueue our stylesheet.
	 */
	public function enqueue_stylesheet() {
		// Load the calendar widget CSS (the list widget inherits much of the same)
		Tribe__Events__Pro__Widgets::enqueue_calendar_widget_styles();
	}

	public function taxonomy_filters( $query ) {
		if ( empty( $this->instance ) ) {
			return $query;
		}

		$filters   = isset( $this->instance['raw_filters'] ) ? $this->instance['raw_filters'] : json_decode( $this->instance['filters'] );
		$tax_query = Tribe__Events__Pro__Widgets::form_tax_query( $filters, $this->instance['operand'] );

		if ( isset( $query['tax_query'] ) ) {
			$query['tax_query'] = array_merge( $query['tax_query'], $tax_query );
		} else {
			$query['tax_query'] = $tax_query;
		}

		return $query;
	}

	public function widget( $args, $instance ) {
		$ecp            = Tribe__Events__Pro__Main::instance();
		$tooltip_status = $ecp->recurring_info_tooltip_status();
		$ecp->disable_recurring_info_tooltip();
		$this->instance_defaults( $instance );

		// @todo remove after 3.7 (continuity helper for upgrading users)
		if ( isset( $this->instance['category'] ) ) {
			$this->include_cat_id( $this->instance['filters'], $this->instance['category'] );
		}

		parent::widget_output( $args, $this->instance, 'pro/widgets/list-widget' );

		if ( $tooltip_status ) {
			$ecp->enable_recurring_info_tooltip();
		}
	}

	public function update( $new_instance, $old_instance ) {
		$instance = parent::update( $new_instance, $old_instance );

		$instance['venue']     = $new_instance['venue'];
		$instance['country']   = $new_instance['country'];
		$instance['address']   = $new_instance['address'];
		$instance['city']      = $new_instance['city'];
		$instance['region']    = $new_instance['region'];
		$instance['zip']       = $new_instance['zip'];
		$instance['phone']     = $new_instance['phone'];
		$instance['cost']      = $new_instance['cost'];
		$instance['organizer'] = $new_instance['organizer'];
		$instance['operand']   = strip_tags( $new_instance['operand'] );
		$instance['filters']   = maybe_unserialize( $new_instance['filters'] );

		// @todo remove after 3.7 (added for continuity when users transition from 3.5.x or earlier to this release)
		if ( isset( $old_instance['category'] ) ) {
			$this->include_cat_id( $instance['filters'], $old_instance['category'] );
			unset( $instance['category'] );
		}

		return $instance;
	}

	public function form( $instance ) {
		$this->instance_defaults( $instance );
		$this->include_cat_id( $this->instance['filters'], $this->instance['category'] ); // @todo remove after 3.7

		$taxonomies = get_object_taxonomies( Tribe__Events__Main::POSTTYPE, 'objects' );
		$taxonomies = array_reverse( $taxonomies );

		$instance = $this->instance;
		include( Tribe__Events__Pro__Main::instance()->pluginPath . 'src/admin-views/widget-admin-advanced-list.php' );
	}

	protected function instance_defaults( $instance ) {
		$this->instance = wp_parse_args( (array) $instance, array(
			'title'              => __( 'Upcoming Events', 'tribe-events-calendar-pro' ),
			'limit'              => '5',
			'no_upcoming_events' => false,
			'venue'              => false,
			'country'            => true,
			'address'            => false,
			'city'               => true,
			'region'             => true,
			'zip'                => false,
			'phone'              => false,
			'cost'               => false,
			'category'           => false, // @todo remove this element after 3.7
			'organizer'          => false,
			'operand'            => 'OR',
			'filters'            => '',
			'instance'           => &$this->instance,
		) );
	}

	/**
	 * Adds the provided category ID to the list of filters.
	 *
	 * In 3.6 taxonomy filters were added to this widget (as already existed for the calendar
	 * widget): this helper exists to provide some continuity for users upgrading from a 3.5.x
	 * release or earlier, transitioning any existing category setting to the new filters
	 * list.
	 *
	 * @todo remove after 3.7
	 *
	 * @param mixed &$filters
	 * @param int   $id
	 */
	protected function include_cat_id( &$filters, $id ) {
		$id  = (string) absint( $id ); // An absint for sanity but a string for comparison purposes
		$tax = Tribe__Events__Main::TAXONOMY;
		if ( '0' === $id || ! is_string( $filters ) ) {
			return;
		}

		$filters = (array) json_decode( $filters, true );

		if ( isset( $filters[ $tax ] ) && ! in_array( $id, $filters[ $tax ] ) ) {
			$filters[ $tax ][] = $id;
		} elseif ( ! isset( $filters[ $tax ] ) ) {
			$filters[ $tax ] = array( $id );
		}

		$filters = json_encode( $filters );
	}
}
