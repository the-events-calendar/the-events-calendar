<?php

/**
 *    Class that implements the export to iCal functionality
 *  both for list and single events
 */
class Tribe__Events__iCal {

	/**
	 * Set all the filters and actions necessary for the operation of the iCal generator.
	 * @static
	 */
	public static function init() {
		add_filter( 'tribe_events_after_footer', array( __CLASS__, 'maybe_add_link' ), 10, 1 );
		add_action( 'tribe_events_single_event_after_the_content', array( __CLASS__, 'single_event_links' ) );
		add_action( 'tribe_tec_template_chooser', array( __CLASS__, 'do_ical_template' ) );
		add_filter( 'tribe_get_ical_link', array( __CLASS__, 'day_view_ical_link' ), 20, 1 );
		add_action( 'wp_head', array( __CLASS__, 'set_feed_link' ), 2, 0 );
	}

	/**
	 * outputs a <link> element for the ical feed
	 */
	public static function set_feed_link() {
		if ( ! current_theme_supports( 'automatic-feed-links' ) ) {
			return;
		}
		$separator  = _x( '&raquo;', 'feed link', 'tribe-events-calendar' );
		$feed_title = sprintf( __( '%1$s %2$s iCal Feed', 'tribe-events-calendar' ), get_bloginfo( 'name' ), $separator );

		printf( '<link rel="alternate" type="text/calendar" title="%s" href="%s" />', esc_attr( $feed_title ), esc_url( tribe_get_ical_link() ) );
		echo "\n";
	}

	/**
	 * Returns the url for the iCal generator for lists of posts
	 * @static
	 * @return string
	 */
	public static function get_ical_link() {
		$tec = Tribe__Events__Main::instance();

		return trailingslashit( $tec->getLink( 'home' ) ) . '?ical=1';
	}

	/**
	 * Make sure ical link has the date in the URL instead of "today" on day view
	 *
	 * @param $link
	 *
	 * @return string
	 */
	public static function day_view_ical_link( $link ) {
		if ( tribe_is_day() ) {
			global $wp_query;
			$day  = $wp_query->get( 'start_date' );
			$link = trailingslashit( esc_url( trailingslashit( tribe_get_day_link( $day ) ) . '?ical=1' ) );
		}

		return $link;
	}

	/**
	 * Generates the markup for iCal and gCal single event links
	 *
	 * @return void
	 **/
	public static function single_event_links() {

		// don't show on password protected posts
		if ( is_single() && post_password_required() ) {
			return;
		}
		echo '<div class="tribe-events-cal-links">';
		echo '<a class="tribe-events-gcal tribe-events-button" href="' . Tribe__Events__Main::instance()->esc_gcal_url( tribe_get_gcal_link() ) . '" title="' . esc_attr__( 'Add to Google Calendar', 'tribe-events-calendar' ) . '">+ ' . esc_html__( 'Google Calendar', 'tribe-events-calendar' ) . '</a>';
		echo '<a class="tribe-events-ical tribe-events-button" href="' . esc_url( tribe_get_single_ical_link() ) . '" title="' . esc_attr__( 'Download .ics file', 'tribe-events-calendar' ) . '" >+ ' . esc_html__( 'iCal Export', 'tribe-events-calendar' ) . '</a>';
		echo '</div><!-- .tribe-events-cal-links -->';
	}

	/**
	 * Generates the markup for the "iCal Import" link for the views.
	 */
	public static function maybe_add_link() {
		global $wp_query;
		$show_ical = apply_filters( 'tribe_events_list_show_ical_link', true );

		if ( ! $show_ical ) {
			return;
		}
		if ( tribe_is_month() && ! tribe_events_month_has_events() ) {
			return;
		}
		if ( is_single() || ! have_posts() ) {
			return;
		}

		$tec = Tribe__Events__Main::instance();

		$view = $tec->displaying;
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX && isset( $wp_query->query_vars['eventDisplay'] ) ) {
			$view = $wp_query->query_vars['eventDisplay'];
		}

		switch ( strtolower( $view ) ) {
			case 'month':
				$modifier = sprintf( __( "Month's %s", 'tribe-events-calendar' ), tribe_get_event_label_plural() );
				break;
			case 'week':
				$modifier = sprintf( __( "Week's %s", 'tribe-events-calendar' ), tribe_get_event_label_plural() );
				break;
			case 'day':
				$modifier = sprintf( __( "Day's %s", 'tribe-events-calendar' ), tribe_get_event_label_plural() );
				break;
			default:
				$modifier = sprintf( __( 'Listed %s', 'tribe-events-calendar' ), tribe_get_event_label_plural() );
				break;
		}

		$text  = apply_filters( 'tribe_events_ical_export_text', __( 'Export', 'tribe-events-calendar' ) . ' ' . $modifier );
		$title = __( 'Use this to share calendar data with Google Calendar, Apple iCal and other compatible apps', 'tribe-events-calendar' );
		$ical  = '<a class="tribe-events-ical tribe-events-button" title="' . $title . '" href="' . esc_url( tribe_get_ical_link() ) . '">+ ' . $text . '</a>';

		echo $ical;
	}

	/**
	 * Executes the iCal generator when the appropiate query_var or $_GET is setup
	 *
	 * @static
	 *
	 * @param $template
	 */
	public static function do_ical_template( $template ) {
		// hijack to iCal template
		if ( get_query_var( 'ical' ) || isset( $_GET['ical'] ) ) {
			global $wp_query;
			if ( is_single() ) {
				self::generate_ical_feed( $wp_query->post, null );
			} else {
				self::generate_ical_feed();
			}
			die();
		}
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
	 * @return array events in the month
	 */
	private static function get_month_view_events() {
		global $wp_query;

		$event_date = $wp_query->get( 'eventDate' );

		$month = empty( $event_date )
			? tribe_get_month_view_date()
			: $wp_query->get( 'eventDate' );

		$args = array(
			'eventDisplay' => 'custom',
			'start_date'   => Tribe__Events__Template__Month::calculate_first_cell_date( $month ),
			'end_date'     => Tribe__Events__Template__Month::calculate_final_cell_date( $month ),
			'posts_per_page' => -1,
			'hide_upcoming' => true,
		);

		/**
		 * Provides an opportunity to modify the query args used to build a list of events
		 * to export from month view.
		 *
		 * This could be useful where its desirable to limit the exported data set to only
		 * those events taking place in the specific month being viewed (rather than an exact
		 * match of the events shown in month view itself, which may include events from
		 * adjacent months).
		 *
		 * @var array  $args
		 * @var string $month
		 */
		$args = (array) apply_filters( 'tribe_ical_feed_month_view_query_args', $args, $month );

		return tribe_get_events( $args );
	}

	/**
	 * Generates the iCal file
	 *
	 * @static
	 *
	 * @param int|null $post If you want the ical file for a single event
	 */
	public static function generate_ical_feed( $post = null ) {

		$tec         = Tribe__Events__Main::instance();
		$events      = '';
		$blogHome    = get_bloginfo( 'url' );
		$blogName    = get_bloginfo( 'name' );

		if ( $post ) {
			$events_posts   = array();
			$events_posts[] = $post;
		} else {
			if ( tribe_is_month() ) {
				$events_posts = self::get_month_view_events();
			} else {
				global $wp_query;
				$events_posts = $wp_query->posts;
			}
		}

		$event_ids = wp_list_pluck( $events_posts, 'ID' );

		foreach ( $events_posts as $event_post ) {
			// add fields to iCal output
			$item = array();

			$full_format = 'Ymd\THis\Z';
			$time = (object) array(
				'start' => self::wp_strtotime( $event_post->EventStartDate ),
				'end' => self::wp_strtotime( $event_post->EventEndDate ),
				'modified' => self::wp_strtotime( $event_post->post_modified ),
				'created' => self::wp_strtotime( $event_post->post_date ),
			);

			if ( 'yes' == get_post_meta( $event_post->ID, '_EventAllDay', true ) ) {
				$type = 'DATE';
				$format = 'Ymd';
			} else {
				$type = 'DATE-TIME';
				$format = $full_format;
			}

			$tzoned = (object) array(
				'start' => date( $format, $time->start ),
				'end' => date( $format, $time->end ),
				'modified' => date( $full_format, $time->modified ),
				'created' => date( $full_format, $time->created ),
			);

			if ( 'DATE' === $type ){
				$item[] = "DTSTART;VALUE=$type:" . $tzoned->start;
				$item[] = "DTEND;VALUE=$type:" . $tzoned->end;
			} else {
				$item[] = 'DTSTART:' . $tzoned->start;
				$item[] = 'DTEND:' . $tzoned->end;
			}

			$item[] = 'DTSTAMP:' . date( $full_format, time() );
			$item[] = 'CREATED:' . $tzoned->created;
			$item[] = 'LAST-MODIFIED:' . $tzoned->modified;
			$item[] = 'UID:' . $event_post->ID . '-' . $time->start . '-' . $time->end . '@' . parse_url( home_url( '/' ), PHP_URL_HOST );
			$item[] = 'SUMMARY:' . str_replace( array( ',', "\n", "\r", "\t" ), array( '\,', '\n', '', '\t' ), html_entity_decode( strip_tags( $event_post->post_title ), ENT_QUOTES ) );
			$item[] = 'DESCRIPTION:' . str_replace( array( ',', "\n", "\r", "\t" ), array( '\,', '\n', '', '\t' ), html_entity_decode( strip_tags( $event_post->post_content ), ENT_QUOTES ) );
			$item[] = 'URL:' . get_permalink( $event_post->ID );

			// add location if available
			$location = $tec->fullAddressString( $event_post->ID );
			if ( ! empty( $location ) ) {
				$str_location = str_replace( array( ',', "\n" ), array( '\,', '\n' ), html_entity_decode( $location, ENT_QUOTES ) );

				$item[] = 'LOCATION:' .  $str_location;
			}

			// add geo coordinates if available
			if ( class_exists( 'Tribe__Events__Pro__Geo_Loc' ) ) {
				$long = Tribe__Events__Pro__Geo_Loc::instance()->get_lng_for_event( $event_post->ID );
				$lat  = Tribe__Events__Pro__Geo_Loc::instance()->get_lat_for_event( $event_post->ID );
				if ( ! empty( $long ) && ! empty( $lat ) ) {
					$item[] = sprintf( 'GEO:%s;%s', $lat, $long );

					$str_title = str_replace( array( ',', "\n" ), array( '\,', '\n' ), html_entity_decode( tribe_get_address( $event_post->ID ), ENT_QUOTES ) );

					if ( ! empty( $str_title ) && ! empty( $str_location ) ) {
						$item[] =
							'X-APPLE-STRUCTURED-LOCATION;VALUE=URI;X-ADDRESS=' . str_replace( '\,', '', trim( $str_location ) ) . ';' .
							'X-APPLE-RADIUS=500;' .
							'X-TITLE=' . trim( $str_title ) . ':geo:' . $long . ',' . $lat;
					}
				}
			}

			// add categories if available
			$event_cats = (array) wp_get_object_terms( $event_post->ID, Tribe__Events__Main::TAXONOMY, array( 'fields' => 'names' ) );
			if ( ! empty( $event_cats ) ) {
				$item[] = 'CATEGORIES:' . html_entity_decode( join( ',', $event_cats ), ENT_QUOTES );
			}

			// add featured image if available
			if ( has_post_thumbnail( $event_post->ID ) ) {
				$thumbnail_id        = get_post_thumbnail_id( $event_post->ID );
				$thumbnail_url       = wp_get_attachment_url( $thumbnail_id );
				$thumbnail_mime_type = get_post_mime_type( $thumbnail_id );
				$item[]              = apply_filters( 'tribe_ical_feed_item_thumbnail', sprintf( 'ATTACH;FMTTYPE=%s:%s', $thumbnail_mime_type, $thumbnail_url ), $event_post->ID );
			}

			// add organizer if available
			$organizer_email = tribe_get_organizer_email( $event_post->ID );
			if ( $organizer_email ) {
				$organizer_id = tribe_get_organizer_id( $event_post->ID );
				$organizer = get_post( $organizer_id );

				if ( $organizer_id ) {
					$item[] = sprintf( 'ORGANIZER;CN="%s":MAILTO:%s', rawurlencode( $organizer->post_title ), $organizer_email );
				} else {
					$item[] = sprintf( 'ORGANIZER:MAILTO:%s', $organizer_email );
				}
			}

			$item = apply_filters( 'tribe_ical_feed_item', $item, $event_post );

			$events .= "BEGIN:VEVENT\r\n" . implode( "\r\n", $item ) . "\r\nEND:VEVENT\r\n";
		}

		header( 'Content-type: text/calendar; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="ical-event-' . implode( $event_ids ) . '.ics"' );
		$content = "BEGIN:VCALENDAR\r\n";
		$content .= "VERSION:2.0\r\n";
		$content .= 'PRODID:-//' . $blogName . ' - ECPv' . Tribe__Events__Main::VERSION . "//NONSGML v1.0//EN\r\n";
		$content .= "CALSCALE:GREGORIAN\r\n";
		$content .= "METHOD:PUBLISH\r\n";
		$content .= 'X-WR-CALNAME:' . apply_filters( 'tribe_ical_feed_calname', $blogName ) . "\r\n";
		$content .= 'X-ORIGINAL-URL:' . $blogHome . "\r\n";
		$content .= 'X-WR-CALDESC:Events for ' . $blogName . "\r\n";
		$content = apply_filters( 'tribe_ical_properties', $content );
		$content .= $events;
		$content .= 'END:VCALENDAR';
		echo $content;

		exit;

	}

	/**
	 * Converts a locally-formatted date to a unix timestamp. This is a drop-in
	 * replacement for `strtotime()`, except that where strtotime assumes GMT, this
	 * assumes local time (as described below). If a timezone is specified, this
	 * function defers to strtotime().
	 *
	 * If there is a timezone_string available, the date is assumed to be in that
	 * timezone, otherwise it simply subtracts the value of the 'gmt_offset'
	 * option.
	 *
	 * @see strtotime()
	 * @uses get_option() to retrieve the value of 'gmt_offset'.
	 * @param string $string A date/time string. See `strtotime` for valid formats.
	 * @return int UNIX timestamp.
	 */
	private static function wp_strtotime( $string ) {
		// If there's a timezone specified, we shouldn't convert it
		try {
			$test_date = new DateTime( $string );
			if ( 'UTC' != $test_date->getTimezone()->getName() ) {
				return strtotime( $string );
			}
		} catch ( Exception $e ) {
			return strtotime( $string );
		}

		$tz = get_option( 'timezone_string' );
		if ( ! empty( $tz ) ) {
			$date = date_create( $string, new DateTimeZone( $tz ) );
			if ( ! $date ) {
				return strtotime( $string );
			}
			$date->setTimezone( new DateTimeZone( 'UTC' ) );
			return $date->format( 'U' );
		} else {
			$offset = (float) get_option( 'gmt_offset' );
			$seconds = intval( $offset * HOUR_IN_SECONDS );
			$timestamp = strtotime( $string ) - $seconds;
			return $timestamp;
		}
	}

}
