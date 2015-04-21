<?php

/**
 * Class Tribe__Events__Importer__File_Importer_Organizers
 */
class Tribe__Events__Importer__File_Importer_Organizers extends Tribe__Events__Importer__File_Importer {

	protected $required_fields = array( 'organizer_name' );

	protected function match_existing_post( array $record ) {
		$name = $this->get_value_by_key( $record, 'organizer_name' );
		$id   = $this->find_matching_post_id( $name, Tribe__Events__Main::ORGANIZER_POST_TYPE );

		return $id;
	}

	protected function update_post( $post_id, array $record ) {
		$organizer = $this->build_organizer_array( $record );
		Tribe__Events__API::updateOrganizer( $post_id, $organizer );
	}

	protected function create_post( array $record ) {
		$organizer = $this->build_organizer_array( $record );
		$id        = Tribe__Events__API::createOrganizer( $organizer );

		return $id;
	}

	private function build_organizer_array( array $record ) {
		$organizer = array(
			'Organizer' => $this->get_value_by_key( $record, 'organizer_name' ),
			'Email'     => $this->get_value_by_key( $record, 'organizer_email' ),
			'Phone'     => $this->get_value_by_key( $record, 'organizer_phone' ),
			'Website'   => $this->get_value_by_key( $record, 'organizer_website' ),
		);

		return $organizer;
	}
}
