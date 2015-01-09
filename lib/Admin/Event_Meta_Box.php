<?php
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
	 * Container for variables used within the meta box template itself.
	 *
	 * @var array
	 */
	protected $vars = array();


	/**
	 * Sets up and renders the event meta box for the specified existing event
	 * or for a new event (if $event === null).
	 *
	 * @param null $event
	 */
	public function __construct( $event = null ) {
		$this->load_event( $event );
		$this->tribe = TribeEvents::instance();
		$this->setup();
		$this->do_meta_box();
	}

	/**
	 * Work with the specifed event object or else use a placeholder if we are in
	 * the middle of creating a new event.
	 *
	 * @param null $event
	 */
	protected function load_event( $event = null ) {
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

		$_EventAllDay    = isset( $_EventAllDay ) ? $_EventAllDay : false;
		$_EventStartDate = ( isset( $_EventStartDate ) ) ? $_EventStartDate : null;

		if ( isset( $_EventEndDate ) ) {
			if ( $_EventAllDay && TribeDateUtils::timeOnly( $_EventEndDate ) != '23:59:59' && TribeDateUtils::timeOnly( tribe_event_end_of_day() ) != '23:59:59' ) {

				// If it's an all day event and the EOD cutoff is later than midnight
				// set the end date to be the previous day so it displays correctly in the datepicker
				// so the datepickers will match. we'll set the correct end time upon saving
				// @todo: remove this once we're allowed to have all day events without a start/end time

				$_EventEndDate = date_create( $_EventEndDate );
				$_EventEndDate->modify( '-1 day' );
				$_EventEndDate = $_EventEndDate->format( TribeDateUtils::DBDATETIMEFORMAT );

			}
		} else {
			$_EventEndDate = null;
		}
		$isEventAllDay        = ( $_EventAllDay == 'yes' || ! TribeDateUtils::dateOnly( $_EventStartDate ) ) ? 'checked="checked"' : ''; // default is all day for new posts
		$startMinuteOptions   = TribeEventsViewHelpers::getMinuteOptions( $_EventStartDate, true );
		$endMinuteOptions     = TribeEventsViewHelpers::getMinuteOptions( $_EventEndDate );
		$startHourOptions     = TribeEventsViewHelpers::getHourOptions( $_EventAllDay == 'yes' ? null : $_EventStartDate, true );
		$endHourOptions       = TribeEventsViewHelpers::getHourOptions( $_EventAllDay == 'yes' ? null : $_EventEndDate );
		$startMeridianOptions = TribeEventsViewHelpers::getMeridianOptions( $_EventStartDate, true );
		$endMeridianOptions   = TribeEventsViewHelpers::getMeridianOptions( $_EventEndDate );

		if ( $_EventStartDate ) {
			$start = TribeDateUtils::dateOnly( $_EventStartDate );
		}

		$EventStartDate = ( isset( $start ) && $start ) ? $start : date( 'Y-m-d' );

		if ( ! empty( $_REQUEST['eventDate'] ) ) {
			$EventStartDate = esc_attr( $_REQUEST['eventDate'] );
		}

		if ( $_EventEndDate ) {
			$end = TribeDateUtils::dateOnly( $_EventEndDate );
		}

		$EventEndDate = ( isset( $end ) && $end ) ? $end : date( 'Y-m-d' );
		$recStart     = isset( $_REQUEST['event_start'] ) ? esc_attr( $_REQUEST['event_start'] ) : null;
		$recPost      = isset( $_REQUEST['post'] ) ? absint( $_REQUEST['post'] ) : null;


		if ( ! empty( $_REQUEST['eventDate'] ) ) {
			$duration     = get_post_meta( $this->event->ID, '_EventDuration', true );
			$start_time   = isset( $_EventStartDate ) ? TribeDateUtils::timeOnly( $_EventStartDate ) : TribeDateUtils::timeOnly( tribe_get_start_date( $post->ID ) );
			$EventEndDate = TribeDateUtils::dateOnly( strtotime( $_REQUEST['eventDate'] . ' ' . $start_time ) + $duration, true );
		}
	}

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

	protected function do_meta_box() {
		$events_meta_box_template = $this->tribe->pluginPath . 'admin-views/events-meta-box.php';
		$events_meta_box_template = apply_filters( 'tribe_events_meta_box_template', $events_meta_box_template );

		extract( $this->vars );
		$event = $this->event;
		$tribe = $this->tribe;

		include( $events_meta_box_template );
	}
}