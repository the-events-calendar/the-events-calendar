<?php
/**
 * Sets up and renders the main event meta box used in the event editor.
 */
class Tribe__Events__Admin__Event_Meta_Box {
	/**
	 * @var WP_Post
	 */
	protected $event;

	/**
	 * @var TribeEvents
	 */
	protected $tribe;

	/**
	 * Variables (with some defaults) for use within the meta box template itself.
	 *
	 * @var array
	 */
	protected $vars = array(
		'_EventAllDay' => false,
		'_EventEndDate' => null,
		'_EventStartDate' => null,
	);


	/**
	 * Sets up and renders the event meta box for the specified existing event
	 * or for a new event (if $event === null).
	 *
	 * @param null $event
	 */
	public function __construct( $event = null ) {
		$this->tribe = TribeEvents::instance();
		$this->work_with( $event );
		$this->setup();
		$this->do_meta_box();
	}

	/**
	 * Work with the specifed event object or else use a placeholder if we are in
	 * the middle of creating a new event.
	 *
	 * @param null $event
	 */
	protected function work_with( $event = null ) {
		global $post;

		if ( $event === null ) {
			$this->event = $post;
		} elseif ( is_a( $event, 'WP_Post' ) ) {
			$this->event = $event;
		} else {
			$this->event = new WP_Post( (object) array( 'ID' => 0 ) );
		}
	}

	protected function setup() {
		$this->get_existing_event_vars();
		$this->get_existing_organizer_vars();
		$this->get_existing_venue_vars();

		$this->eod_correction();
		$this->set_all_day();
		$this->set_end_date_time();
		$this->set_start_date_time();
	}

	/**
	 * Checks for existing event post meta data and populates the list of vars accordingly.
	 */
	protected function get_existing_event_vars() {
		foreach ( $this->tribe->metaTags as $tag ) {
			if ( $this->event->ID ) {
				$this->vars[$tag] = get_post_meta( $this->event->ID, $tag, true );
			} else {
				$cleaned_tag = str_replace( '_Event', '', $tag );
				if ( isset( $_POST['Event' . $cleaned_tag] ) ) {
					$this->vars[$tag] = stripslashes_deep( $_POST['Event' . $cleaned_tag] );
				} else {
					$this->vars[$tag] = call_user_func( array( $this->tribe->defaults(), $cleaned_tag ) );
				}
			}
		}
	}

	/**
	 * Checks for existing organizer post meta data and populates the list of vars accordingly.
	 */
	protected function get_existing_organizer_vars() {
		if ( isset( $this->vars['_EventOrganizerID'] ) && $this->vars['_EventOrganizerID'] ) {
			foreach ( $this->tribe->organizerTags as $tag ) {
				$this->vars[$tag] = get_post_meta( $this->vars['_EventOrganizerID'], $tag, true );
			}
		} else {
			foreach ( $this->tribe->organizerTags as $tag ) {
				$cleaned_tag = str_replace( '_Organizer', '', $tag );
				if ( isset( $_POST['organizer'][$cleaned_tag] ) ) {
					$this->vars[$tag] = stripslashes_deep( $_POST['organizer'][$cleaned_tag] );
				}
			}
		}
	}

	/**
	 * Checks for existing venue post meta data and populates the list of vars accordingly.
	 */
	protected function get_existing_venue_vars() {
		if ( isset( $this->vars['_EventVenueID'] ) && $this->vars['_EventVenueID'] ) {
			foreach ( $this->tribe->venueTags as $tag ) {
				$this->vars[$tag] = get_post_meta( $this->vars['_EventVenueID'], $tag, true );
			}
		} else {
			$_VenueVenue = $this->tribe->defaults()->venue_id();
			if ( !$_VenueVenue ) {
				$_VenueVenue = NULL;
			}
		}
	}

	/**
	 * If it's an all day event and the EOD cutoff is later than midnight
	 * set the end date to be the previous day so it displays correctly in the datepicker
	 * so the datepickers will match. we'll set the correct end time upon saving
	 *
	 * @todo remove this once we're allowed to have all day events without a start/end time
	 */
	protected function eod_correction() {
		$all_day  = $this->vars['_EventAllDay'];
		$end_date = $this->vars['_EventEndDate'];

		$ends_at_midnight = '23:59:59' === TribeDateUtils::timeOnly( $end_date );
		$midnight_cutoff  = '23:59:59' === TribeDateUtils::timeOnly( tribe_event_end_of_day() );

		if ( ! $all_day || $ends_at_midnight || $midnight_cutoff ) {
			return;
		}

		$end_date = date_create( $this->vars['_EventEndDate'] );
		$end_date->modify( '-1 day' );
		$this->vars['_EventEndDate'] = $end_date->format( TribeDateUtils::DBDATETIMEFORMAT );
	}

	/**
	 * Assess if this is an all day event.
	 */
	protected function set_all_day() {
		$this->vars['isEventAllDay'] = ( $this->vars['_EventAllDay'] == 'yes' || ! TribeDateUtils::dateOnly( $this->vars['_EventStartDate'] ) ) ? 'checked="checked"' : '';
	}


	protected function set_end_date_time() {
		$this->vars['endMinuteOptions']   = TribeEventsViewHelpers::getMinuteOptions( $this->vars['_EventEndDate'] );
		$this->vars['endHourOptions']     = TribeEventsViewHelpers::getHourOptions( $this->vars['_EventAllDay'] == 'yes' ? null : $this->vars['_EventEndDate'] );
		$this->vars['endMeridianOptions'] = TribeEventsViewHelpers::getMeridianOptions( $this->vars['_EventEndDate'] );

		if ( ! empty( $_REQUEST['eventDate'] ) ) {
			$this->vars['EventStartDate'] = esc_attr( $_REQUEST['eventDate'] );
		}

		if ( $this->vars['_EventEndDate'] ) {
			$end = TribeDateUtils::dateOnly( $this->vars['_EventEndDate'] );
		}

		$this->vars['EventEndDate'] = ( isset( $end ) && $end ) ? $end : date( 'Y-m-d' );

		if ( ! empty( $_REQUEST['eventDate'] ) ) {
			$duration     = get_post_meta( $this->event->ID, '_EventDuration', true );
			$start_time   = isset( $this->vars['_EventStartDate'] ) ? TribeDateUtils::timeOnly( $this->vars['_EventStartDate'] ) : TribeDateUtils::timeOnly( tribe_get_start_date( $this->event->ID ) );
			$this->vars['EventEndDate'] = TribeDateUtils::dateOnly( strtotime( $_REQUEST['eventDate'] . ' ' . $start_time ) + $duration, true );
		}
	}

	protected function set_start_date_time() {
		$this->vars['startMinuteOptions']   = TribeEventsViewHelpers::getMinuteOptions( $this->vars['_EventStartDate'], true );
		$this->vars['startHourOptions']     = TribeEventsViewHelpers::getHourOptions( $this->vars['_EventAllDay'] == 'yes' ? null : $this->vars['_EventStartDate'], true );
		$this->vars['startMeridianOptions'] = TribeEventsViewHelpers::getMeridianOptions( $this->vars['_EventStartDate'], true );

		if ( $this->vars['_EventStartDate'] ) {
			$start = TribeDateUtils::dateOnly( $this->vars['_EventStartDate'] );
		}

		$this->vars['EventStartDate'] = ( isset( $start ) && $start ) ? $start : date( 'Y-m-d' );
	}

	/**
	 * Pull the expected variables into scope and load the meta box template.
	 */
	protected function do_meta_box() {
		$events_meta_box_template = $this->tribe->pluginPath . 'admin-views/events-meta-box.php';
		$events_meta_box_template = apply_filters( 'tribe_events_meta_box_template', $events_meta_box_template );

		extract( $this->vars );
		$event = $this->event;
		$tribe = $this->tribe;

		include( $events_meta_box_template );
	}
}