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
		$html .= '<option value="">' . __( 'Do Not Import', 'the-events-calendar' ) . '</option>';
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
		return apply_filters( 'tribe_events_importer_event_column_names', array(
			'event_name'              => __( 'Event Name', 'the-events-calendar' ),
			'event_description'       => __( 'Event Description', 'the-events-calendar' ),
			'event_start_date'        => __( 'Event Start Date', 'the-events-calendar' ),
			'event_start_time'        => __( 'Event Start Time', 'the-events-calendar' ),
			'event_end_date'          => __( 'Event End Date', 'the-events-calendar' ),
			'event_end_time'          => __( 'Event End Time', 'the-events-calendar' ),
			'event_all_day'           => __( 'All Day Event', 'the-events-calendar' ),
			'event_venue_name'        => __( 'Event Venue Name', 'the-events-calendar' ),
			'event_organizer_name'    => __( 'Event Organizer Name', 'the-events-calendar' ),
			'event_show_map_link'     => __( 'Event Show Map Link', 'the-events-calendar' ),
			'event_show_map'          => __( 'Event Show Map', 'the-events-calendar' ),
			'event_cost'              => __( 'Event Cost', 'the-events-calendar' ),
			'event_currency_symbol'   => __( 'Event Currency Symbol', 'the-events-calendar' ),
			'event_currency_position' => __( 'Event Currency Position', 'the-events-calendar' ),
			'event_category'          => __( 'Event Category', 'the-events-calendar' ),
			'event_tags'              => __( 'Event Tags', 'the-events-calendar' ),
			'event_website'           => __( 'Event Website', 'the-events-calendar' ),
		) );
	}

	private function get_venue_column_names() {
		return array(
			'venue_name'     => __( 'Venue Name', 'the-events-calendar' ),
			'venue_country'  => __( 'Venue Country', 'the-events-calendar' ),
			'venue_address'  => __( 'Venue Address', 'the-events-calendar' ),
			'venue_address2' => __( 'Venue Address 2', 'the-events-calendar' ),
			'venue_city'     => __( 'Venue City', 'the-events-calendar' ),
			'venue_state'    => __( 'Venue State/Province', 'the-events-calendar' ),
			'venue_zip'      => __( 'Venue Zip', 'the-events-calendar' ),
			'venue_phone'    => __( 'Venue Phone', 'the-events-calendar' ),
			'venue_url'      => __( 'Venue Website', 'the-events-calendar' ),
		);
	}

	private function get_organizer_column_names() {
		return array(
			'organizer_name'    => __( 'Organizer Name', 'the-events-calendar' ),
			'organizer_email'   => __( 'Organizer Email', 'the-events-calendar' ),
			'organizer_website' => __( 'Organizer Website', 'the-events-calendar' ),
			'organizer_phone'   => __( 'Organizer Phone', 'the-events-calendar' ),
		);
	}
}
