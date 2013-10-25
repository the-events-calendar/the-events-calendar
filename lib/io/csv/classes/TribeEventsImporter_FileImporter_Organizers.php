<?php

/**
 * Class TribeEventsImporter_FileImporter_Organizers
 */
class TribeEventsImporter_FileImporter_Organizers extends TribeEventsImporter_FileImporter {

	protected $required_fields = array('organizer_name');

	protected function match_existing_post( array $record ) {
		$name = $this->get_value_by_key( $record, 'organizer_name' );
		$id = $this->find_matching_post_id( $name, TribeEvents::ORGANIZER_POST_TYPE );
		return $id;
	}

	protected function update_post( $post_id, array $record ) {
		$this->hack_to_remove_broken_filters();
		$organizer = $this->build_organizer_array( $record );
		TribeEventsAPI::updateOrganizer( $post_id, $organizer );
	}

	protected function create_post( array $record ) {
		$this->hack_to_remove_broken_filters();
		$organizer = $this->build_organizer_array( $record );
		$id = TribeEventsAPI::createOrganizer( $organizer );
		return $id;
	}

	private function build_organizer_array( array $record ) {
		$organizer = array(
			'Organizer' => $this->get_value_by_key( $record, 'organizer_name' ),
			'Email' => $this->get_value_by_key( $record, 'organizer_email' ),
			'Phone' => $this->get_value_by_key( $record, 'organizer_phone' ),
			'Website' => $this->get_value_by_key( $record, 'organizer_website' ),
		);
		return $organizer;
	}

	private function hack_to_remove_broken_filters() {
		// a stupid hack for some stupid code
		// the callback will automatically replace every organizer title with "Unnamed Organizer"
		$TribeEvents = TribeEvents::instance();
		remove_action( 'save_post', array( $TribeEvents, 'save_organizer_data' ), 16, 2 );
	}

}
