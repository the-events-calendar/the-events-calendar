<?php

/**
 *    Class that implements the export to iCal functionality
 *  both for list and single events
 */
class TribeiCal {

	/**
	 * Set all the filters and actions necessary for the operation of the iCal generator.
	 * @static
	 */
	public static function init() {
		add_filter( 'tribe_events_after_footer',                   array( __CLASS__, 'maybe_add_link'     ), 10, 1 );
		add_action( 'tribe_events_single_event_after_the_content', array( __CLASS__, 'single_event_links' )        );
		add_action( 'tribe_tec_template_chooser',                  array( __CLASS__, 'do_ical_template'   )        );
		add_filter( 'tribe_get_ical_link', 						   array( __CLASS__, 'day_view_ical_link' ), 20, 1 );
		add_action( 'wp_head',                                     array( __CLASS__, 'set_feed_link'      ), 2,  0 );
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

		printf( '<link rel="alternate" type="text/calendar" title="%s" href="%s" />', $feed_title, tribe_get_ical_link() );
		echo "\n";
	}

	/**
	 * Returns the url for the iCal generator for lists of posts
	 * @static
	 * @return string
	 */
	public static function get_ical_link() {
		$tec = TribeEvents::instance();

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
		echo '<a class="tribe-events-gcal tribe-events-button" href="' . tribe_get_gcal_link() . '" title="' . __( 'Add to Google Calendar', 'tribe-events-calendar' ) . '">+ ' . __( 'Google Calendar', 'tribe-events-calendar' ) . '</a>';
		echo '<a class="tribe-events-ical tribe-events-button" href="' . tribe_get_single_ical_link() . '" title="' . __( 'Download .ics file', 'tribe-events-calendar' ) . '" >+ ' . __( 'iCal Export', 'tribe-events-calendar' ) . '</a>';
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

		$tec = TribeEvents::instance();

		$view = $tec->displaying;
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX && isset( $wp_query->query_vars['eventDisplay'] ) ) {
			$view = $wp_query->query_vars['eventDisplay'];
		}

		switch ( strtolower( $view ) ) {
			case 'month':
				$modifier = sprintf( __( "Month's %s", "tribe-events-calendar" ), tribe_get_event_label_plural() );
				break;
			case 'week':
				$modifier = sprintf( __( "Week's %s", "tribe-events-calendar" ), tribe_get_event_label_plural() );
				break;
			case 'day':
				$modifier = sprintf( __( "Day's %s", "tribe-events-calendar" ), tribe_get_event_label_plural() );
				break;
			default:
				$modifier = sprintf( __( "Listed %s", "tribe-events-calendar" ), tribe_get_event_label_plural() );
				break;
		}

		$text  = apply_filters( 'tribe_events_ical_export_text', __( 'Export', 'tribe-events-calendar' ) . ' ' . $modifier );
		$title = __( 'Use this to share calendar data with Google Calendar, Apple iCal and other compatible apps', 'tribe-events-calendar' );
		$ical  = '<a class="tribe-events-ical tribe-events-button" title="' . $title . '" href="' . tribe_get_ical_link() . '">+ ' . $text . '</a>';

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
	 * Gets all events in the month
	 *
	 * @static
	 *
	 * @return array events in the month
	 */
	private static function get_month_view_events() {
		do_action( 'tribe_events_before_view' ); // this will trigger the month view query setup
		$events_posts = array();
		while ( tribe_events_have_month_days() ) {
			tribe_events_the_month_day();
			$month_day = tribe_events_get_current_month_day();
			if ( isset( $month_day['events'] ) && $month_day['total_events'] > 0 ) {
				$events_posts = array_merge( $month_day['events']->posts, $events_posts );
			}
		}

		return $events_posts;
	}

	/**
	 * Generates the iCal file
	 *
	 * @static
	 *
	 * @param int|null $post If you want the ical file for a single event
	 */
	public static function generate_ical_feed( $post = null ) {

		$tec         = TribeEvents::instance();
		$wp_timezone = get_option( 'timezone_string' );
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

		foreach ( $events_posts as $event_post ) {
			// add fields to iCal output
			$item   = array();

			$startDate = date( 'Ymd\THis', strtotime( $event_post->EventStartDate ) );
			$endDate   = date( 'Ymd\THis', strtotime( $event_post->EventEndDate ) );

			$tz_startDate = self::wp_date( 'Ymd\THis\Z', strtotime( $event_post->EventStartDate ) );
			$tz_endDate = self::wp_date( 'Ymd\THis\Z', strtotime( $event_post->EventEndDate ) );

			if ( get_post_meta( $event_post->ID, '_EventAllDay', true ) == 'yes' ) {
				$startDate = substr( $startDate, 0, 8 );
				$endDate   = substr( $endDate, 0, 8 );
				// endDate bumped ahead one day to counter iCal's off-by-one error
				$endDateStamp = strtotime( $endDate );
				$endDate      = date( 'Ymd', $endDateStamp + 86400 );
				$type         = 'DATE';
				$item[] = "DTSTART;VALUE=$type:" . $startDate;
				$item[] = "DTEND;VALUE=$type:" . $endDate;

			} else {
				$type = 'DATE-TIME';

				if ( ! empty( $wp_timezone ) ){
					$item[] = "DTSTART;TZID=\"{$wp_timezone}\":{$tz_startDate}";
				}
				$item[] = 'DTSTART:' . $tz_startDate;
				$item[] = 'DTSTART:' . $startDate;

				if ( ! empty( $wp_timezone ) ){
					$item[] = "DTEND;TZID=\"{$wp_timezone}\":{$tz_endDate}";
				}
				$item[] = 'DTEND:' . $tz_endDate;
				$item[] = 'DTEND:' . $endDate;
			}

			$description = str_replace( array( ',', "\n", "\r", "\t" ), array( '\,', '\n', '', '\t' ), strip_tags( $event_post->post_content ) );

			$item[] = 'DTSTAMP:' . date( 'Ymd\THis', time() );
			$item[] = 'CREATED:' . str_replace( array( '-', ' ', ':' ), array( '', 'T', '' ), $event_post->post_date );
			$item[] = 'LAST-MODIFIED:' . str_replace(
					array(
						'-',
						' ',
						':',
					), array(
						'',
						'T',
						'',
					), $event_post->post_modified
				);
			$item[] = 'UID:' . $event_post->ID . '-' . strtotime( $startDate ) . '-' . strtotime( $endDate ) . '@' . $blogHome;
			$item[] = 'SUMMARY:' . $event_post->post_title;
			$item[] = 'DESCRIPTION:' . str_replace( ',', '\,', $description );
			$item[] = 'URL:' . get_permalink( $event_post->ID );

			// add location if available
			$location = $tec->fullAddressString( $event_post->ID );
			if ( ! empty( $location ) ) {
				$str_location = str_replace( array( ',', "\n" ), array( '\,', '\n' ), html_entity_decode( $location, ENT_QUOTES ) );

				$item[] = 'LOCATION:' .  $str_location;
			}

			// add geo coordinates if available
			if ( class_exists( 'TribeEventsGeoLoc' ) ) {
				$long = TribeEventsGeoLoc::instance()->get_lng_for_event( $event_post->ID );
				$lat  = TribeEventsGeoLoc::instance()->get_lat_for_event( $event_post->ID );
				if ( ! empty( $long ) && ! empty( $lat ) ) {
					$item[] = sprintf( 'GEO:%s;%s', $long, $lat );

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
			$event_cats = (array) wp_get_object_terms( $event_post->ID, TribeEvents::TAXONOMY, array( 'fields' => 'names' ) );
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
				$organizer_name = tribe_get_organizer( $event_post->ID );
				if ( $organizer_name ) {
					$item[] = sprintf( 'ORGANIZER;CN=%s:MAILTO:%s', $organizer_name, $organizer_email );
				} else {
					$item[] = sprintf( 'ORGANIZER:MAILTO:%s', $organizer_email );
				}
			}

			$item = apply_filters( 'tribe_ical_feed_item', $item, $event_post );

			$events .= "BEGIN:VEVENT\r\n" . implode( "\r\n", $item ) . "\r\nEND:VEVENT\r\n";
		}

		header( 'Content-type: text/calendar; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="iCal-TribeEvents.ics"' );
		$content = "BEGIN:VCALENDAR\r\n";
		$content .= "VERSION:2.0\r\n";
		$content .= 'PRODID:-//' . $blogName . ' - ECPv' . TribeEvents::VERSION . "//NONSGML v1.0//EN\r\n";
		$content .= "CALSCALE:GREGORIAN\r\n";
		$content .= "METHOD:PUBLISH\r\n";
		$content .= 'X-WR-CALNAME:' . apply_filters( 'tribe_ical_feed_calname', $blogName ) . "\r\n";
		$content .= 'X-ORIGINAL-URL:' . $blogHome . "\r\n";
		$content .= 'X-WR-CALDESC:Events for ' . $blogName . "\r\n";
		if ( $wp_timezone ) {
			$content .= 'X-WR-TIMEZONE:' . $wp_timezone . "\r\n";
		}
		$content = apply_filters( 'tribe_ical_properties', $content );
		$content .= $events;
		$content .= 'END:VCALENDAR';
		echo $content;

		exit;

	}


	/**
	 * Returns a formatted date in the local timezone. This is a drop-in
	 * replacement for `date()`, except that the returned string will be formatted
	 * for the local timezone.
	 *
	 * If there is a timezone_string available, the date is assumed to be in that
	 * timezone, otherwise it simply subtracts the value of the 'gmt_offset'
	 * option.
	 *
	 * @uses get_option() to retrieve the value of 'gmt_offset'.
	 * @param string $format The format of the outputted date string.
	 * @param string $timestamp Optional. If absent, defaults to `time()`.
	 * @return string GMT version of the date provided.
	 */
	private static function wp_date( $format, $timestamp = false ) {
		$tz = get_option( 'timezone_string' );
		if ( ! $timestamp ) {
			$timestamp = time();
		}
		if ( $tz ) {
			$date = date_create( '@' . $timestamp );
			if ( ! $date ) {
				return gmdate( $format, 0 );
			}
			$date->setTimezone( new DateTimeZone( $tz ) );
			return $date->format( $format );
		} else {
			return date( $format, $timestamp + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) );
		}
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
		if ( $tz ) {
			$date = date_create( $string, new DateTimeZone( $tz ) );
			if ( ! $date ) {
				return strtotime( $string );
			}
			$date->setTimezone( new DateTimeZone( 'UTC' ) );
			return $date->getTimestamp();
		} else {
			return strtotime( $string ) - ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
		}
	}


}
