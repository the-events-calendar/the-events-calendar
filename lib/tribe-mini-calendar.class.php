<?php
if ( class_exists( 'TribeEventsMiniCalendar' ) ) {
	return;
}

class TribeEventsMiniCalendar {

	private $args;
	private $show_list = true;

	function __construct() {
		add_action( 'wp_ajax_tribe-mini-cal', array( $this, 'ajax_change_month' ) );
		add_action( 'wp_ajax_nopriv_tribe-mini-cal', array( $this, 'ajax_change_month' ) );

		add_action( 'wp_ajax_tribe-mini-cal-day', array( $this, 'ajax_select_day' ) );
		add_action( 'wp_ajax_nopriv_tribe-mini-cal-day', array( $this, 'ajax_select_day' ) );

		// set up the list query
		add_action( 'tribe_before_get_template_part', array( $this, 'setup_list' ) );

		// enqueue the list view cleanup
		add_action( 'tribe_after_get_template_part', array( $this, 'shutdown_list' ) );
	}

	/**
	 * Return the month to show in the widget
	 *
	 * @param string $format
	 *
	 * @return string
	 */
	public function get_month( $format = TribeDateUtils::DBDATETIMEFORMAT ) {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return isset( $_POST["eventDate"] ) ? $_POST["eventDate"] : date_i18n( $format );
		}

		return date_i18n( $format );
	}

	/**
	 * Get the args passed to the mini calendar
	 *
	 * @return array
	 **/
	public function get_args() {
		return $this->args;
	}

	public function  ajax_select_day() {

		$response = array( 'success' => false, 'html' => '', 'view' => 'mini-day' );

		if ( isset( $_POST["nonce"] ) && isset( $_POST["eventDate"] ) && isset( $_POST["count"] ) ) {
			if ( ! wp_verify_nonce( $_POST["nonce"], 'calendar-ajax' ) ) {
				die();
			}

			$response['success'] = true;

			add_action( 'pre_get_posts', array( $this, 'ajax_select_day_set_date' ), -10 );

			$tax_query = isset( $_POST['tax_query'] ) ? $_POST['tax_query'] : null;

			$this->args = array(
				'eventDate'    => $_POST["eventDate"],
				'count'        => $_POST["count"],
				'tax_query'    => $tax_query,
				'eventDisplay' => 'day'
			);

			ob_start();

			tribe_get_template_part( 'pro/widgets/mini-calendar/list' );

			remove_action( 'pre_get_posts', array( $this, 'ajax_select_day_set_date' ) );

			$response['html'] = ob_get_clean();

			if ( ! empty( $_POST['return_objects'] ) && $_POST['return_objects'] === '1' ) {
				$response['objects'] = $events;
			}

		}
		apply_filters( 'tribe_events_ajax_response', $response );

		header( 'Content-type: application/json' );
		echo json_encode( $response );
		die();
	}

	public function ajax_change_month() {

		$response = array( 'success' => false, 'html' => '', 'view' => 'mini-month' );

		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'calendar-ajax' ) ) {
			die( -1 );
		}

		if ( isset( $_POST['eventDate'] ) && isset( $_POST['count'] ) ) {
			$tax_query = isset( $_POST['tax_query'] ) ? $_POST['tax_query'] : null;

			$_POST['eventDate'] = trim( $_POST['eventDate'] );

			if ( false == strtotime( $_POST['eventDate'] ) ) {
				die( -1 );
			}

			$args = array(
				'eventDate'           => $_POST['eventDate'],
				'count'               => $_POST['count'],
				'tax_query'           => $tax_query,
				'filter_date'         => true,
				'tribeHideRecurrence' => false,
			);

			ob_start();

			self::do_calendar( $args );

			$response['html']    = ob_get_clean();
			$response['success'] = true;

		}
		apply_filters( 'tribe_events_ajax_response', $response );

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
	 *                            -----> eventDate:   date    What month-year to print
	 *                            count:       int     # of events in the list (doesn't affect the calendar).
	 *                            tax_query:   array   For the events list (doesn't affect the calendar).
	 *                            Same format as WP_Query tax_queries. See sample below.
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

		$this->args = $args;

		if ( ! isset( $this->args['eventDate'] ) ) {
			$this->args['eventDate'] = $this->get_month();
		}

		// don't show the list if they set it the widget option to show 0 events in the list
		if ( $this->args['count'] == 0 ) {
			$this->show_list = false;
		}

		// enqueue the widget js
		self::styles_and_scripts();

		// widget setting for count is not 0
		if ( ! $this->show_list ) {
			add_filter( 'tribe_events_template_widgets/mini-calendar/list.php', '__return_false' );
		}

		tribe_get_template_part( 'pro/widgets/mini-calendar-widget' );

	}

	/**
	 * @todo revise so that our stylesheet is enqueued in time for the link to be included within the head element
	 */
	private function styles_and_scripts() {
		wp_enqueue_script( 'tribe-mini-calendar', TribeEventsPro::instance()->pluginUrl . 'resources/widget-calendar.js', array( 'jquery' ), apply_filters( 'tribe_events_pro_js_version', TribeEventsPro::VERSION ) );
		TribeEventsPro_Widgets::enqueue_calendar_widget_styles();

		$widget_data = array( "ajaxurl" => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ) );
		wp_localize_script( 'tribe-mini-calendar', 'TribeMiniCalendar', $widget_data );
	}

	public function setup_list( $template_file ) {

		if ( basename( dirname( $template_file ) ) . '/' . basename( $template_file ) == 'mini-calendar/list.php' ) {

			if ( $this->args['count'] == 0 ) {
				return;
			}

			// make sure the widget taxonomy filter setting is respected
			add_action( 'pre_get_posts', array( $this, 'set_count' ), 1000 );

			global $wp_query;

			// hijack the main query to load the events via provided $args
			if ( ! is_null( $this->args ) ) {
				$query_args = array(
					'posts_per_page'         => $this->args['count'],
					'tax_query'              => $this->args['tax_query'],
					'eventDisplay'           => 'custom',
					'start_date'             => $this->get_month(),
					'post_status'            => array( 'publish' ),
					'is_tribe_mini_calendar' => true,
					'tribeHideRecurrence'    => false,
				);

				// set end date if initial load, or ajax month switch
				if ( ! defined( 'DOING_AJAX' ) || ( defined( 'DOING_AJAX' ) && $_POST['action'] == 'tribe-mini-cal' ) ) {
					$query_args['end_date'] = substr_replace( $this->get_month( TribeDateUtils::DBDATEFORMAT ), TribeDateUtils::getLastDayOfMonth( strtotime( $this->get_month() ) ), - 2 );
					// @todo use tribe_events_end_of_day() ?
					$query_args['end_date'] = TribeDateUtils::endOfDay( $query_args['end_date'] );
				}

				$wp_query = TribeEventsQuery::getEvents( $query_args, true );

			}
		}
	}

	public function shutdown_list( $template_file ) {
		if ( basename( dirname( $template_file ) ) . '/' . basename( $template_file ) == 'mini-calendar/list.php' ) {
			// reset the global $wp_query
			wp_reset_query();

			// stop paying attention to the widget count setting, we're done with it
			remove_action( 'pre_get_posts', array( $this, 'set_count' ), 1000 );
		}
	}

	/* Query Filters */

	public function set_count( $query ) {

		$count = ! empty( $this->args['count'] ) ? $this->args['count'] : 5;
		$query->set( 'posts_per_page', $count );

		return $query;
	}

	public function set_taxonomies( $query ) {

		if ( ! empty( $this->args['tax_query'] ) ) {
			$query->set( 'tax_query', $this->args['tax_query'] );
		}

		return $query;
	}

	public function ajax_change_month_set_date( $query ) {

		if ( isset( $_POST["eventDate"] ) && $_POST["eventDate"] ) {
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
		if ( ! is_a( self::$instance, __CLASS__ ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
