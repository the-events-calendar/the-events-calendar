<?php

use Tribe__Date_Utils as Dates;
use Tribe__Utils__Array as Arr;

/**
 *  Class that implements the export to iCal functionality
 *  both for views and single events.
 */
class Tribe__Events__iCal {

	/**
	 * The number of events that will be exported when generating the iCal feed.
	 *
	 * @since 4.4.0
	 *
	 * @var int
	 */
	protected $feed_default_export_count = 30;

	/**
	 * The $post where the *.ics file is generated.
	 *
	 * @since 4.9.4
	 *
	 * @var null
	 */
	protected $post = null;

	/**
	 * An array with all the events that are part of the *.ics file.
	 *
	 * @since 4.9.4
	 *
	 * @var array
	 */
	protected $events = [];

	/**
	 * The type of iCal Feed (ical|outlook).
	 *
	 * @since 5.16.0
	 *
	 * @var string
	 */
	protected $type = 'ical';

	/**
	 * Set all the filters and actions necessary for the operation of the iCal generator.
	 *
	 * @since 3.6.0
	 * @since 6.15.6 Add `prevent_redirect_on_ical`.
	 */
	public function hook() {
		add_action( 'tribe_events_after_footer', [ $this, 'maybe_add_link' ], 10, 1 );
		add_action( 'tribe_events_single_event_after_the_content', [ $this, 'single_event_links' ] );
		add_action( 'template_redirect', [ $this, 'do_ical_template' ] );
		add_filter( 'tribe_get_ical_link', [ $this, 'day_view_ical_link' ], 20, 1 );
		add_action( 'wp_head', [ $this, 'set_feed_link' ], 2, 0 );
		add_filter( 'tec_events_views_v2_should_redirect', [ $this, 'prevent_redirect_on_ical' ] );
	}

	/**
	 * Outputs a <link> element for the iCal feed.
	 *
	 * @since 3.6.0
	 */
	public function set_feed_link() {
		if ( ! current_theme_supports( 'automatic-feed-links' ) ) {
			return;
		}
		$separator  = _x( '&raquo;', 'feed link', 'the-events-calendar' );
		$feed_title = sprintf( esc_html__( '%1$s %2$s iCal Feed', 'the-events-calendar' ), get_bloginfo( 'name' ), $separator );

		printf(
			'<link rel="alternate" type="text/calendar" title="%s" href="%s" />',
			esc_attr( $feed_title ),
			esc_url( tribe_get_ical_link() )
		);

		echo "\n";
	}

	/**
	 * Returns the URL for the iCal generator for lists of posts.
	 *
	 * @since 3.6.0
	 *
	 * @param string $type The type of iCal link to return, defaults to 'home'.
	 *
	 * @return string
	 */
	public function get_ical_link( $type = 'home' ) {
		$tec = Tribe__Events__Main::instance();

		return add_query_arg( [ 'ical' => 1 ], $tec->getLink( $type ) );
	}

	/**
	 * Make sure when we grab a month link it includes the correct month.
	 *
	 * @since 5.1.0
	 *
	 * @param string $event_date Date of the month we are getting the link for.
	 *
	 * @return string The iCal export URL for the Month view.
	 */
	public function month_view_ical_link( $event_date = null ) {
		$tec = Tribe__Events__Main::instance();

		// Default to current month if not set.
		if ( empty( $event_date ) ) {
			$event_date = Dates::build_date_object()->format( Dates::DBYEARMONTHTIMEFORMAT );
		}

		$url = $tec->getLink( 'month', $event_date );

		return add_query_arg( [ 'ical' => 1 ], $url );
	}

	/**
	 * Make sure iCal link has the date in the URL instead of "today" on day view.
	 *
	 * @since 3.6.0
	 *
	 * @param string $link The URL of the iCal feed for day view.
	 *
	 * @return string
	 */
	public function day_view_ical_link( $link ) {
		if ( tribe_is_day() ) {

			if ( ! $wp_query = tribe_get_global_query_object() ) {
				return;
			}

			$day  = $wp_query->get( 'start_date' );
			$link = trailingslashit( esc_url( trailingslashit( tribe_get_day_link( $day ) ) . '?ical=1' ) );
		}

		return $link;
	}

	/**
	 * Generates the markup for iCal and gCal single event links.
	 *
	 * @since 3.6.0
	 */
	public function single_event_links() {

		// Don't show on password protected posts.
		if ( is_single() && post_password_required() ) {
			return;
		}
		$calendar_links = '<div class="tribe-events-cal-links">';
		$calendar_links .= '<a class="tribe-events-gcal tribe-events-button" href="' . Tribe__Events__Main::instance()->esc_gcal_url( tribe_get_gcal_link() ) . '" target="_blank" rel="noopener noreferrer noindex" title="' . esc_attr__( 'Add to Google Calendar', 'the-events-calendar' ) . '">+ ' . esc_html__( 'Google Calendar', 'the-events-calendar' ) . '</a>';
		$calendar_links .= '<a class="tribe-events-ical tribe-events-button" href="' . esc_url( tribe_get_single_ical_link() ) . '" title="' . esc_attr__( 'Download .ics file', 'the-events-calendar' ) . '"  rel="noopener noreferrer noindex" >+ ' . esc_html__( 'Add to iCalendar', 'the-events-calendar' ) . '</a>';
		$calendar_links .= '</div><!-- .tribe-events-cal-links -->';

		/**
		 * Filters the HTML for the iCal and gCal single-event link buttons.
		 *
		 * This allows for complete customization of the calendar links output.
		 *
		 * @since 4.6.2
		 *
		 * @param string $calendar_links The HTML markup of the iCal and gCal single-event link buttons.
		 */
		echo apply_filters( 'tribe_events_ical_single_event_links', $calendar_links );
	}


	/**
	 * Generates the markup for the "iCal Import" link for the views.
	 *
	 * @since 3.6.0
	 */
	public function maybe_add_link() {

		if ( ! $wp_query = tribe_get_global_query_object() ) {
			return;
		}

		/**
		 * A filter to control whether the "iCal Import" link shows up or not.
		 *
		 * @since 4.6.2
		 *
		 * @param boolean $show Whether to show the "iCal Import" link; defaults to true.
		 */
		$show_ical = apply_filters( 'tribe_events_list_show_ical_link', true );

		if ( ! $show_ical ) {
			return;
		}

		if ( tribe_is_month() && ! tribe_events_month_has_events() ) {
			return;
		}

		if ( ! tribe_is_month() && ( is_single() || empty( $wp_query->posts ) ) ) {
			return;
		}

		$tec  = Tribe__Events__Main::instance();
		$view = $tec->displaying;

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX && isset( $wp_query->query_vars['eventDisplay'] ) ) {
			$view = $wp_query->query_vars['eventDisplay'];
		}

		/**
		 * Allow the customization of the iCal export link "Export Events" text.
		 *
		 * @since 4.6.2
		 *
		 * @param string $text The default link text, which is "Export Events".
		 */
		$text  = apply_filters( 'tribe_events_ical_export_text', esc_html__( 'Export Events', 'the-events-calendar' ) );
		$title = esc_html__( 'Use this to share calendar data with Google Calendar, Apple iCal and other compatible apps', 'the-events-calendar' );

		printf(
			'<a class="tribe-events-ical tribe-events-button" rel="noindex nofollow" title="%1$s" href="%2$s">+ %3$s</a>',
			$title,
			esc_url( tribe_get_ical_link() ),
			$text
		);
	}

	/**
	 * Executes the iCal generator when the appropriate query_var or $_GET is set up.
	 *
	 * @since 3.6.0
	 */
	public function do_ical_template() {
		// Bail if a relevant query string is not included.
		// Otherwise, hijack to the iCal template.
		if (
			! get_query_var( 'ical' )
			&& ! isset( $_GET['ical'] )
			&& ! get_query_var( 'outlook-ical' )
			&& ! isset( $_GET['outlook-ical'] )
		) {
			return;
		}

		// Change the type, if it's an Outlook export.
		if (
			get_query_var( 'outlook-ical' )
			|| isset( $_GET['outlook-ical'] )
		) {
			$this->type = 'outlook';
		}

		/**
		 * Action fired before the creation of the feed is started, helpful to set up methods and other filters used
		 * on this class.
		 *
		 * @since 4.6.11
		 */
		do_action( 'tribe_events_ical_before' );

		if ( ! $wp_query = tribe_get_global_query_object() ) {
			return;
		}

		$event_ids = tribe_get_request_var( 'event_ids', false );

		/**
		 * Allows filtering the event IDs after the `Tribe__Events__ICal` class
		 * tried to fetch them from the current request.
		 *
		 * @since 4.6.0
		 *
		 * @param array<int>|false $event_ids Either a list of requested event post IDs or `false`
		 *                                    if the current request does not specify the event post
		 *                                    IDs to fetch.
		 */
		$event_ids = apply_filters( 'tribe_ical_template_event_ids', $event_ids );

		if ( false !== $event_ids ) {
			$event_ids = Arr::list_to_array( $event_ids );

			// Exit if there are no events, or the feed will still generate.
			if ( empty( $event_ids ) ) {
				die();
			}

			$events = array_map( 'tribe_get_event', $event_ids );
			$this->generate_ical_feed( $events );
		} elseif ( is_singular( Tribe__Events__Main::POSTTYPE ) ) {
			$this->generate_ical_feed( $wp_query->post );
		} else {
			$this->generate_ical_feed();
		}
		die();
	}

	/**
	 * Checks access to an event's content, in the context for generating an iCal file.
	 *
	 * @since 6.1.3
	 *
	 * @param numeric|WP_Post $post The post to evaluate our access to.
	 *
	 * @return bool True if a post should be shown, false if it should be hidden or password protected.
	 */
	public static function has_access_to_see_event_content( $post ): bool {
		// Can we see it at all?
		if ( ! self::has_access_to_see_event_exists( $post ) ) {
			return false;
		}

		// If password required, we hide the content from the feed.
		return ! post_password_required( $post );
	}

	/**
	 * Checks access to an event, in the context for generating an iCal file.
	 *
	 * @since 6.1.3
	 *
	 * @param numeric|WP_Post $post The post to evaluate our access to.
	 *
	 * @return bool True if the post can be accessed, false if not.
	 */
	public static function has_access_to_see_event_exists( $post ): bool {
		$post = get_post( $post );
		if ( ! $post instanceof WP_Post ) {
			return false;
		}
		// Most events.
		if ( $post->post_status === 'publish' ) {
			return true;
		}
		// If private, make sure they have access (and are logged in).
		if ( $post->post_status === 'private' && current_user_can( 'read_post', $post->ID ) ) {
			return true;
		}

		// Fallback to denied access unless explicitly approved above.
		return false;
	}

	/**
	 * Generates the iCal file.
	 *
	 * @since 6.1.3 Adding access checks to the provided posts.
	 *
	 * @param int|null|array $post If you want the iCal file for a single event.
	 * @param boolean        $echo Whether the content should be echoed or returned.
	 *
	 * @return string The complete iCal feed.
	 */
	public function generate_ical_feed( $post = null, $echo = true ) {
		// If we are searching via a single numeric/post, turn into an array.
		if ( ! empty( $post ) && ! is_array( $post ) ) {
			$post = [ $post ];
		}

		// Gatekeep any externally handed events through permissions.
		if ( is_array( $post ) ) {
			$post = array_filter( $post, static function ( $event_id ) {
				return self::has_access_to_see_event_exists( get_post( $event_id ) );
			} );
			if ( empty( $post ) ) {
				if ( $echo ) {
					die();
				} else {
					return '';
				}
			}
			$post = array_map( 'get_post', $post );
		}

		// Now set up to do our search.
		$this->post   = $post;
		$this->events = $this->get_event_posts();

		$content = $this->get_content();

		if ( $echo ) {
			$this->set_headers();
			tribe_exit( $content );
		}

		return $content;
	}

	/**
	 * Get an array with all the Events to be used to process the *.ics file.
	 *
	 * @since 4.9.4
	 *
	 * @return array|null An array of events or null.
	 */
	protected function get_event_posts() {
		if ( $this->post ) {
			return is_array( $this->post ) ? $this->post : [ $this->post ];
		}

		if ( tribe_is_month() ) {
			return $this->get_month_view_events();
		}

		$list_view_slug = \Tribe\Events\Views\V2\Views\List_View::get_view_slug();

		if ( tribe_is_organizer() ) {
			return $this->get_events_list(
				[
					'organizer'    => get_the_ID(),
					'eventDisplay' => $list_view_slug,
				]
			);
		}

		if ( tribe_is_venue() ) {
			return $this->get_events_list(
				[
					'venue'        => get_the_ID(),
					'eventDisplay' => tribe_get_request_var( 'tribe_event_display', $list_view_slug ),
				]
			);
		}

		if ( ! $wp_query = tribe_get_global_query_object() ) {
			return [];
		}

		$args = $wp_query->query_vars;

		if ( $list_view_slug === $args['eventDisplay'] ) {
			// When producing a List view iCal feed the `eventDate` is misleading, so we remove it.
			unset( $args['eventDate'] );

			// If passed a date, only observe it if it's in the future.
			if ( isset( $args['tribe-bar-date'] ) ) {
				$set_date = Dates::build_date_object( $args['tribe-bar-date'] );
				if ( $set_date < Dates::build_date_object() ) {
					unset( $args['tribe-bar-date'] );
				}
			}
		}

		return $this->get_events_list( $args, $wp_query );
	}

	/**
	 * Gets all events in the current month, matching those presented in month view
	 * by default (and therefore potentially including some events from the tail end
	 * of the previous month and start of the following month).
	 *
	 * We build a fresh 'custom'-type query here rather than taking advantage of the
	 * main query since page spoofing can render the actual query and results
	 * inaccessible (and it cannot be recovered via a query reset).
	 *
	 * @since 3.6.0
	 *
	 * @return array Events in the month.
	 */
	private function get_month_view_events() {

		if ( ! $wp_query = tribe_get_global_query_object() ) {
			return [];
		}

		$event_date = $wp_query->get( 'eventDate' );

		$month = empty( $event_date )
			? tribe_get_month_view_date()
			: $wp_query->get( 'eventDate' );

		$args = [
			'eventDisplay'   => 'custom',
			'start_date'     => \Tribe\Events\Views\V2\Views\Month_View::calculate_first_cell_date( $month ),
			'end_date'       => \Tribe\Events\Views\V2\Views\Month_View::calculate_final_cell_date( $month ),
			'posts_per_page' => -1,
			'hide_upcoming'  => true,
		];

		// Verify the Initial Category.
		if ( $wp_query->get( Tribe__Events__Main::TAXONOMY, false ) !== false ) {
			$args[ Tribe__Events__Main::TAXONOMY ] = $wp_query->get( Tribe__Events__Main::TAXONOMY );
		}

		/**
		 * Provides an opportunity to modify the query args used to build a list of events
		 * to export from month view.
		 *
		 * This could be useful where its desirable to limit the exported data set to only
		 * those events taking place in the specific month being viewed (rather than an exact
		 * match of the events shown in month view itself, which may include events from
		 * adjacent months).
		 *
		 * @since 3.10.1
		 *
		 * @var array  $args  The query arguments.
		 * @var string $month The month the query is for in YYYY-MM-DD format for the first day of the month.
		 */
		$args = (array) apply_filters( 'tribe_ical_feed_month_view_query_args', $args, $month );

		return tribe_get_events( $args );
	}

	/**
	 * Set the headers before the file is delivered.
	 *
	 * @since 4.9.4
	 */
	protected function set_headers() {
		header( 'HTTP/1.0 200 OK', true, 200 );
		header( 'Content-type: text/calendar; charset=UTF-8' );
		header(
			'Content-Disposition: attachment; filename="' . $this->get_file_name() . '"'
		);
	}

	/**
	 * Get the file name of the *.ics file.
	 *
	 * @since 4.9.4
	 * @since 5.16.0 - Add the iCal type to the filename so both ics and outlook ics are unique names.
	 *
	 * @return mixed The calendar name.
	 */
	protected function get_file_name() {
		$event_ids = wp_list_pluck( $this->events, 'ID' );
		$site      = sanitize_title( get_bloginfo( 'name' ) );
		$hash      = substr( md5( $this->type . implode( $event_ids ) ), 0, 11 );
		$filename  = sprintf( '%s-%s.ics', $site, $hash );

		/**
		 * Allows filtering the filename provided in the Content-Disposition header for iCal feeds.
		 *
		 * @since 4.9.4
		 *
		 * @var string       $filename
		 * @var WP_Post|null $post
		 */
		return apply_filters( 'tribe_events_ical_feed_filename', $filename, $this->post );
	}

	/**
	 * Get the full content of the *.ics file.
	 *
	 * @since 4.9.4
	 *
	 * @return string The full content of the *.ics file.
	 */
	protected function get_content() {
		$parts = [
			$this->get_start(),
			$this->get_timezones( $this->events ),
			$this->get_body( $this->events ),
			$this->get_end()
		];
		return implode( '', $parts );
	}

	/**
	 * Get the start of the .ics file.
	 *
	 * @since 4.9.4
	 * @since 5.16.0 - Add a check for iCal type to prevent Outlook ics from including X-WR-CALNAME.
	 *
	 * @return string The beginning of the iCal feed containing calendar information.
	 */
	protected function get_start() {
		$blog_home    = get_bloginfo( 'url' );
		$blog_name    = get_bloginfo( 'name' );

		$content  = "BEGIN:VCALENDAR\r\n";
		$content .= "VERSION:2.0\r\n";
		$content .= 'PRODID:-//' . $blog_name . ' - ECPv' . Tribe__Events__Main::VERSION . "//NONSGML v1.0//EN\r\n";
		$content .= "CALSCALE:GREGORIAN\r\n";
		$content .= "METHOD:PUBLISH\r\n";

		/**
		 * Allows for customizing the value of the generated iCal file's "X-WR-CALNAME:" property.
		 *
		 * @since 4.9.4
		 *
		 * @param string $blog_name The value to use for "X-WR-CALNAME"; defaults to value of get_bloginfo( 'name' ).
		 */
		$x_wr_calname = apply_filters( 'tribe_ical_feed_calname', $blog_name );

		if ( ! empty( $x_wr_calname ) && 'ical' === $this->type ) {
			$content .= 'X-WR-CALNAME:' . $x_wr_calname . "\r\n";
		}

		$content .= 'X-ORIGINAL-URL:' . $blog_home . "\r\n";
		$content .= "X-WR-CALDESC:" . sprintf( esc_html_x( 'Events for %s', 'iCal feed description', 'the-events-calendar' ), $blog_name ) . "\r\n";

		/**
		 * Allows for customization of the various properties at the top of the generated iCal file.
		 *
		 * @since 4.9.4
		 *
		 * @param string $content Existing properties atop the file; starts at "BEGIN:VCALENDAR", ends at "X-WR-CALDESC".
		 */
		return apply_filters( 'tribe_ical_properties', $content );
	}

	/**
	 * Add the VTIMEZONE groups to the file.
	 *
	 * @since 4.9.4
	 * @since 6.10.2 Make sure that each time zone definition has its own group.
	 * @since 6.15.6 Adjust the time zone definition to include the DST transitions for a year before and after.
	 *
	 * @param array $events An array with all the events.
	 *
	 * @return string The string containing all the time zone information needed in the iCal feed/file.
	 */
	protected function get_timezones( $events = [] ) {
		$timezones = $this->parse_timezones( $events );

		if ( empty( $timezones ) ) {
			return '';
		}

		$item = [];
		foreach ( $timezones as $row ) {
			/** @var DateTimeZone $timezone */
			$timezone = $row['timezone'];

			$ordered = [
				'start' => wp_list_pluck( $row['events'], 'start_year' ),
				'end'   => wp_list_pluck( $row['events'], 'end_year' ),
			];

			sort( $ordered['start'] );
			rsort( $ordered['end'] );

			$ordered['start'] = array_values( $ordered['start'] );
			$ordered['end']   = array_values( $ordered['end'] );

			$start_year = date( 'Y', reset( $ordered['start'] ) );  // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
			$end_year   = date( 'Y', reset( $ordered['end'] ) );  // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date

			/**
			 * Filters the number of years to extend timezone transitions in each direction.
			 *
			 * @since 6.15.6
			 *
			 * @param int    $years    The number of years to extend before and after event years. Default 1.
			 * @param string $timezone The timezone identifier (e.g., 'Europe/Berlin').
			 * @param array  $events   The events being processed for this timezone.
			 */
			$extend_years = (int) apply_filters( 'tec_events_ical_timezone_extend_years', 1, $timezone->getName(), $row['events'] );

			// Ensure we have a valid positive integer.
			$extend_years = max( 1, $extend_years );

			// Extend the range by the specified number of years in each direction.
			$extended_start = strtotime( 'first day of january ' . ( $start_year - $extend_years ) );
			$extended_end   = strtotime( 'last day of december ' . ( $end_year + $extend_years ) );

			$start = $extended_start;
			$end   = $extended_end;

			if ( empty( $start ) || empty( $end ) ) {
				continue;
			}

			$transitions = $timezone->getTransitions( $start, $end );
			if ( is_array( $transitions ) && count( $transitions ) === 1 ) {
				$transitions[] = array_values( $transitions )[ 0 ];
			}

			$item[] = 'BEGIN:VTIMEZONE';

			$id = $timezone->getName();
			$item[] = 'TZID:' .  $id;

			$last_transition = null;
			foreach ( $transitions as $i => $transition ) {
				if ( $i === 0 ) {
					$last_transition = $transition;
					continue;
				}

				$type = 'STANDARD';
				if ( $transition['isdst'] ) {
					$type = 'DAYLIGHT';
				}
				$item[] = 'BEGIN:' . $type;
				$item[] = 'TZOFFSETFROM:' . $this->format_offset( $last_transition['offset'] );
				$item[] = 'TZOFFSETTO:' . $this->format_offset( $transition['offset'] );
				$item[] = 'TZNAME:' . $transition['abbr'];
				try {
					$start = new DateTime( $transition['time'], $timezone );
					$item[] = 'DTSTART:' . $start->format( "Ymd\THis" );
				} catch ( Exception $e ) {
					// @todo [BTRIA-610]: report this exception
				}
				$item[] = 'END:' . $type;
				$last_transition = $transition;
			}

			$item[] = 'END:VTIMEZONE';
		}

		/**
		 * Allows customization of the "VTIMEZONE" items rendered inside an iCal export file.
		 *
		 * @since 4.9.4
		 *
		 * @param array $item      The various iCal file format components of the time zones.
		 * @param array $timezones The various iCal time zone components.
		 */
		$item = apply_filters( 'tribe_ical_feed_vtimezone', $item, $timezones );

		return implode( "\r\n", $item ) . "\r\n";
	}

	/**
	 * Create an array of arrays with unique time zones for all the events, every time zone has
	 * the following fields:
	 *
	 * - timezone - The Timezone Object.
	 * - events - List with all the events.
	 *
	 * @since 4.9.4
	 *
	 * @param $events array An array with all the events to parse the timezones.
	 *
	 * @return array An array with all time zones as keys, and array of events in the respective time zones as values.
	 */
	protected function parse_timezones( $events ) {
		$data = [];
		foreach ( $events as $event ) {
			if ( ! $event instanceof WP_Post ) {
				continue;
			}

			$timezone = $this->get_timezone( $event );

			if ( ! isset( $data[ $timezone ] ) ) {
				$data[ $timezone ] = [
					'timezone' => Tribe__Events__Timezones::build_timezone_object( $timezone ),
					'events' => [],
				];
			}

			$data[ $timezone ]['events'][] = [
				'event' => $event,
				'start_year' => strtotime( 'first day of january', tribe_get_start_date( $event, false, 'U' ) ),
				'end_year' => strtotime( 'last day of december', tribe_get_end_date( $event, false, 'U' ) ),
			];
		}
		return $data;
	}

	/**
	 * Format the offset into hours and minutes from seconds.
	 *
	 * @since 4.9.4
	 *
	 * @param int $offset Offset to UTC in seconds.
	 *
	 * @return string The offset to UTC in hours and minutes in ±HHMM format.
	 */
	protected function format_offset( $offset ) {
		$hours   = intval( $offset / 60 / 60 );
		$minutes = abs( $offset ) / 60 - intval( abs( $offset ) / 60 / 60 ) * 60;
		$format  = "+%02d%02d";
		if ( $hours < 0 ) {
			$format = "%03d%02d";
		}

		return sprintf( $format, $hours, $minutes );
	}

	/**
	 * Get the body with all the events of the .ics file.
	 *
	 * @since 4.9.4
	 * @since 5.1.6 - Utilize get_ical_output_for_an_event() to get the iCal output.
	 *
	 * @param array $posts The array of events to generate the iCal content for.
	 *
	 * @return string A string containing all the event information needed in the iCal feed/file.
	 */
	protected function get_body( $posts = [] ) {
		$tec         = Tribe__Events__Main::instance();
		$events      = '';

		foreach ( $posts as $event_post ) {

			$item = $this->get_ical_output_for_an_event( $event_post, $tec );

			$events .= "BEGIN:VEVENT\r\n" . implode( "\r\n", $item ) . "\r\nEND:VEVENT\r\n";
		}

		return $events;
	}

	/**
	 * Sanitize organizer name.
	 *
	 * @since 6.2.2
	 *
	 * @param string $name The organizer name (post title).
	 *
	 * @return string The sanitized organizer name.
	 */
	private function sanitize_organizer_name( string $name ): string {
		// Characters not allowed in organizer name: CTLs, DQUOTE, ";", ":", ","
		$sanitized_name = rawurlencode( $name );

		// Array of characters to allow in organizer name.
		$chars = [ ' ', '&', '!', '?', "'", '*', '(', ')', '@', '#', '$', '%', '^', '+', '=', '{', '}', '[', ']', '|', '\\', '/', '<', '>', '`', '~' ];
		$encoded_chars = array_map( 'rawurlencode', $chars );

		// Since single quotes are allowed, let's convert any double quotes to single quotes.
		$encoded_chars[] = rawurlencode( '"' );
		$chars[] = "'";

		return str_replace( $encoded_chars, $chars, $sanitized_name );
	}

	/**
	 * Get the iCal Output for the provided event object.
	 *
	 * @since 5.1.6
	 * @since 6.2.2   Sanitize organizer name using new method.
	 *
	 * @param \WP_Post             $event_post The event post object.
	 * @param \Tribe__Events__Main $tec        An instance of the main TEC Class.
	 *
	 * @return array An array of iCal output fields.
	 */
	public function get_ical_output_for_an_event( $event_post, Tribe__Events__Main $tec ) {
		// Add fields to iCal output.
		$item              = [];
		$access_to_content = self::has_access_to_see_event_content( $event_post );
		$full_format       = 'Ymd\THis';
		$utc_format        = 'Ymd\THis\Z';
		$all_day           = ( 'yes' === get_post_meta( $event_post->ID, '_EventAllDay', true ) );
		$time              = (object) [
			'start'    => tribe_get_start_date( $event_post->ID, false, 'U' ),
			'end'      => tribe_get_end_date( $event_post->ID, false, 'U' ),
			'modified' => Tribe__Date_Utils::wp_strtotime( $event_post->post_modified ),
			'created'  => Tribe__Date_Utils::wp_strtotime( $event_post->post_date ),
		];

		$type   = 'DATE-TIME';
		$format = $full_format;

		if ( $all_day ) {
			$type   = 'DATE';
			$format = 'Ymd';
		}

		$tzoned = (object) [
			'start'    => Tribe__Date_Utils::build_date_object( $time->start )->format( $format ),
			'end'      => Tribe__Date_Utils::build_date_object( $time->end )->format( $format ),
			'modified' => Tribe__Date_Utils::build_date_object( $time->modified )->format( $utc_format ),
			'created'  => Tribe__Date_Utils::build_date_object( $time->created )->format( $utc_format ),
		];

		$dtstart = $tzoned->start;
		$dtend   = $tzoned->end;

		if ( 'DATE' === $type ) {
			// For all day events dtend should always be +1 day.
			if ( $all_day ) {
				$dtend = Tribe__Date_Utils::build_date_object( strtotime( '+1 day', strtotime( $dtend ) ) )->format( $format );
			}

			$item['DTSTART'] = 'DTSTART;VALUE=' . $type . ':' . $dtstart;
			$item['DTEND']   = 'DTEND;VALUE=' . $type . ':' . $dtend;
		} else {
			// Are we using the site-wide timezone or the local event timezone?
			$timezone_name = $this->get_timezone( $event_post );
			$timezone      = Tribe__Events__Timezones::build_timezone_object( $timezone_name );

			$item['DTSTART'] = 'DTSTART;TZID=' . $timezone->getName() . ':' . $dtstart;
			$item['DTEND']   = 'DTEND;TZID=' . $timezone->getName() . ':' . $dtend;
		}

		$item['DTSTAMP']       = 'DTSTAMP:' . Tribe__Date_Utils::build_date_object()->format( $full_format );
		$item['CREATED']       = 'CREATED:' . $tzoned->created;
		$item['LAST-MODIFIED'] = 'LAST-MODIFIED:' . $tzoned->modified;
		$item['UID']           = 'UID:' . $event_post->ID . '-' . $time->start . '-' . $time->end . '@' . wp_parse_url( home_url( '/' ), PHP_URL_HOST );
		$item['SUMMARY']       = 'SUMMARY:' . $this->replace( wp_strip_all_tags( $event_post->post_title ) );

		if ( $access_to_content ) {
			/**
			 * Allows filtering the event description in the iCal feed.
			 *
			 * @since 4.9.4
			 *
			 * @param string $content The event description (post content).
			 */
			$content = apply_filters( 'the_content', tribe( 'editor.utils' )->exclude_tribe_blocks( $event_post->post_content ) );
		} else {
			$content = _x( 'Content is protected.', 'Description in iCal content for events with hidden/protected content.', 'the-events-calendar' );
			/**
			 * Filters the password protected description for iCal event descriptions that are displayed in the output.
			 *
			 * @since 6.1.3
			 *
			 * @param string $content The replaced message that will display in the ical event description.
			 */
			$content = apply_filters( 'tec_events_ical_protected_content_description', $content );
		}

		$item['DESCRIPTION'] = 'DESCRIPTION:' . $this->replace( wp_strip_all_tags( str_replace( '</p>', '</p> ', $content ) ) );

		$item['URL'] = 'URL:' . get_permalink( $event_post->ID );

		// Add location if available.
		$location = \Tribe__Events__Venue::get_address_full_string( $event_post->ID );
		if ( ! empty( $location ) ) {
			$str_location = $this->replace( $location, [ ',', "\n" ], [ '\,', '\n' ] );

			$item['LOCATION'] = 'LOCATION:' . $str_location;
		}

		// Add categories if available.
		$event_cats = wp_get_object_terms( $event_post->ID, Tribe__Events__Main::TAXONOMY, [ 'fields' => 'names' ] );

		if ( ! is_wp_error( $event_cats ) && ! empty( $event_cats ) ) {
			$item['CATEGORIES'] = 'CATEGORIES:' . $this->html_decode( join( ',', (array) $event_cats ) );
		}

		// Add featured image if available.
		if ( has_post_thumbnail( $event_post->ID ) && $access_to_content ) {
			$thumbnail_id        = get_post_thumbnail_id( $event_post->ID );
			$thumbnail_url       = wp_get_attachment_url( $thumbnail_id );
			$thumbnail_mime_type = get_post_mime_type( $thumbnail_id );

			/**
			 * Allow for customization of an individual iCal-exported event's thumbnail.
			 *
			 * @since 4.6.2
			 *
			 * @param string $string  This thumbnail's iCal-formatted "ATTACH;" string with the thumbnail mime type and URL.
			 * @param int    $post_id The ID of the event this thumbnail belongs to.
			 */
			$item['ATTACH'] = apply_filters( 'tribe_ical_feed_item_thumbnail', sprintf( 'ATTACH;FMTTYPE=%s:%s', $thumbnail_mime_type, $thumbnail_url ), $event_post->ID );
		}

		// Add organizer if available.
		$organizer_email = tribe_get_organizer_email( $event_post->ID, false );
		if ( $organizer_email ) {
			$organizer_id = tribe_get_organizer_id( $event_post->ID );
			$organizer    = get_post( $organizer_id );

			if ( $organizer_id ) {
				$sanitized_name = $this->sanitize_organizer_name( $organizer->post_title );
				$item['ORGANIZER'] = sprintf( 'ORGANIZER;CN="%s":MAILTO:%s', $sanitized_name, $organizer_email );
			} else {
				$item['ORGANIZER'] = sprintf( 'ORGANIZER:MAILTO:%s', $organizer_email );
			}
		}

		/**
		 * Allow for customization of an individual "VEVENT" item to be rendered inside an iCal export file.
		 *
		 * @since 4.6.2
		 *
		 * @param array  $item       The various iCal file format components of this specific event item.
		 * @param object $event_post The WP_Post of this event.
		 */
		return apply_filters( 'tribe_ical_feed_item', $item, $event_post );
	}

	/**
	 * Replace the text and encode the text before doing the replacement.
	 *
	 * @since 4.9.4
	 *
	 * @param string $text        The text to be replaced.
	 * @param array  $search      What elements to search to replace.
	 * @param array  $replacement New values used to replace.
	 *
	 * @return mixed
	 */
	protected function replace( $text = '', $search = [], $replacement = [] ) {
		$search = empty( $search ) ? [ ',', "\n", "\r" ] : $search;
		$replacement = empty( $replacement ) ? [ '\,', '\n', '' ] : $replacement;
		return str_replace( $search, $replacement, $this->html_decode( $text ) );
	}

	/**
	 * Apply html_entity_decode on a string using ENT_QUOTES style.
	 *
	 * @since 4.9.4
	 *
	 * @param string $text The text to be encoded.
	 *
	 * @return string The encoded text.
	 */
	protected function html_decode( $text = '' ) {
		return html_entity_decode( $text, ENT_QUOTES );
	}

	/**
	 * Return the time zone name associated with the event.
	 *
	 * @since 4.9.4
	 *
	 * @param WP_Post $event The event post.
	 *
	 * @return string The time zone string of the event in the format of 'America/New_York'.
	 */
	protected function get_timezone( $event ) {
		return Tribe__Events__Timezones::EVENT_TIMEZONE === Tribe__Events__Timezones::mode()
			? Tribe__Events__Timezones::get_event_timezone_string( $event->ID )
			: Tribe__Events__Timezones::wp_timezone_string();
	}

	/**
	 * Return the end of the .ics file.
	 *
	 * @since 4.9.4
	 *
	 * @return string
	 */
	protected function get_end() {
		return 'END:VCALENDAR';
	}

	/**
	 * Get a list of events, the function makes sure it uses the default values used on the main events page,
	 * so if is called from a different location like a page or post (shortcode) it will retain the original values
	 * to generate the events feed.
	 *
	 * @since 4.6.11
	 *
	 * @param array $args  The WP_Query arguments.
	 * @param mixed $query A WP_Query object or null if none.
	 *
	 * @return array
	 */
	protected function get_events_list( $args = [], $query = null ) {
		/**
		 * Filter the arguments used to construct the call to get the list of events.
		 *
		 * @since 4.6.11
		 *
		 * @param array $args Arguments used in WP_Query call.
		 */
		$args = apply_filters( 'tribe_events_ical_events_list_args', $args );

		/**
		 * Filter the Query object used to get the list of events used to populate the feed.
		 *
		 * @since 4.6.11
		 *
		 * @param mixed $query a WP_Query or null.
		 */
		$query = apply_filters( 'tribe_events_ical_events_list_query', $query );

		$count = $this->feed_posts_per_page();
		$query_posts_per_page = 0;
		if ( $query instanceof WP_Query ) {
			$query_posts_per_page = $query->get( 'posts_per_page' );
		}

		$list = [];
		// When `posts_per_page` is set to `-1` we can slice.
		if ( $query_posts_per_page >= 0 && $count > $query_posts_per_page ) {
			$args['posts_per_page'] = $count;
			$events_query = tribe_get_events( wp_parse_args( $args, [ 'posts_per_page' => $this->feed_posts_per_page() ] ), true );
			$list = $events_query->get_posts();
		} elseif ( $query instanceof WP_Query ) {
			$list = array_slice( $query->posts, 0, $count );
		}
		return $list;
	}

	/**
	 * Get the number of posts per page to be used in the iCal feed, make sure it passes the value via the filter
	 * tribe_ical_feed_posts_per_page and validates the number is greater than 0.
	 *
	 * @since 4.6.11
	 *
	 * @return int
	 */
	public function feed_posts_per_page() {
		/**
		 * Filters the number of upcoming events the iCal feed should export.
		 *
		 * This filter allows developers to override the pagination setting and the default value
		 * to export a number of events that's inferior or superior to the one shown on the page.
		 * The minimum value is 1.
		 *
		 * @since 4.6.11
		 *
		 * @param int $count The number of upcoming events that should be exported in the
		 *                   feed, defaults to 30.
		 */
		$count = apply_filters( 'tribe_ical_feed_posts_per_page', $this->feed_default_export_count );

		return is_numeric( $count ) && is_int( $count ) && $count > 0
			? $count
			: $this->feed_default_export_count;
	}

	/**
	 * Gets the number of events that should be exported when generating the iCal feed.
	 *
	 * @since 4.3.3
	 *
	 * @return int
	 */
	public function get_feed_default_export_count() {
		return $this->feed_default_export_count;
	}

	/**
	 * Sets the number of events that should be exported when generating the iCal feed.
	 *
	 * @since 4.3.3
	 *
	 * @param int $count
	 */
	public function set_feed_default_export_count( $count ) {
		$this->feed_default_export_count = $count;
	}

	/**
	 * Prevent Views V2 from redirecting when exporting iCal or Outlook ICS feeds.
	 *
	 * @since 6.15.6
	 *
	 * @param bool $should_redirect Whether the request should redirect.
	 *
	 * @return bool Whether the redirect should occur.
	 */
	public function prevent_redirect_on_ical( $should_redirect ) {
		$is_ical_request         = (bool) tec_get_request_var( 'ical', false );
		$is_outlook_ical_request = (bool) tec_get_request_var( 'outlook_ical', false );

		// Bail early if exporting iCal or Outlook ICS feeds to prevent redirect.
		if ( $is_ical_request || $is_outlook_ical_request ) {
			return false;
		}

		return $should_redirect;
	}
}
