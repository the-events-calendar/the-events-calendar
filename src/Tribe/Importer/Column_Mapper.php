<?php

/**
 * Class Tribe__Events__Importer__Column_Mapper
 */
class Tribe__Events__Importer__Column_Mapper {
	private $column_names = [];
	private $import_type = '';
	private $defaults = [];

	public function __construct( $import_type ) {
		$this->import_type = $import_type;
		switch ( $this->import_type ) {
			case 'events':
				$this->column_names = $this->get_event_column_names();
				break;
			case 'venue':
			case 'venues':
				$this->column_names = $this->get_venue_column_names();
				break;
			case 'organizer':
			case 'organizers':
				$this->column_names = $this->get_organizer_column_names();
				break;
			default:
				/**
				 * Filters the column names that will be available for a custom import type.
				 *
				 * @param array $column_names
				 */
				$this->column_names = apply_filters( "tribe_event_import_{$import_type}_column_names", [] );
				break;
		}
	}

	public function set_defaults( $defaults ) {
		$this->defaults = $defaults;
	}

	public function make_select_box( $index ) {
		$selected = isset( $this->defaults[ $index ] ) ? $this->defaults[ $index ] : '';
		$html     = '<select name="column_map[' . $index . ']">';
		$html .= '<option value="">' . esc_html__( 'Do Not Import', 'the-events-calendar' ) . '</option>';
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
		$column_names = array(
			'event_name'              => esc_html__( 'Event Name', 'the-events-calendar' ),
			'event_description'       => esc_html__( 'Event Description', 'the-events-calendar' ),
			'event_excerpt'           => esc_html__( 'Event Excerpt', 'the-events-calendar' ),
			'event_start_date'        => esc_html__( 'Event Start Date', 'the-events-calendar' ),
			'event_start_time'        => esc_html__( 'Event Start Time', 'the-events-calendar' ),
			'event_end_date'          => esc_html__( 'Event End Date', 'the-events-calendar' ),
			'event_end_time'          => esc_html__( 'Event End Time', 'the-events-calendar' ),
			'event_timezone'          => esc_html__( 'Event Time Zone', 'the-events-calendar' ),
			'event_all_day'           => esc_html__( 'All Day Event', 'the-events-calendar' ),
			'event_hide'              => esc_html__( 'Hide Event From Event Listings', 'the-events-calendar' ),
			'event_sticky'            => esc_html__( 'Event Sticky in Month View', 'the-events-calendar' ),
			'feature_event'           => esc_html__( 'Feature Event', 'the-events-calendar' ),
			'event_venue_name'        => esc_html__( 'Event Venue Name', 'the-events-calendar' ),
			'event_organizer_name'    => esc_html__( 'Event Organizer Name(s) or ID(s)', 'the-events-calendar' ),
			'event_show_map_link'     => esc_html__( 'Event Show Map Link', 'the-events-calendar' ),
			'event_show_map'          => esc_html__( 'Event Show Map', 'the-events-calendar' ),
			'event_cost'              => esc_html__( 'Event Cost', 'the-events-calendar' ),
			'event_currency_symbol'   => esc_html__( 'Event Currency Symbol', 'the-events-calendar' ),
			'event_currency_position' => esc_html__( 'Event Currency Position', 'the-events-calendar' ),
			'event_category'          => esc_html__( 'Event Category', 'the-events-calendar' ),
			'event_tags'              => esc_html__( 'Event Tags', 'the-events-calendar' ),
			'event_website'           => esc_html__( 'Event Website', 'the-events-calendar' ),
			'featured_image'          => esc_html__( 'Event Featured Image', 'the-events-calendar' ),
			'event_comment_status'    => esc_html__( 'Event Allow Comments', 'the-events-calendar' ),
			'event_ping_status'       => esc_html__( 'Event Allow Trackbacks and Pingbacks', 'the-events-calendar' ),
		);

		/**
		 * Filters the Event column names that will be shown to the user.
		 *
		 * @param array<string|string> $column_names An array of column names for event import.
		 */
		return apply_filters( 'tribe_events_importer_event_column_names', $column_names );
	}

	private function get_venue_column_names() {
		$column_names = array(
			'venue_name'        => esc_html__( 'Venue Name', 'the-events-calendar' ),
			'venue_description' => esc_html__( 'Venue Description', 'the-events-calendar' ),
			'venue_country'     => esc_html__( 'Venue Country', 'the-events-calendar' ),
			'venue_address'     => esc_html__( 'Venue Address', 'the-events-calendar' ),
			'venue_address2'    => esc_html__( 'Venue Address 2', 'the-events-calendar' ),
			'venue_city'        => esc_html__( 'Venue City', 'the-events-calendar' ),
			'venue_state'       => esc_html__( 'Venue State/Province', 'the-events-calendar' ),
			'venue_zip'         => esc_html__( 'Venue Zip', 'the-events-calendar' ),
			'venue_phone'       => esc_html__( 'Venue Phone', 'the-events-calendar' ),
			'venue_url'         => esc_html__( 'Venue Website', 'the-events-calendar' ),
			'featured_image'    => esc_html__( 'Venue Featured Image', 'the-events-calendar' ),
		);

		/**
		 * Filters the Venue column names that will be shown to the user.
		 *
		 * @param array<string|string> $column_names An array of column names for venue import.
		 */
		return apply_filters( 'tribe_events_importer_venue_column_names', $column_names );
	}

	private function get_organizer_column_names() {
		$column_names = array(
			'organizer_name'        => esc_html__( 'Organizer Name', 'the-events-calendar' ),
			'organizer_description' => esc_html__( 'Organizer Description', 'the-events-calendar' ),
			'organizer_email'       => esc_html__( 'Organizer Email', 'the-events-calendar' ),
			'organizer_website'     => esc_html__( 'Organizer Website', 'the-events-calendar' ),
			'organizer_phone'       => esc_html__( 'Organizer Phone', 'the-events-calendar' ),
			'featured_image'        => esc_html__( 'Organizer Featured Image', 'the-events-calendar' ),
		);

		/**
		 * Filters the Organizer column names that will be shown to the user.
		 *
		 * @param array<string|string> $column_names An array of column names for organizer import.
		 */
		return apply_filters( 'tribe_events_importer_organizer_column_names', $column_names );
	}
}
