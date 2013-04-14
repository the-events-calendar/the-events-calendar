<?php
/**
 *	Class that implements the export to iCal functionality
 *  both for list and single events
 */
class TribeiCal {

	/**
	 * Set all the filters and actions necessary for the operation of the iCal generator.
	 * @static
	 */
	public static function init() {
		add_filter( 'tribe_events_list_after_template',   array( __CLASS__, 'maybe_add_link' ), 30, 1 );
		add_filter( 'tribe_events_calendar_after_footer', array( __CLASS__, 'maybe_add_link' ), 30, 1 );
		add_filter( 'tribe_events_week_after_template',   array( __CLASS__, 'maybe_add_link' ), 30, 1 );
		add_action( 'tribe_tec_template_chooser',         array( __CLASS__, 'do_ical_template' ) );
	}


	/**
	 * Returns the url for the iCal generator for lists of posts
	 * @static
	 * @return string
	 */
	public static function get_ical_link() {
		$tec = TribeEvents::instance();
		return trailingslashit( $tec->getLink( 'home' ) ) . 'ical';
	}

	/**
	 * Generates the markup for the "iCal Import" link for the views.
	 *
	 * @static
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	public static function maybe_add_link( $content ) {
		global $wp_query;

		$show_ical = apply_filters( 'tribe_events_list_show_ical_link', true );

		if ( ! $show_ical )
			return $content;

		$tec = TribeEvents::instance();

		$view = $tec->displaying;
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX && isset( $wp_query->query_vars['eventDisplay'] ) )
			$view = $wp_query->query_vars['eventDisplay'];

		switch ( strtolower( $view ) ) {

			case 'month':
				$modifier = __( "Month's Events", "tribe-events-calendar-pro" );
				break;
			case 'week':
				$modifier = __( "Week's Events", "tribe-events-calendar-pro" );
				break;
			case 'day':
				$modifier = __( "Day's Events", "tribe-events-calendar-pro" );
				break;
			default:
				$modifier = __( "Listed Events", "tribe-events-calendar-pro" );
				break;
		}

		$ical    = '<a class="tribe-events-ical tribe-events-button" title="' . __( 'Import is filter/view sensitive', 'tribe-events-calendar' ) . '" href="' . tribe_get_ical_link() . '">' . __( '+ iCal Import', 'tribe-events-calendar' ) . ' ' . $modifier . '</a>';
		echo $ical;

		return $content;

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

		$event_display = ! empty( $_REQUEST['tribe_display'] ) ? $_REQUEST['tribe_display'] : 'upcoming';
		if ( $event_display == 'list' )
			$event_display = 'upcoming';

		if ( $post ) {
			$events_posts   = array();
			$events_posts[] = $post;
		} else {
			if ( class_exists( 'TribeEventsFilterView' ) ) {
				TribeEventsFilterView::instance()->createFilters( null, true );
			}
			TribeEventsQuery::init();
			$events_query = TribeEventsQuery::getEvents( array( 'posts_per_page'=> - 1, 'eventDisplay' => $event_display ), true );
			$events_posts = $events_query->posts;
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
			$item[] = 'LAST-MODIFIED:' . str_replace( array( '-', ' ', ':' ), array( '', 'T', '' ), $event_post->post_modified );
			$item[] = 'UID:' . $event_post->ID . '-' . strtotime( $startDate ) . '-' . strtotime( $endDate ) . '@' . $blogHome;
			$item[] = 'SUMMARY:' . $event_post->post_title;
			$item[] = 'DESCRIPTION:' . str_replace( ',', '\,', $description );
			$item[] = 'LOCATION:' . html_entity_decode( $tec->fullAddressString( $event_post->ID ), ENT_QUOTES );
			$item[] = 'URL:' . get_permalink( $event_post->ID );

			$item = apply_filters( 'tribe_ical_feed_item', $item, $event_post );

			$events .= "BEGIN:VEVENT\n" . implode( "\n", $item ) . "\nEND:VEVENT\n";
		}

		header( 'Content-type: text/calendar' );
		header( 'Content-Disposition: attachment; filename="iCal-TribeEvents.ics"' );
		$content = "BEGIN:VCALENDAR\n";
		$content .= "VERSION:2.0\n";
		$content .= 'PRODID:-//' . $blogName . ' - ECPv' . TribeEvents::VERSION . "//NONSGML v1.0//EN\n";
		$content .= "CALSCALE:GREGORIAN\n";
		$content .= "METHOD:PUBLISH\n";
		$content .= 'X-WR-CALNAME:' . apply_filters( 'tribe_ical_feed_calname', $blogName ) . "\n";
		$content .= 'X-ORIGINAL-URL:' . $blogHome . "\n";
		$content .= 'X-WR-CALDESC:Events for ' . $blogName . "\n";
		if ( $wp_timezone ) $content .= 'X-WR-TIMEZONE:' . $wp_timezone . "\n";
		$content = apply_filters( 'tribe_ical_properties', $content );
		$content .= $events;
		$content .= 'END:VCALENDAR';
		echo $content;

		exit;

	}
}