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
	 * The $post where the *.ics file is generated
	 *
	 * @since 4.9.4
	 *
	 * @var null
	 */
	protected $post = null;

	/**
	 * An array with all the events that are part of the *.ics file
	 *
	 * @since 4.9.4
	 *
	 * @var array
	 */
	protected $events = [];

	/**
	 * Set all the filters and actions necessary for the operation of the iCal generator.
	 */
	public function hook() {
		add_action( 'tribe_events_after_footer', [ $this, 'maybe_add_link' ], 10, 1 );
		add_action(
			'tribe_events_single_event_after_the_content',
			[ $this, 'single_event_links' ]
		);
		add_action( 'template_redirect', [ $this, 'do_ical_template' ] );
		add_filter( 'tribe_get_ical_link', [ $this, 'day_view_ical_link' ], 20, 1 );
		add_action( 'wp_head', [ $this, 'set_feed_link' ], 2, 0 );
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

		printf(
			'<link rel="alternate" type="text/calendar" title="%s" href="%s" />',
			esc_attr( $feed_title ),
			esc_url( tribe_get_ical_link() )
		);

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

		return add_query_arg( [ 'ical' => 1 ], $tec->getLink( $type ) );
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

			if ( ! $wp_query = tribe_get_global_query_object() ) {
				return;
			}

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
		$calendar_links = '<div class="tribe-events-cal-links">';
		$calendar_links .= '<a class="tribe-events-gcal tribe-events-button" href="' . Tribe__Events__Main::instance()->esc_gcal_url( tribe_get_gcal_link() ) . '" title="' . esc_attr__( 'Add to Google Calendar', 'the-events-calendar' ) . '">+ ' . esc_html__( 'Google Calendar', 'the-events-calendar' ) . '</a>';
		$calendar_links .= '<a class="tribe-events-ical tribe-events-button" href="' . esc_url( tribe_get_single_ical_link() ) . '" title="' . esc_attr__( 'Download .ics file', 'the-events-calendar' ) . '" >+ ' . esc_html__( 'iCal Export', 'the-events-calendar' ) . '</a>';
		$calendar_links .= '</div><!-- .tribe-events-cal-links -->';

		/**
		 * Allow for complete customization of the iCal and gCal single-event links.
		 *
		 * @param string $calendar_links The HTML of the iCal and gCal single-event link buttons.
		 */
		echo apply_filters( 'tribe_events_ical_single_event_links', $calendar_links );
	}


	/**
	 * Generates the markup for the "iCal Import" link for the views.
	 */
	public function maybe_add_link() {

		if ( ! $wp_query = tribe_get_global_query_object() ) {
			return;
		}

		/**
		 * A filter to control whether the "iCal Import" link shows up or not.
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
		 * Allow for customization of the iCal export link "Export Events" text.
		 *
		 * @param string $text The default link text, which is "Export Events".
		 */
		$text  = apply_filters( 'tribe_events_ical_export_text', esc_html__( 'Export Events', 'the-events-calendar' ) );
		$title = esc_html__( 'Use this to share calendar data with Google Calendar, Apple iCal and other compatible apps', 'the-events-calendar' );

		printf(
			'<a class="tribe-events-ical tribe-events-button" title="%1$s" href="%2$s">+ %3$s</a>',
			$title,
			esc_url( tribe_get_ical_link() ),
			$text
		);
	}

	/**
	 * Executes the iCal generator when the appropiate query_var or $_GET is setup
	 */
	public function do_ical_template() {
		// hijack to iCal template
		if ( get_query_var( 'ical' ) || isset( $_GET['ical'] ) ) {
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

			if ( isset( $_GET['event_ids'] ) ) {
				if ( empty( $_GET['event_ids'] ) ) {
					die();
				}
				$event_ids = explode( ',', $_GET['event_ids'] );
				$events = tribe_get_events( [ 'post__in' => $event_ids ] );
				$this->generate_ical_feed( $events );
			} elseif ( is_singular( Tribe__Events__Main::POSTTYPE ) ) {
				$this->generate_ical_feed( $wp_query->post );
			} else {
				$this->generate_ical_feed();
			}
			die();
		}
	}

	/**
	 * Generates the iCal file
	 *
	 * @param int|null $post If you want the ical file for a single event
	 * @param boolean  $echo Whether the content should be echoed or returned
	 *
	 * @return string
	 */
	public function generate_ical_feed( $post = null, $echo = true ) {
		$this->post = $post;
		$this->events = $this->get_event_posts();
		$content = $this->get_content();

		if ( $echo ) {
			$this->set_headers();
			tribe_exit( $content );
		}

		return $content;
	}

	/**
	 * Get an array with all the Events to be used to process the *.ics file
	 *
	 * @since 4.9.4
	 *
	 * @return array|null
	 */
	protected function get_event_posts() {
		if ( $this->post ) {
			return is_array( $this->post ) ? $this->post : [ $this->post ];
		}

		if ( tribe_is_month() ) {
			return $this->get_month_view_events();
		}

		if ( tribe_is_organizer() ) {
			return $this->get_events_list(
				[
					'organizer' => get_the_ID(),
					'eventDisplay' => 'list',
				]
			);
		}

		if ( tribe_is_venue() ) {
			return $this->get_events_list(
				[
					'venue' => get_the_ID(),
					'eventDisplay' => tribe_get_request_var( 'tribe_event_display', 'list' ),
				]
			);
		}

		if ( ! $wp_query = tribe_get_global_query_object() ) {
			return [];
		}

		$args = $wp_query->query_vars;

		if ( 'list' === $args['eventDisplay'] ) {
			// Whe producing a List view iCal feed the `eventDate` is misleading.
			unset( $args['eventDate'] );
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
	 * @return array events in the month
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
			'eventDisplay' => 'custom',
			'start_date' => Tribe__Events__Template__Month::calculate_first_cell_date( $month ),
			'end_date' => Tribe__Events__Template__Month::calculate_final_cell_date( $month ),
			'posts_per_page' => -1,
			'hide_upcoming' => true,
		];

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
	 * Get the file name of the *.ics file
	 *
	 * @since 4.9.4
	 *
	 * @return mixed The calendar name
	 */
	protected function get_file_name() {
		$event_ids = wp_list_pluck( $this->events, 'ID' );
		$site = sanitize_title( get_bloginfo( 'name' ) );
		$hash = substr( md5( implode( $event_ids ) ), 0, 11 );
		$filename = sprintf( '%s-%s.ics', $site, $hash );

		/**
		 * Modifies the filename provided in the Content-Disposition header for iCal feeds.
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
	 * @return string
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
	 * Get the start of the .ics File
	 *
	 * @since 4.9.4
	 *
	 * @return mixed
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
		 * @param string $blog_name The value to use for "X-WR-CALNAME"; defaults to value of get_bloginfo( 'name' ).
		 */
		$x_wr_calname = apply_filters( 'tribe_ical_feed_calname', $blog_name );

		if ( ! empty( $x_wr_calname ) ) {
			$content .= 'X-WR-CALNAME:' . $x_wr_calname . "\r\n";
		}

		$content .= 'X-ORIGINAL-URL:' . $blog_home . "\r\n";
		$content .= "X-WR-CALDESC:" . sprintf( esc_html_x( 'Events for %s', 'iCal feed description', 'the-events-calendar' ), $blog_name ) . "\r\n";

		/**
		 * Allows for customization of the various properties at the top of the generated iCal file.
		 *
		 * @param string $content Existing properties atop the file; starts at "BEGIN:VCALENDAR", ends at "X-WR-CALDESC".
		 */
		return apply_filters( 'tribe_ical_properties', $content );
	}

	/**
	 * Add the VTIMEZONE group to the file
	 *
	 * @since 4.9.4
	 *
	 * @param array $events
	 *
	 * @return string
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
				'start' => array_column( $row['events'], 'start_year' ),
				'end' => array_column( $row['events'], 'end_year' ),
			];

			sort( $ordered['start'] );
			rsort( $ordered['end'] );

			$ordered['start'] = array_values( $ordered['start'] );
			$ordered['end'] = array_values( $ordered['end'] );

			$start = reset( $ordered['start'] );
			$end = reset( $ordered['end'] );

			if ( empty( $start ) || empty( $end ) ) {
				continue;
			}

			$transitions = $timezone->getTransitions( $start, $end );
			if ( count( $transitions ) === 1 ) {
				$transitions[] = array_values( $transitions )[ 0 ];
			}

			$id = $timezone->getName();
			$item[] = 'TZID:"' .  $id . '"';

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
					// TODO: report this exception
				}
				$item[] = 'END:' . $type;
				$last_transition = $transition;
			}
		}

		/**
		 * Allow for customization of an individual "VTIMEZONE" item to be rendered inside an iCal export file.
		 *
		 * @since 4.9.4
		 *
		 * @param array $item The various iCal file format components of this specific event item.
		 * @param array $timezones The various iCal timzone components of this specific event item.
		 */
		$item = apply_filters( 'tribe_ical_feed_vtimezone', $item, $timezones );

		return "BEGIN:VTIMEZONE\r\n" . implode( "\r\n", $item ) . "\r\nEND:VTIMEZONE\r\n";
	}

	/**
	 * Create an array of arrays with unique Timezones for all the events, every timezone has
	 * the following fields:
	 *
	 * - timezone. The Timezone Object
	 * - events. List with all the events
	 *
	 * @since 4.9.4
	 *
	 * @param $events array An array with all the events to parse the timezones.
	 *
	 * @return array
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
	 * Format the offset into Hours and minutes from seconds.
	 *
	 * @since 4.9.4
	 *
	 * @param $offset
	 *
	 * @return string
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
	 * Get the Body With all the events of the .ics file
	 *
	 * @since 4.9.4
	 *
	 * @param array $posts
	 *
	 * @return string
	 */
	protected function get_body( $posts = [] ) {
		$tec         = Tribe__Events__Main::instance();
		$events      = '';

		foreach ( $posts as $event_post ) {
			// add fields to iCal output
			$item = [];

			$full_format = 'Ymd\THis';
			$utc_format  = 'Ymd\THis\Z';
			$all_day     = ( 'yes' === get_post_meta( $event_post->ID, '_EventAllDay', true ) );
			$time = (object) [
				'start' => tribe_get_start_date( $event_post->ID, false, 'U' ),
				'end' => tribe_get_end_date( $event_post->ID, false, 'U' ),
				'modified' => Tribe__Date_Utils::wp_strtotime( $event_post->post_modified ),
				'created' => Tribe__Date_Utils::wp_strtotime( $event_post->post_date ),
			];

			$type   = 'DATE-TIME';
			$format = $full_format;

			if ( $all_day ) {
				$type   = 'DATE';
				$format = 'Ymd';
			}

			$tzoned = (object) [
				'start' => date( $format, $time->start ),
				'end' => date( $format, $time->end ),
				'modified' => date( $utc_format, $time->modified ),
				'created' => date( $utc_format, $time->created ),
			];

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
				$timezone_name = $this->get_timezone( $event_post );
				$timezone = Tribe__Events__Timezones::build_timezone_object( $timezone_name );

				$item[] = 'DTSTART;TZID="' . $timezone->getName() . '":' . $dtstart;
				$item[] = 'DTEND;TZID="' . $timezone->getName() . '":' . $dtend;
			}

			$item[] = 'DTSTAMP:' . date( $full_format, time() );
			$item[] = 'CREATED:' . $tzoned->created;
			$item[] = 'LAST-MODIFIED:' . $tzoned->modified;
			$item[] = 'UID:' . $event_post->ID . '-' . $time->start . '-' . $time->end . '@' . parse_url( home_url( '/' ), PHP_URL_HOST );
			$item[] = 'SUMMARY:' . $this->replace( strip_tags( $event_post->post_title ) );

			$content = apply_filters( 'the_content', tribe( 'editor.utils' )->exclude_tribe_blocks( $event_post->post_content ) );

			$item[] = 'DESCRIPTION:' . $this->replace( strip_tags( str_replace( '</p>', '</p> ', $content ) ) );

			$item[] = 'URL:' . get_permalink( $event_post->ID );

			// add location if available
			$location = $tec->fullAddressString( $event_post->ID );
			if ( ! empty( $location ) ) {
				$str_location = $this->replace( $location, [ ',', "\n" ], [ '\,', '\n' ] );

				$item[] = 'LOCATION:' .  $str_location;
			}

			// add categories if available
			$event_cats = (array) wp_get_object_terms(
				$event_post->ID,
				Tribe__Events__Main::TAXONOMY,
				[ 'fields' => 'names' ]
			);

			if ( ! empty( $event_cats ) ) {
				$item[] = 'CATEGORIES:' . $this->html_decode( join( ',', $event_cats ) );
			}

			// add featured image if available
			if ( has_post_thumbnail( $event_post->ID ) ) {
				$thumbnail_id        = get_post_thumbnail_id( $event_post->ID );
				$thumbnail_url       = wp_get_attachment_url( $thumbnail_id );
				$thumbnail_mime_type = get_post_mime_type( $thumbnail_id );

				/**
				 * Allow for customization of an individual iCal-exported event's thumbnail.
				 *
				 * @param string $string This thumbnail's iCal-formatted "ATTACH;" string with the thumbnail mime type and URL.
				 * @param int $post_id The ID of the event this thumbnail belongs to.
				 */
				$item[] = apply_filters( 'tribe_ical_feed_item_thumbnail', sprintf( 'ATTACH;FMTTYPE=%s:%s', $thumbnail_mime_type, $thumbnail_url ), $event_post->ID );
			}

			// add organizer if available
			$organizer_email = tribe_get_organizer_email( $event_post->ID, false );
			if ( $organizer_email ) {
				$organizer_id = tribe_get_organizer_id( $event_post->ID );
				$organizer    = get_post( $organizer_id );

				if ( $organizer_id ) {
					$item[] = sprintf( 'ORGANIZER;CN="%s":MAILTO:%s', rawurlencode( $organizer->post_title ), $organizer_email );
				} else {
					$item[] = sprintf( 'ORGANIZER:MAILTO:%s', $organizer_email );
				}
			}

			/**
			 * Allow for customization of an individual "VEVENT" item to be rendered inside an iCal export file.
			 *
			 * @param array $item The various iCal file format components of this specific event item.
			 * @param object $event_post The WP_Post of this event.
			 */
			$item = apply_filters( 'tribe_ical_feed_item', $item, $event_post );

			$events .= "BEGIN:VEVENT\r\n" . implode( "\r\n", $item ) . "\r\nEND:VEVENT\r\n";
		}

		return $events;
	}

	/**
	 * Replace the text and encode the text before doing the replacement.
	 *
	 * @since 4.9.4
	 *
	 * @param string $text The text to be replaced.
	 * @param array  $search What elements to search to replace.
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
	 * Apply html_entity_decode on a string using ENT_QUOTES style
	 *
	 * @since 4.9.4
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	protected function html_decode( $text = '' ) {
		return html_entity_decode( $text, ENT_QUOTES );
	}

	/**
	 * Return the timezone name associated with the event
	 *
	 * @since 4.9.4
	 *
	 * @param $event \WP_Post The $event post
	 *
	 * @return string
	 */
	protected function get_timezone( $event ) {
		return Tribe__Events__Timezones::EVENT_TIMEZONE === Tribe__Events__Timezones::mode()
			? Tribe__Events__Timezones::get_event_timezone_string( $event->ID )
			: Tribe__Events__Timezones::wp_timezone_string();
	}

	/**
	 * Return the end of the .ics file
	 *
	 * @since 4.9.4
	 *
	 * @return string
	 */
	protected function get_end() {
		return 'END:VCALENDAR';
	}

	/**
	 * Get a list of events, the function make sure it uses the default values used on the main events page
	 * so if is called from a different location like a page or post (shortcode) it will retain the original values
	 * to generate the events feed.
	 *
	 * @since 4.6.11
	 *
	 * @param array $args The WP_Query arguments.
	 * @param mixed $query A WP_Query object or null if none.
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
	 * Get the number of posts per page to be used on the feed of the iCal, make sure it passes the value via the filter
	 * tribe_ical_feed_posts_per_page and validates the number is greater than 0.
	 *
	 * @since 4.6.11
	 *
	 * @return int
	 */
	protected function feed_posts_per_page() {
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

		return is_numeric( $count ) && is_int( $count ) && $count > 0
			? $count
			: $this->feed_default_export_count;
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
