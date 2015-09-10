<?php

/**
 * Class Tribe__Events__Pro__Recurrence_Instance
 */
class Tribe__Events__Pro__Recurrence_Instance {
	private $parent_id = 0;
	private $start_date = null;
	private $end_date = null;
	private $timezone = '';
	private $post_id = 0;

	public function __construct( $parent_id, $start_date, $instance_id = 0 ) {
		$this->parent_id  = $parent_id;
		$this->start_date = new DateTime( '@' . $start_date );
		$this->post_id    = $instance_id;
	}

	public function save() {
		$parent       = get_post( $this->parent_id );
		$post_to_save = get_object_vars( $parent );
		unset( $post_to_save['ID'] );
		unset( $post_to_save['guid'] );
		$post_to_save['post_parent'] = $parent->ID;
		$post_to_save['post_name']   = $parent->post_name . '-' . $this->start_date->format( 'Y-m-d' );

		$duration = $this->get_duration();
		$this->end_date = $this->get_end_date();
		$this->timezone = Tribe__Events__Timezones::get_event_timezone_string( $this->parent_id );

		if ( ! empty( $this->post_id ) ) { // update the existing post
			$post_to_save['ID'] = $this->post_id;
			if ( get_post_status( $this->post_id ) == 'trash' ) {
				$post_to_save['post_status'] = get_post_status( $this->post_id );
			}
			$this->post_id = wp_update_post( $post_to_save );

			update_post_meta( $this->post_id, '_EventStartDate',    $this->db_formatted_start_date() );
			update_post_meta( $this->post_id, '_EventStartDateUTC', $this->db_formatted_start_date_utc() );
			update_post_meta( $this->post_id, '_EventEndDate',      $this->db_formatted_end_date() );
			update_post_meta( $this->post_id, '_EventEndDateUTC',   $this->db_formatted_end_date_utc() );
			update_post_meta( $this->post_id, '_EventDuration',     $duration );
		} else { // add a new post
			$post_to_save['guid'] = esc_url( add_query_arg( array( 'eventDate' => $this->start_date->format( 'Y-m-d' ) ), $parent->guid ) );
			$this->post_id        = wp_insert_post( $post_to_save );
			// save several queries by calling add_post_meta when we have a new post
			add_post_meta( $this->post_id, '_EventStartDate',    $this->db_formatted_start_date() );
			add_post_meta( $this->post_id, '_EventStartDateUTC', $this->db_formatted_start_date_utc() );
			add_post_meta( $this->post_id, '_EventEndDate',      $this->db_formatted_end_date() );
			add_post_meta( $this->post_id, '_EventEndDateUTC',   $this->db_formatted_end_date_utc() );
			add_post_meta( $this->post_id, '_EventDuration',     $duration );
		}

		$this->copy_meta(); // everything else
		$this->set_terms();
	}

	public function get_id() {
		return $this->post_id;
	}

	public function get_duration() {
		return get_post_meta( $this->parent_id, '_EventDuration', true );
	}

	public function get_end_date() {
		$duration      = $this->get_duration();
		$end_timestamp = (int) ( $this->start_date->format( 'U' ) ) + $duration;

		return new DateTime( '@' . $end_timestamp );
	}

	public function get_organizer() {
		$organizer = get_post_meta( $this->parent_id, '_EventOrganizerID', true );
		if ( empty( $organizer ) ) {
			return 0;
		}

		return (int) $organizer;
	}

	public function get_venue() {
		$venue = get_post_meta( $this->parent_id, '_EventVenueID', true );
		if ( empty( $venue ) ) {
			return 0;
		}

		return (int) $venue;
	}

	private function copy_meta() {
		$copier = new Tribe__Events__Pro__Post_Meta_Copier();
		$copier->copy_meta( $this->parent_id, $this->post_id );
	}

	private function set_terms() {
		$taxonomies = get_object_taxonomies( Tribe__Events__Main::POSTTYPE );
		foreach ( $taxonomies as $tax ) {
			$terms    = get_the_terms( $this->parent_id, $tax );
			$term_ids = empty( $terms ) ? array() : wp_list_pluck( $terms, 'term_id' );
			wp_set_object_terms( $this->post_id, $term_ids, $tax );
		}
	}

	/**
	 * @return string instance start_date in "Y-m-d H:i:s" format
	 */
	private function db_formatted_start_date() {
		return $this->start_date->format( Tribe__Events__Date_Utils::DBDATETIMEFORMAT );
	}

	/**
	 * @return string instance start_date (converted to UTC) in "Y-m-d H:i:s" format
	 */
	private function db_formatted_start_date_utc() {
		return ( class_exists( 'Tribe__Events__Timezones' ) && ! empty( $this->timezone ) )
			? Tribe__Events__Timezones::to_utc( $this->db_formatted_start_date(), $this->timezone )
			: $this->db_formatted_start_date();
	}

	/**
	 * @return string instance end_date in "Y-m-d H:i:s" format
	 */
	private function db_formatted_end_date() {
		return $this->end_date->format( Tribe__Events__Date_Utils::DBDATETIMEFORMAT );
	}

	/**
	 * @return string instance end_date (converted to UTC) in "Y-m-d H:i:s" format
	 */
	private function db_formatted_end_date_utc() {
		return ( class_exists( 'Tribe__Events__Timezones' ) && ! empty( $this->timezone ) )
			? Tribe__Events__Timezones::to_utc( $this->db_formatted_end_date(), $this->timezone )
			: $this->db_formatted_end_date();
	}
}

