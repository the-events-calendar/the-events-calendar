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
				$modifier = __( "Month's Events", "tribe-events-calendar" );
				break;
			case 'week':
				$modifier = __( "Week's Events", "tribe-events-calendar" );
				break;
			case 'day':
				$modifier = __( "Day's Events", "tribe-events-calendar" );
				break;
			default:
				$modifier = __( "Listed Events", "tribe-events-calendar" );
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

			$startDate = $event_post->EventStartDate;
			$endDate   = $event_post->EventEndDate;

			// convert 2010-04-08 00:00:00 to 20100408T000000 or YYYYMMDDTHHMMSS
			$startDate = str_replace( array( '-', ' ', ':' ), array( '', 'T', '' ), $startDate );
			$endDate   = str_replace( array( '-', ' ', ':' ), array( '', 'T', '' ), $endDate );
			if ( get_post_meta( $event_post->ID, '_EventAllDay', true ) == 'yes' ) {
				$startDate = substr( $startDate, 0, 8 );
				$endDate   = substr( $endDate, 0, 8 );
				// endDate bumped ahead one day to counter iCal's off-by-one error
				$endDateStamp = strtotime( $endDate );
				$endDate      = date( 'Ymd', $endDateStamp + 86400 );
				$type         = 'DATE';
			} else {
				$type = 'DATE-TIME';
			}
			$description = preg_replace( "/[\n\t\r]/", ' ', strip_tags( $event_post->post_content ) );

			// add fields to iCal output
			$item   = array();
			$item[] = "DTSTART;VALUE=$type:" . $startDate;
			$item[] = "DTEND;VALUE=$type:" . $endDate;
			$item[] = 'DTSTAMP:' . date( 'Ymd\THis', time() );
			$item[] = 'CREATED:' . str_replace( array( '-', ' ', ':' ), array( '', 'T', '' ), $event_post->post_date );
			$item[] = 'LAST-MODIFIED:' . str_replace(
					array(
						'-',
						' ',
						':'
					), array(
						'',
						'T',
						''
					), $event_post->post_modified
				);
			$item[] = 'UID:' . $event_post->ID . '-' . strtotime( $startDate ) . '-' . strtotime( $endDate ) . '@' . $blogHome;
			$item[] = 'SUMMARY:' . $event_post->post_title;
			$item[] = 'DESCRIPTION:' . str_replace( ',', '\,', $description );
			$item[] = 'URL:' . get_permalink( $event_post->ID );

			// add location if available
			$location = $tec->fullAddressString( $event_post->ID );
			if ( ! empty( $location ) ) {
				$item[] = 'LOCATION:' . html_entity_decode( $location, ENT_QUOTES );
			}

			// add geo coordinates if available
			if ( class_exists( 'TribeEventsGeoLoc' ) ) {
				$long = TribeEventsGeoLoc::instance()->get_lng_for_event( $event_post->ID );
				$lat  = TribeEventsGeoLoc::instance()->get_lat_for_event( $event_post->ID );
				if ( ! empty( $long ) && ! empty( $lat ) ) {
					$item[] = sprintf( 'GEO:%s;%s', $long, $lat );
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
}
