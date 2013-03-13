<?php
if ( class_exists( 'TribeEventsMiniCalendar' ) )
	return;

class TribeEventsMiniCalendar {

	private $args;

	function __construct() {
		add_action( 'wp_ajax_tribe-mini-cal', array( $this, 'ajax_change_month' ) );
		add_action( 'wp_ajax_nopriv_tribe-mini-cal', array( $this, 'ajax_change_month' ) );

		add_action( 'wp_ajax_tribe-mini-cal-day', array( $this, 'ajax_select_day' ) );
		add_action( 'wp_ajax_nopriv_tribe-mini-cal-day', array( $this, 'ajax_select_day' ) );

	}

	public function  ajax_select_day() {
		$response = array( 'success' => false, 'html' => '' );

		if ( isset( $_POST["nonce"] ) && isset( $_POST["eventDate"] ) && isset( $_POST["count"] ) ) {

			if ( !wp_verify_nonce( $_POST["nonce"], 'calendar-ajax' ) )
				die();

			$response['success'] = true;

			add_action( 'pre_get_posts', array( $this, 'ajax_select_day_set_date' ), -10 );

			$tax_query = isset( $_POST['tax_query'] ) ? maybe_unserialize( stripslashes_deep( $_POST['tax_query'] ) ) : null;

			$args = array( 'month'        => $_POST["eventDate"],
			               'count'        => $_POST["count"],
			               'tax_query'    => $tax_query,
			               'eventDisplay' => 'day' );

			ob_start();
			$events = self::show_events( $args );

			remove_action( 'pre_get_posts', array( $this, 'ajax_select_day_set_date' ) );


			$response['html'] = ob_get_clean();

			if ( !empty( $_POST['return_objects'] ) && $_POST['return_objects'] === '1' ) {
				$response['objects'] = $events;
			}

		}

		header( 'Content-type: application/json' );
		echo json_encode( $response );
		die();
	}

	public function ajax_change_month() {

		$response = array( 'success' => false, 'html' => '' );

		if ( isset( $_POST["eventDate"] ) && isset( $_POST["layout"] ) && isset( $_POST["count"] ) ) {

			$tax_query = isset( $_POST['tax_query'] ) ? maybe_unserialize( stripslashes_deep( $_POST['tax_query'] ) ) : null;

			$args = array( 'layout'       => $_POST["layout"],
			               'month'        => $_POST["eventDate"] . '-01',
			               'count'        => $_POST["count"],
			               'tax_query'    => $tax_query,
			               'filter_date'  => true );

			ob_start();

			self::do_calendar( $args );

			$response['html']    = ob_get_clean();
			$response['success'] = true;

		}

		header( 'Content-type: application/json' );
		echo json_encode( $response );
		die();

	}

	/**
	 *
	 * returns the full markup for the AJAX Calendar
	 *
	 * @static
	 *
	 * @param array $args
	 *              -----> layout:      string  'wide' or 'tall'
	 *                                          month:       date    What month-year to print
	 *                                          count:       int     # of events in the list (doesn't affect the calendar).
	 *                                          tax_query:   array   For the events list (doesn't affect the calendar).
	 *                                          Same format as WP_Query tax_queries. See sample below.
	 *
	 *
	 * tax_query sample:
	 *
	 *        array( 'relation' => 'AND',
	 *               array( 'taxonomy' => 'tribe_events_cat',
	 *                      'field'    => 'slug',
	 *                      'terms'    => array( 'featured' ),
	 *              array( 'taxonomy' => 'post_tag',
	 *                     'field'    => 'id',
	 *                     'terms'    => array( 103, 115, 206 ),
	 *                     'operator' => 'NOT IN' ) ) );
	 *
	 *
	 */

	public function do_calendar( $args = array() ) {
		global $wp_query;

		self::styles_and_scripts();

		$default = array( 'layout'         => 'wide',
		                  'month'          => date_i18n( TribeDateUtils::DBDATEFORMAT ),
		                  'count'          => 5,
		                  'tax_query'      => null,
		                  'eventDisplay'   => 'month' );


		$args = wp_parse_args( $args, $default );

		$old_date = ( !( empty( $wp_query->query_vars['eventDate'] ) ) ) ? $wp_query->query_vars['eventDate'] : null;

		$wp_query->query_vars['eventDate']      = $args['month'];
		$wp_query->query_vars['eventDisplay']   = $args['eventDisplay'];
		$wp_query->query_vars['posts_per_page'] = $args['count'];

		// Commenting out title as we are allowing users to specify in widget
		//echo apply_filters( 'tribe_events_calendar_widget_title', "<h3 class='tribe-mini-calendar-title widget-title layout-" . esc_attr( $args['layout'] ) . "'>" . __( 'Events Calendar', 'tribe-events-calendar-pro' ) . "</h3>", $args );

		echo "<div class='tribe-mini-calendar-wrapper layout-" . esc_attr( $args['layout'] ) . "'>";

		echo "<div class='tribe-mini-calendar-grid-wrapper'>";

		self::show_calendar( $args );

		echo "</div>";
		echo "<div class='tribe-mini-calendar-list-wrapper layout-" . esc_attr( $args['layout'] ) . "'>";

		if ( !empty( $args['filter_date'] ) && $args['filter_date'] )
			add_action( 'pre_get_posts', array( $this, 'ajax_change_month_set_date' ), -10 );

		self::show_events( $args );

		if ( !empty( $args['filter_date'] ) && $args['filter_date'] )
			remove_action( 'pre_get_posts', array( $this, 'ajax_change_month_set_date' ) );

		echo "</div>";
		echo "</div>";

		if ( $old_date ) {
			$wp_query->query_vars['eventDate'] = $old_date;
		}

	}

	private function styles_and_scripts() {
		wp_enqueue_style( 'tribe-mini-calendar', TribeEventsPro::instance()->pluginUrl . 'resources/widget-calendar.css' );
		wp_enqueue_script( 'tribe-mini-calendar', TribeEventsPro::instance()->pluginUrl . 'resources/widget-calendar.js', array( 'jquery' ) );

		$widget_data = array( "ajaxurl" => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ) );
		wp_localize_script( 'tribe-mini-calendar', 'TribeMiniCalendar', $widget_data );
	}

	private function show_calendar( $args ) {
		$ts = TribeEventsPro::instance();
		include $ts->pluginPath . 'views/mini-calendar-grid.php';
	}


	private function  get_events( $args ) {

		TribeEventsQuery::init();

		$query_args = array( 'posts_per_page'               => $args['count'],
		                     'tax_query'                    => $args['tax_query'],
		                     'eventDisplay'                 => 'custom',
		                     'post_status'                  => array( 'publish' ),
		                     'is_tribe_mini_calendar'       => true );


		return TribeEventsQuery::getEvents( $query_args );

	}

	private function show_events( $args ) {
		$ts = TribeEventsPro::instance();

		$this->args = $args;

		add_action( 'tribe_events_pre_get_posts', array( $this, 'set_count_and_taxonomies' ), 1000 );

		$events = self::get_events( $args );
		$cont   = 0;

		remove_action( 'tribe_events_pre_get_posts', array( $this, 'set_count_and_taxonomies' ) );


		echo "<div class='tribe-mini-calendar-left'>";
		foreach ( (array)$events as $event ) {
			$cont++;
			if ( $cont == 2 ) {
				echo "</div>";
				echo "<div class='tribe-mini-calendar-right'>";
			}
			include $ts->pluginPath . 'views/mini-calendar-list.php';
		}
		echo "</div>";

		return $events;
	}

	public function get_tax_query_from_widget_options( $filters, $operand ) {

		if ( empty( $filters ) )
			return null;

		$tax_query = array();

		foreach ( $filters as $tax => $terms ) {
			if ( empty( $terms ) )
				continue;

			$tax_operand = 'AND';
			if ( $operand == 'OR' )
				$tax_operand = 'IN';

			$arr         = array( 'taxonomy' => $tax, 'field' => 'id', 'operator' => $tax_operand, 'terms' => $terms );
			$tax_query[] = $arr;
		}

		if ( count( $tax_query ) > 1 ) {
			$tax_query['relation'] = $operand;
		}

		return $tax_query;
	}

	/* Query Filters */

	public function set_count_and_taxonomies( $query ) {
		if ( !isset( $query->query_vars['is_tribe_mini_calendar'] ) )
			return $query;

		$count = !empty( $this->args['count'] ) ? $this->args['count'] : 5;
		$query->set( 'posts_per_page', $count );
		if ( !empty( $this->args['tax_query'] ) ) {
			$query->set( 'tax_query', $this->args['tax_query'] );
		}

		return $query;
	}

	public function ajax_change_month_set_date( $query ) {

		if ( isset( $_POST["eventDate"] ) && $_POST["eventDate"] ) {
			$query->set( 'eventDate', $_POST["eventDate"] . '-01' );
			$query->set( 'start_date', $_POST["eventDate"] . '-01' );
			$query->set( 'end_date', date( 'Y-m-d', strtotime( TribeEvents::instance()->nextMonth( $_POST["eventDate"] . '-01' ) ) - ( 24 * 3600 ) ) );
			$query->set( 'eventDisplay', 'month' );
		}
		return $query;
	}

	public function ajax_select_day_set_date( $query ) {

		if ( isset( $_POST["eventDate"] ) && $_POST["eventDate"] ) {
			$query->set( 'eventDate', $_POST["eventDate"] );
			$query->set( 'eventDisplay', 'day' );
			$query->set( 'start_date', tribe_event_beginning_of_day( $_POST["eventDate"] ) );
			$query->set( 'end_date', tribe_event_end_of_day( $_POST["eventDate"] ) );
			$query->set( 'hide_upcoming', false );
		}
		return $query;
	}


	public static $instance;

	/**
	 * Get (and instantiate, if necessary) the instance of the class
	 *
	 * @static
	 * @return TribeEventsMiniCalendar
	 */
	public static function instance() {
		if ( !is_a( self::$instance, __CLASS__ ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

}