<?php
if ( class_exists( 'Tribe__Events__Pro__This_Week' ) ) {
	return;
}

class Tribe__Events__Pro__This_Week {

	private static $instance;

	/**
	 * Get (and instantiate, if necessary) the instance of the class
	 *
	 * @static
	 * @return Tribe__Events__Pro__This_Week
	 */
	public static function instance() {
		if ( ! self::$instance instanceof self ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct() {
		//AJAX Setup
		add_action( 'wp_ajax_tribe_this_week', array( $this, 'ajax_change_this_week' ) );
		add_action( 'wp_ajax_nopriv_tribe_this_week', array( $this, 'ajax_change_this_week' ) );

		//Enqueue Style and Scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'styles_and_scripts' ), 15 );
	}

	/**
	 * This Week Widget - Style and Scripts
	 *
	 */
	public static function styles_and_scripts() {

		wp_enqueue_script( 'tribe-this-week', tribe_events_pro_resource_url( 'widget-this-week.min.js' ), array( 'jquery' ), apply_filters( 'tribe_events_pro_js_version', Tribe__Events__Pro__Main::VERSION ) );

		// Tribe Events CSS filename
		$event_file        = 'widget-this-week.css';
		$stylesheet_option = tribe_get_option( 'stylesheetOption', 'tribe' );

		// What Option was selected
		switch ( $stylesheet_option ) {
			case 'skeleton':
				$event_file_option = 'widget-this-week-' . $stylesheet_option . '.css';
				break;
			case 'full':
				$event_file_option = 'widget-this-week-' . $stylesheet_option . '.css';
				break;
			default:
				$event_file_option = 'widget-this-week-theme.css';
				break;
		}

		$style_url = tribe_events_pro_resource_url( $event_file_option );

		// get the minified file
		$style_url = Tribe__Events__Template_Factory::getMinFile( $style_url, true );

		//filter stylesheet
		$style_url = apply_filters( 'tribe_events_pro_widget_calendar_stylesheet_url', $style_url );

		//Check for Override
		$style_override_url = Tribe__Events__Templates::locate_stylesheet( 'tribe-events/pro/' . $event_file, $style_url );

		// Load up stylesheet from theme or plugin
		if ( $style_url && $stylesheet_option == 'tribe' ) {
			wp_enqueue_style( 'widget-this-week-pro-style', tribe_events_pro_resource_url( 'widget-this-week-full.css' ), array(), apply_filters( 'tribe_events_pro_css_version', Tribe__Events__Pro__Main::VERSION ) );
			wp_enqueue_style( Tribe__Events__Main::POSTTYPE . '-widget-this-week-pro-style', $style_url, array(), apply_filters( 'tribe_events_pro_css_version', Tribe__Events__Pro__Main::VERSION ) );
		} else {
			wp_enqueue_style( Tribe__Events__Main::POSTTYPE . '-widget-this-week-pro-style', $style_url, array(), apply_filters( 'tribe_events_pro_css_version', Tribe__Events__Pro__Main::VERSION ) );
		}

		if ( $style_override_url && $style_override_url != $style_url ) {
			wp_enqueue_style( Tribe__Events__Main::POSTTYPE . '--widget-this-week-pro-override-style', $style_override_url, array(), apply_filters( 'tribe_events_pro_css_version', Tribe__Events__Pro__Main::VERSION ) );
		}

		$widget_data = array( 'ajaxurl' => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ) );
		wp_localize_script( 'tribe-this-week', 'tribe_this_week', $widget_data );
	}

	/**
	 * This Week Widget - Ajax Change Week
	 *
	 *
	 */
	public function ajax_change_this_week() {

		$response = array( 'success' => false, 'html' => '', 'view' => 'this-week' );

		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'this-week-ajax' ) ) {
			wp_send_json_error();
		}

		if ( isset( $_POST['start_date'] ) && isset( $_POST['count'] ) ) {

			//Disable Tooltips
			$ecp            = Tribe__Events__Pro__Main::instance();
			$tooltip_status = $ecp->recurring_info_tooltip_status();
			$ecp->disable_recurring_info_tooltip();

			$tax_query = isset( $_POST['tax_query'] ) ? $_POST['tax_query'] : null;

			$_POST['start_date'] = trim( $_POST['start_date'] );

			if ( false == strtotime( $_POST['start_date'] ) ) {
				wp_send_json_error();
			}

			//Array of Variables to use for Data Attributes and Query
			$this_week_query_vars['start_date']    = $_POST['start_date'];
			$this_week_query_vars['end_date']      = tribe_get_last_week_day( $_POST['start_date'] );
			$this_week_query_vars['count']         = $_POST['count'];
			$this_week_query_vars['layout']        = $_POST['layout'];
			$this_week_query_vars['tax_query']     = $tax_query;
			$this_week_query_vars['hide_weekends'] = $_POST['hide_weekends'];

			//Setup Variables for Template
			$this_week_template_vars = self::this_week_template_vars( $this_week_query_vars );

			//Setup Attributes for Ajax
			$this_week_data_attrs = self::this_week_data_attr( $this_week_query_vars );

			//Setups This Week Object for Each Day
			$week_days = self::this_week_query( $this_week_query_vars );

			ob_start();

			include Tribe__Events__Templates::getTemplateHierarchy( 'pro/widgets/this-week-widget.php' );

			$response['html']    = ob_get_clean();
			$response['success'] = true;

			// Re-enable recurring event info
			if ( $tooltip_status ) {
				$ecp->enable_recurring_info_tooltip();
			}
		}
		apply_filters( 'tribe_events_ajax_response', $response );

		wp_send_json( $response );

	}

	/**
	 * This Week Widget - Data Attributes for Ajax
	 *
	 *
	 */
	public static function this_week_template_vars( $this_week_query_vars ) {

		$this_week_template_vars['layout']                = $this_week_query_vars['layout'];
		$this_week_template_vars['start_date']            = $this_week_query_vars['start_date'];
		$this_week_template_vars['end_date']              = $this_week_query_vars['end_date'];
		$this_week_template_vars['hide_weekends']         = $this_week_query_vars['hide_weekends'];
		$this_week_template_vars['events_label_singular'] = tribe_get_event_label_singular();
		$this_week_template_vars['events_label_plural']   = tribe_get_event_label_plural();

		return $this_week_template_vars;

	}

	/**
	 * This Week Widget - Data Attributes for Ajax
	 *
	 *
	 */
	public static function this_week_data_attr( $this_week_query_vars ) {

		if ( is_array( $this_week_query_vars['tax_query'] ) ) {
			$this_week_query_vars['tax_query'] = json_encode( $this_week_query_vars['tax_query'] );
		}

		$attrs = '';
		$attrs .= ' data-prev-date="' . esc_attr( date( Tribe__Date_Utils::DBDATEFORMAT, strtotime( $this_week_query_vars['start_date'] . ' -7 days' ) ) ) . '"';
		$attrs .= ' data-next-date="' . esc_attr( $this_week_query_vars['end_date'] ) . '"';
		$attrs .= ' data-count="' . esc_attr( $this_week_query_vars['count'] ) . '"';
		$attrs .= ' data-layout="' . esc_attr( $this_week_query_vars['layout'] ) . '"';
		$attrs .= ' data-tax-query="' . esc_attr( $this_week_query_vars['tax_query'] ) . '"';
		$attrs .= ' data-hide-weekends="' . esc_attr( $this_week_query_vars['hide_weekends'] ) . '"';
		$attrs .= ' data-nonce="' . wp_create_nonce( 'this-week-ajax' ) . '"';

		return $attrs;

	}

	/**
	* Get the array of days we're showing on this week widget
	* Takes into account the first day of the week in WP general settings
	*
	* @return array
	*
	* @see tribe_events_week_get_days()
	*/
	public static function get_day_range( ) {

		$start_of_week = get_option( 'start_of_week' );
		$days          = range( $start_of_week, $start_of_week + 6 );

		foreach ( $days as $i => $day ) {
			if ( $day > 6 ) {
				$days[ $i ] -= 7;
			}
		}

		$days = array_values( $days );

		return $days;
	}

	/**
	 * This Week Query
	 *
	 *
	 *  @return object
	 */
	public static function this_week_query( $this_week_query_vars ) {

		//Only Get Private Events if user can view
		$post_status = array( 'publish' );
		if ( current_user_can( 'read_private_tribe_events' ) ) {
			$post_status[] = 'private';
		}

		//Get Events with Hide From Event Listings Checked
		$hide_upcoming_ids = Tribe__Events__Query::getHideFromUpcomingEvents();


		$this_week_widget_args = array(
			'post_type'            => Tribe__Events__Main::POSTTYPE,
			'tax_query'            => $this_week_query_vars['tax_query'],
			'eventDisplay'         => 'custom',
			'start_date'           => $this_week_query_vars['start_date'],
			'end_date'             => $this_week_query_vars['end_date'],
			'post_status'          => $post_status,
			'tribeHideRecurrence'  => false,
			'post__not_in'         => $hide_upcoming_ids,
			'tribe_render_context' => 'widget',
			'posts_per_page'       => - 1,
		);
		/**
		 * Filter This Week Widget args
		 *
		 * @param array $this_week_widget_args Arguments for This Week Widget
		 */
		$this_week_widget_args = apply_filters( 'tribe_events_pro_this_week_widget_query_args', $this_week_widget_args );

		// Get all the upcoming events for this week
		$events = tribe_get_events( $this_week_widget_args, true );

		//Days Array to set events for each day
		$week_days = array();

		//Set First Day
		$day = $this_week_query_vars[ 'start_date' ];

		//Get Day Range
		$day_range = self::get_day_range();

		//Todays Date According to WordPress
		$timestamp_today = strtotime( current_time( Tribe__Date_Utils::DBDATEFORMAT ) );

		//Date Formats from The Events Calendar
		$display_date_format  = apply_filters( 'tribe_events_this_week_date_format', 'jS' );
		$display_day_format  = apply_filters( 'tribe_events_this_week_day_format', 'D ' );

		// Array used for calculation of php strtotime relative dates
		$weekday_array = array(
			'Sunday',
			'Monday',
			'Tuesday',
			'Wednesday',
			'Thursday',
			'Friday',
			'Saturday',
		);

		//Build an Array for Each Day
		foreach ( $day_range as $i => $day_number ) {

			//If Hide Weekends True then skip those days
			if ( $this_week_query_vars[ 'hide_weekends' ] === 'true' && ( $day_number == 0 || $day_number == 6 ) ) {
				continue;
			}

			// figure out the $date that we're currently looking at
			if ( $day_number >= $day_range[0] ) {
				// usually we can just get the date for the next day
				$date = date( Tribe__Date_Utils::DBDATEFORMAT, strtotime( $day . "+$i days" ) );
			} else {
				//Start Day of week in the Middle and not in typical Sunday or Monday
				$date = date( Tribe__Date_Utils::DBDATEFORMAT, strtotime( "Next {$weekday_array[$day_number]}", strtotime( $day ) ) );
			}

			$this_week_events_sticky = $this_week_events = array();

			if ( $events->have_posts() ) {
				//loop through all events and sort based on sticky or not
				foreach ( $events->posts as $j => $event ) {

					if ( tribe_event_is_on_date( $date, $event ) ) {

						$event->days_between = tribe_get_days_between( $event->EventStartDate, $event->EventEndDate, true );

						if ( $event->menu_order == -1 ) {

							$this_week_events_sticky[] = $event;

						} else {

							$this_week_events[] = $event;

						}
					}
				}
			}
			//Merge the two arrays for the day only if sticky events are included for that day
			if ( ! empty( $this_week_events_sticky ) && is_array( $this_week_events_sticky ) && is_array( $this_week_events ) ) {

				$this_week_events = array_merge( $this_week_events_sticky, $this_week_events );
			}

			$formatted_date  = date_i18n( $display_date_format, strtotime( $date ) );
			$formatted_day  = date_i18n( $display_day_format, strtotime( $date ) );
			$timestamp_date  = strtotime( $date );

			// create the "day" element to do display in the template
			$week_days[] = array(
				'date'             => $date,
				'day_number'       => $day_number,
				'formatted_date'   => $formatted_date,
				'formatted_day'    => $formatted_day,
				'is_today'         => ( $timestamp_date == $timestamp_today ) ? true : false,
				'is_past'          => ( $timestamp_date < $timestamp_today ) ? true : false,
				'is_future'        => ( $timestamp_date > $timestamp_today ) ? true : false,
				'this_week_events' => $this_week_events,
				'has_events'       => $this_week_events,
				'total_events'     => count( $this_week_events ),
				'events_limit'     => $this_week_query_vars['count'],
				'view_more'        => ( count( $this_week_events ) > $this_week_query_vars['count'] ) ? esc_url_raw( tribe_get_day_link( $date ) ) : false,
			);
		}

		return $week_days;

	}
}
