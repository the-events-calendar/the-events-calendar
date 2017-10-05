<?php

/**
 *  Class that implements the export to iCal functionality
 *  both for list and single events
 */
class Tribe__Events__iCal {

	/**
	 * @var int The number of events that will be exported when generating the iCal feed.
	 */
	protected $feed_default_export_count = 30;

	/**
	 * Set all the filters and actions necessary for the operation of the iCal generator.
	 */
	public function hook() {
		add_action( 'tribe_events_after_footer', array( $this, 'maybe_add_link' ), 10, 1 );
		add_action( 'tribe_events_single_event_after_the_content', array( $this, 'single_event_links' ) );
		add_action( 'template_redirect', array( $this, 'do_ical_template' ) );
		add_filter( 'tribe_get_ical_link', array( $this, 'day_view_ical_link' ), 20, 1 );
		add_action( 'wp_head', array( $this, 'set_feed_link' ), 2, 0 );
	}

	/**
	 * outputs a <link> element for the ical feed
	 */
	public function set_feed_link() {
		if ( ! current_theme_supports( 'automatic-feed-links' ) ) {
			return;
		}
		$separator  = _x( '&raquo;', 'feed link', 'the-events-calendar' );
		$feed_title = sprintf( esc_html__( '%1$s %2$s iCal Feed', 'the-events-calendar' ), get_bloginfo( 'name' ), $separator );

		printf( '<link rel="alternate" type="text/calendar" title="%s" href="%s" />', esc_attr( $feed_title ), esc_url( tribe_get_ical_link() ) );
		echo "\n";
	}

	/**
	 * Returns the url for the iCal generator for lists of posts.
	 *
	 * @param string $type The type of iCal link to return, defaults to 'home'.
	 *
	 * @return string
	 */
	public function get_ical_link( $type = 'home' ) {
		$tec = Tribe__Events__Main::instance();

		return add_query_arg( array( 'ical' => 1 ), $tec->getLink( $type ) );
	}

	/**
	 * Make sure ical link has the date in the URL instead of "today" on day view
	 *
	 * @param $link
	 *
	 * @return string
	 */
	public function day_view_ical_link( $link ) {
		if ( tribe_is_day() ) {
			global $wp_query;
			$day  = $wp_query->get( 'start_date' );
			$link = trailingslashit( esc_url( trailingslashit( tribe_get_day_link( $day ) ) . '?ical=1' ) );
		}

		return $link;
	}

	/**
	 * Generates the markup for iCal and gCal single event links
	 **/
	public function single_event_links() {

		// don't show on password protected posts
		if ( is_single() && post_password_required() ) {
			return;
		}
		echo '<div class="tribe-events-cal-links">';
		echo '<a class="tribe-events-gcal tribe-events-button" href="' . Tribe__Events__Main::instance()->esc_gcal_url( tribe_get_gcal_link() ) . '" title="' . esc_attr__( 'Add to Google Calendar', 'the-events-calendar' ) . '">+ ' . esc_html__( 'Google Calendar', 'the-events-calendar' ) . '</a>';
		echo '<a class="tribe-events-ical tribe-events-button" href="' . esc_url( tribe_get_single_ical_link() ) . '" title="' . esc_attr__( 'Download .ics file', 'the-events-calendar' ) . '" >+ ' . esc_html__( 'iCal Export', 'the-events-calendar' ) . '</a>';
		echo '</div><!-- .tribe-events-cal-links -->';
	}

	/**
	 * Generates the markup for the "iCal Import" link for the views.
	 */
	public function maybe_add_link() {
		global $wp_query;
		$show_ical = apply_filters( 'tribe_events_list_show_ical_link', true );

		if ( ! $show_ical ) {
			return;
		}
		if ( tribe_is_month() && ! tribe_events_month_has_events() ) {
			return;
		}
		if ( is_single() || empty( $wp_query->posts ) ) {
			return;
		}

		$tec = Tribe__Events__Main::instance();

		$view = $tec->displaying;
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX && isset( $wp_query->query_vars['eventDisplay'] ) ) {
			$view = $wp_query->query_vars['eventDisplay'];
		}

		$text = apply_filters( 'tribe_events_ical_export_text', esc_html__( 'Export Events', 'the-events-calendar' ) );
		$title = esc_html__( 'Use this to share calendar data with Google Calendar, Apple iCal and other compatible apps', 'the-events-calendar' );
		$ical  = '<a class="tribe-events-ical tribe-events-button" title="' . $title . '" href="' . esc_url( tribe_get_ical_link() ) . '">+ ' . $text . '</a>';

		echo $ical;
	}

	/**
	 * Executes the iCal generator when the appropiate query_var or $_GET is setup
	 */
	public function do_ical_template() {
		// hijack to iCal template
		if ( get_query_var( 'ical' ) || isset( $_GET['ical'] ) ) {
			global $wp_query;
			if ( isset( $_GET['event_ids'] ) ) {
				if ( empty( $_GET['event_ids'] ) ) {
					die();
				}
				$event_ids = explode( ',', $_GET['event_ids'] );
				$events    = tribe_get_events( array( 'post__in' => $event_ids ) );
				$this->generate_ical_feed( $events );
			} elseif ( is_single() ) {
				$this->generate_ical_feed( $wp_query->post );
			} else {
				$this->generate_ical_feed();
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
	private function get_month_view_events() {
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

		// Verify the Intial Category
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
		 * @var array  $args
		 * @var string $month
		 */
		$args = (array) apply_filters( 'tribe_ical_feed_month_view_query_args', $args, $month );

		return tribe_get_events( $args );
	}

	/**
	 * Generates the iCal file
	 *
	 * @param int|null $post If you want the ical file for a single event
	 * @param bool $echo Whether the content should be echoed or returned
	 */
	public function generate_ical_feed( $post = null, $echo = true ) {
		$tec         = Tribe__Events__Main::instance();
		$events      = '';
		$blogHome    = get_bloginfo( 'url' );
		$blogName    = get_bloginfo( 'name' );

		if ( $post ) {
			$events_posts = is_array( $post ) ? $post : array( $post );
		} elseif ( tribe_is_month() ) {
			$events_posts = self::get_month_view_events();
		} else {
			/**
			 * Filters the number of upcoming events the iCal feed should export.
			 *
			 * This filter allows developer to override the pagination setting and the default value
			 * to export a number of events that's inferior or superior to the one shown on the page.
			 * The minimum value is 1.
			 *
			 * @param int $count The number of upcoming events that should be exported in the
			 *                   feed, defaults to 30.
			 */
			$count = apply_filters( 'tribe_ical_feed_posts_per_page', $this->feed_default_export_count );

			$count = is_numeric( $count ) && is_int( $count ) && $count > 0 ? $count : $this->feed_default_export_count;

			/** @var WP_Query $wp_query */
			global $wp_query;

			$query_posts_per_page = $wp_query->get( 'posts_per_page' );
			if ( $count > $query_posts_per_page ) {
				$query        = new WP_Query( array_merge( $wp_query->query, array( 'posts_per_page' => $count ) ) );
				$events_posts = $query->get_posts();
			} else {
				$events_posts = array_slice( $wp_query->posts, 0, $count );
			}
		}

		$event_ids = wp_list_pluck( $events_posts, 'ID' );

		foreach ( $events_posts as $event_post ) {
			// add fields to iCal output
			$item = array();

			$full_format = 'Ymd\THis';
			$utc_format = 'Ymd\THis\Z';
			$all_day = ( 'yes' === get_post_meta( $event_post->ID, '_EventAllDay', true ) );
			$time = (object) array(
				'start' => tribe_get_start_date( $event_post->ID, false, 'U' ),
				'end' => tribe_get_end_date( $event_post->ID, false, 'U' ),
				'modified' => Tribe__Date_Utils::wp_strtotime( $event_post->post_modified ),
				'created' => Tribe__Date_Utils::wp_strtotime( $event_post->post_date ),
			);

			if ( $all_day ) {
				$type = 'DATE';
				$format = 'Ymd';
			} else {
				$type = 'DATE-TIME';
				$format = $full_format;
			}

			$tzoned = (object) array(
				'start'    => date( $format, $time->start ),
				'end'      => date( $format, $time->end ),
				'modified' => date( $utc_format, $time->modified ),
				'created'  => date( $utc_format, $time->created ),
			);

			$dtstart = $tzoned->start;
			$dtend   = $tzoned->end;

			if ( 'DATE' === $type ) {
				// For all day events dtend should always be +1 day.
				if ( $all_day ) {
					$dtend = date( $format, strtotime( '+1 day', strtotime( $dtend ) ) );
				}

				$item[] = 'DTSTART;VALUE=' . $type . ':' . $dtstart;
				$item[] = 'DTEND;VALUE=' . $type . ':' . $dtend;
			} else {
				// Are we using the sitewide timezone or the local event timezone?
				$tz = Tribe__Events__Timezones::EVENT_TIMEZONE === Tribe__Events__Timezones::mode()
					? Tribe__Events__Timezones::get_event_timezone_string( $event_post->ID )
					: Tribe__Events__Timezones::wp_timezone_string();

				$item[] = 'DTSTART;TZID=' . $tz . ':' . $dtstart;
				$item[] = 'DTEND;TZID=' . $tz . ':' . $dtend;
			}

			$item[] = 'DTSTAMP:' . date( $full_format, time() );
			$item[] = 'CREATED:' . $tzoned->created;
			$item[] = 'LAST-MODIFIED:' . $tzoned->modified;
			$item[] = 'UID:' . $event_post->ID . '-' . $time->start . '-' . $time->end . '@' . parse_url( home_url( '/' ), PHP_URL_HOST );
			$item[] = 'SUMMARY:' . str_replace( array( ',', "\n", "\r" ), array( '\,', '\n', '' ), html_entity_decode( strip_tags( $event_post->post_title ), ENT_QUOTES ) );
			$item[] = 'DESCRIPTION:' . str_replace( array( ',', "\n", "\r" ), array( '\,', '\n', '' ), html_entity_decode( strip_tags( str_replace( '</p>', '</p> ', apply_filters( 'the_content', $event_post->post_content ) ) ), ENT_QUOTES ) );
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
			$organizer_email = tribe_get_organizer_email( $event_post->ID, false );
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

		$site = sanitize_title( get_bloginfo( 'name' ) );
		$hash = substr( md5( implode( $event_ids ) ), 0, 11 );

		/**
		 * Modifies the filename provided in the Content-Disposition header for iCal feeds.
		 *
		 * @var string       $ical_feed_filename
		 * @var WP_Post|null $post
		 */
		$filename = apply_filters( 'tribe_events_ical_feed_filename', $site . '-' . $hash . '.ics', $post );

		header( 'Content-type: text/calendar; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
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

		if ( $echo ) {
			tribe_exit( $content );
		}

		return $content;
	}

	/**
	 * Gets the number of events that should be exported when generating the iCal feed.
	 *
	 * @return int
	 */
	public function get_feed_default_export_count() {
		return $this->feed_default_export_count;
	}

	/**
	 * Sets the number of events that should be exported when generating the iCal feed.
	 *
	 * @param int $count
	 */
	public function set_feed_default_export_count( $count ) {
		$this->feed_default_export_count = $count;
	}

}
