<?php

/**
 * Class Tribe__Events__Importer__Column_Mapper
 */
class Tribe__Events__Importer__Column_Mapper {
	private $column_names = array();
	private $import_type = '';
	private $defaults = array();

	public function __construct( $import_type ) {
		$this->import_type = $import_type;
		switch ( $this->import_type ) {
			case 'events':
				$this->column_names = $this->get_event_column_names();
				break;
			case 'venues':
				$this->column_names = $this->get_venue_column_names();
				break;
			case 'organizers':
				$this->column_names = $this->get_organizer_column_names();
				break;
		}
	}

	public function set_defaults( $defaults ) {
		$this->defaults = $defaults;
	}

	public function make_select_box( $index ) {
		$selected = isset( $this->defaults[ $index ] ) ? $this->defaults[ $index ] : '';
		$html     = '<select name="column_map[' . $index . ']">';
		$html .= '<option value="">' . esc_html__( 'Do Not Import', 'tribe-events-calendar' ) . '</option>';
		foreach ( $this->column_names as $key => $value ) {
			$html .= sprintf( '<option value="%s" %s>%s</option>', esc_attr( $key ), selected( $selected, $key, false ), esc_html( $value ) );
		}
		$html .= '</select>';

		return $html;
	}

	public function get_column_label( $key ) {
		if ( isset( $this->column_names[ $key ] ) ) {
			return $this->column_names[ $key ];
		}

		return '';
	}

	private function get_event_column_names() {
		return array(
			'event_name'           => esc_html__( 'Event Name', 'tribe-events-calendar' ),
			'event_description'    => esc_html__( 'Event Description', 'tribe-events-calendar' ),
			'event_start_date'     => esc_html__( 'Event Start Date', 'tribe-events-calendar' ),
			'event_start_time'     => esc_html__( 'Event Start Time', 'tribe-events-calendar' ),
			'event_end_date'       => esc_html__( 'Event End Date', 'tribe-events-calendar' ),
			'event_end_time'       => esc_html__( 'Event End Time', 'tribe-events-calendar' ),
			'event_all_day'        => esc_html__( 'All Day Event', 'tribe-events-calendar' ),
			'event_venue_name'     => esc_html__( 'Event Venue Name', 'tribe-events-calendar' ),
			'event_organizer_name' => esc_html__( 'Event Organizer Name', 'tribe-events-calendar' ),
			'event_show_map_link'  => esc_html__( 'Event Show Map Link', 'tribe-events-calendar' ),
			'event_show_map'       => esc_html__( 'Event Show Map', 'tribe-events-calendar' ),
			'event_cost'           => esc_html__( 'Event Cost', 'tribe-events-calendar' ),
			'event_category'       => esc_html__( 'Event Category', 'tribe-events-calendar' ),
			'event_website'  	   => esc_html__( 'Event Website', 'tribe-events-calendar' ),
		);
	}

	private function get_venue_column_names() {
		return array(
			'venue_name'     => esc_html__( 'Venue Name', 'tribe-events-calendar' ),
			'venue_country'  => esc_html__( 'Venue Country', 'tribe-events-calendar' ),
			'venue_address'  => esc_html__( 'Venue Address', 'tribe-events-calendar' ),
			'venue_address2' => esc_html__( 'Venue Address 2', 'tribe-events-calendar' ),
			'venue_city'     => esc_html__( 'Venue City', 'tribe-events-calendar' ),
			'venue_state'    => esc_html__( 'Venue State/Province', 'tribe-events-calendar' ),
			'venue_zip'      => esc_html__( 'Venue Zip', 'tribe-events-calendar' ),
			'venue_phone'    => esc_html__( 'Venue Phone', 'tribe-events-calendar' ),
			'venue_url'      => esc_html__( 'Venue Website', 'tribe-events-calendar' ),
		);
	}

	private function get_organizer_column_names() {
		return array(
			'organizer_name'    => esc_html__( 'Organizer Name', 'tribe-events-calendar' ),
			'organizer_email'   => esc_html__( 'Organizer Email', 'tribe-events-calendar' ),
			'organizer_website' => esc_html__( 'Organizer Website', 'tribe-events-calendar' ),
			'organizer_phone'   => esc_html__( 'Organizer Phone', 'tribe-events-calendar' ),
		);
	}
}
