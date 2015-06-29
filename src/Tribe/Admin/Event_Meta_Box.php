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
	 * @var Tribe__Events__Main
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
		'_EventOrganizerID' => null,
		'_EventVenueID' => null
	);


	/**
	 * Sets up and renders the event meta box for the specified existing event
	 * or for a new event (if $event === null).
	 *
	 * @param null $event
	 */
	public function __construct( $event = null ) {
		$this->tribe = Tribe__Events__Main::instance();
		$this->get_event( $event );
		$this->setup_data();
		$this->do_meta_box();
	}

	/**
	 * Work with the specifed event object or else use a placeholder if we are in
	 * the middle of creating a new event.
	 *
	 * @param null $event
	 */
	protected function get_event( $event = null ) {
		global $post;

		if ( $event === null ) {
			$this->event = $post;
		} elseif ( $event instanceof WP_Post ) {
			$this->event = $event;
		} else {
			$this->event = new WP_Post( (object) array( 'ID' => 0 ) );
		}
	}

	protected function setup_data() {
		$this->get_existing_event_vars();
		$this->get_existing_organizer_vars();
		$this->get_existing_venue_vars();

		$this->eod_correction();
		$this->set_all_day();
		$this->set_start_date_time();
		$this->set_end_date_time();
	}

	/**
	 * Checks for existing event post meta data and populates the list of vars accordingly.
	 */
	protected function get_existing_event_vars() {
		if ( ! $this->event->ID ) {
			return;
		}
		foreach ( $this->tribe->metaTags as $tag ) {
			$this->vars[$tag] = get_post_meta( $this->event->ID, $tag, true );
		}
	}

	/**
	 * Checks for existing organizer post meta data and populates the list of vars accordingly.
	 */
	protected function get_existing_organizer_vars() {
		if ( ! $this->vars['_EventOrganizerID'] ) {
			return;
		}
		foreach ( $this->tribe->organizerTags as $tag ) {
			$this->vars[$tag] = get_post_meta( $this->vars['_EventOrganizerID'], $tag, true );
		}
	}

	/**
	 * Checks for existing venue post meta data and populates the list of vars accordingly.
	 */
	protected function get_existing_venue_vars() {
		if ( ! $this->vars['_EventVenueID'] ) {
			return;
		}
		foreach ( $this->tribe->venueTags as $tag ) {
			$this->vars[$tag] = get_post_meta( $this->vars['_EventVenueID'], $tag, true );
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

		$ends_at_midnight = '23:59:59' === Tribe__Events__Date_Utils::time_only( $end_date );
		$midnight_cutoff  = '23:59:59' === Tribe__Events__Date_Utils::time_only( tribe_event_end_of_day() );

		if ( ! $all_day || $ends_at_midnight || $midnight_cutoff ) {
			return;
		}

		$end_date = date_create( $this->vars['_EventEndDate'] );
		$end_date->modify( '-1 day' );
		$this->vars['_EventEndDate'] = $end_date->format( Tribe__Events__Date_Utils::DBDATETIMEFORMAT );
	}

	/**
	 * Assess if this is an all day event.
	 */
	protected function set_all_day() {
		$this->vars['isEventAllDay'] = ( Tribe__Events__Date_Utils::is_all_day( $this->vars['_EventAllDay'] ) || ! Tribe__Events__Date_Utils::date_only( $this->vars['_EventStartDate'] ) ) ? 'checked="checked"' : '';
	}

	protected function set_start_date_time() {
		$this->vars['startMinuteOptions']   = Tribe__Events__View_Helpers::getMinuteOptions( $this->vars['_EventStartDate'], true );
		$this->vars['startHourOptions']     = Tribe__Events__View_Helpers::getHourOptions( $this->vars['_EventAllDay'] == 'yes' ? null : $this->vars['_EventStartDate'], true );
		$this->vars['startMeridianOptions'] = Tribe__Events__View_Helpers::getMeridianOptions( $this->vars['_EventStartDate'], true );

		$datepicker_format = Tribe__Events__Date_Utils::datepicker_formats( tribe_get_option( 'datepickerFormat' ) );

		if ( $this->vars['_EventStartDate'] ) {
			$start = Tribe__Events__Date_Utils::date_only( $this->vars['_EventStartDate'], false, $datepicker_format );
		}

		// If we don't have a valid start date, assume today's date
		$this->vars['EventStartDate'] = ( isset( $start ) && $start ) ? $start : date( $datepicker_format );
	}

	protected function set_end_date_time() {
		$this->vars['endMinuteOptions']   = Tribe__Events__View_Helpers::getMinuteOptions( $this->vars['_EventEndDate'] );
		$this->vars['endHourOptions']     = Tribe__Events__View_Helpers::getHourOptions( $this->vars['_EventAllDay'] == 'yes' ? null : $this->vars['_EventEndDate'] );
		$this->vars['endMeridianOptions'] = Tribe__Events__View_Helpers::getMeridianOptions( $this->vars['_EventEndDate'] );

		$datepicker_format = Tribe__Events__Date_Utils::datepicker_formats( tribe_get_option( 'datepickerFormat' ) );

		if ( $this->vars['_EventEndDate'] ) {
			$end = Tribe__Events__Date_Utils::date_only( $this->vars['_EventEndDate'], false, $datepicker_format );
		}

		// If we don't have a valid end date, assume today's date
		$this->vars['EventEndDate'] = ( isset( $end ) && $end ) ? $end : date( $datepicker_format );
	}

	/**
	 * Pull the expected variables into scope and load the meta box template.
	 */
	protected function do_meta_box() {
		$events_meta_box_template = $this->tribe->pluginPath . 'src/admin-views/events-meta-box.php';
		$events_meta_box_template = apply_filters( 'tribe_events_meta_box_template', $events_meta_box_template );

		extract( $this->vars );
		$event = $this->event;
		$tribe = $this->tribe;

		include( $events_meta_box_template );
	}
}
