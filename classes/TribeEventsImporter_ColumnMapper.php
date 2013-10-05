<?php

/**
 * Class TribeEventsImporter_ColumnMapper
 */
class TribeEventsImporter_ColumnMapper {
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
		$selected = isset($this->defaults[$index]) ? $this->defaults[$index] : '';
		$html = '<select name="column_map[' . $index . ']">';
		$html .= '<option value="">' . __( 'Do Not Import', 'tribe-events-importer' ) . '</option>';
		foreach( $this->column_names as $key => $value ) {
			$html .= sprintf('<option value="%s" %s>%s</option>', esc_attr($key), selected($selected, $key, FALSE), esc_html($value));
		}
		$html .= '</select>';
		return $html;
	}

	public function get_column_label( $key ) {
		if ( isset($this->column_names[$key]) ) {
			return $this->column_names[$key];
		}
		return '';
	}

	private function get_event_column_names() {
		return array(
			'event_name' => __('Event Name', 'tribe-events-importer'),
			'event_description' => __('Event Description', 'tribe-events-importer'),
			'event_start_date' => __('Event Start Date', 'tribe-events-importer'),
			'event_start_time' => __('Event Start Time', 'tribe-events-importer'),
			'event_end_date' => __('Event End Date', 'tribe-events-importer'),
			'event_end_time' => __('Event End Time', 'tribe-events-importer'),
			'event_all_day' => __('All Day Event', 'tribe-events-importer'),
			'event_venue_name' => __('Event Venue Name', 'tribe-events-importer'),
			'event_organizer_name' => __('Event Organizer Name', 'tribe-events-importer'),
			'event_show_map_link' => __('Event Show Map Link', 'tribe-events-importer'),
			'event_show_map' => __('Event Show Map', 'tribe-events-importer'),
			'event_cost' => __('Event Cost', 'tribe-events-importer'),
			'event_phone' => __('Event Phone', 'tribe-events-importer'),
			'event_category' => __('Event Category', 'tribe-events-importer'),
			//'event_hide' => __('Event Hide From Upcoming', 'tribe-events-importer'),
		);
	}

	private function get_venue_column_names() {
		return array(
			'venue_name' => __('Venue Name', 'tribe-events-importer'),
			'venue_country' => __('Venue Country', 'tribe-events-importer'),
			'venue_address' => __('Venue Address', 'tribe-events-importer'),
			'venue_address2' => __('Venue Addres 2', 'tribe-events-importer'),
			'venue_city' => __('Venue City', 'tribe-events-importer'),
			'venue_state' => __('Venue State/Province', 'tribe-events-importer'),
			'venue_zip' => __('Venue Zip', 'tribe-events-importer'),
			'venue_phone' => __('Venue Phone', 'tribe-events-importer'),
		);
	}

	private function get_organizer_column_names() {
		return array(
			'organizer_name' => __('Organizer Name', 'tribe-events-importer'),
			'organizer_email' => __('Organizer Email', 'tribe-events-importer'),
			'organizer_website' => __('Organizer Website', 'tribe-events-importer'),
			'organizer_phone' => __('Organizer Phone', 'tribe-events-importer'),
		);
	}
}
