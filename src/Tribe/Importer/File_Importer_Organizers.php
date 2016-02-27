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
		$organizer = $this->build_organizer_array( $post_id, $record );
		Tribe__Events__API::updateOrganizer( $post_id, $organizer );
	}

	protected function create_post( array $record ) {
		$organizer = $this->build_organizer_array( false, $record );
		$id        = Tribe__Events__API::createOrganizer( $organizer );

		return $id;
	}

	private function build_organizer_array( $organizer_id, array $record ) {
		$featured_image_content = $this->get_value_by_key( $record, 'featured_image' );
		$featured_image         = $organizer_id ? '' === get_post_meta( $organizer_id, '_wp_attached_file', true ) : $this->featured_image_uploader( $featured_image_content )->upload_and_get_attachment();
		$organizer              = array(
			'Organizer'     => $this->get_value_by_key( $record, 'organizer_name' ),
			'Email'         => $this->get_value_by_key( $record, 'organizer_email' ),
			'Phone'         => $this->get_value_by_key( $record, 'organizer_phone' ),
			'Website'       => $this->get_value_by_key( $record, 'organizer_website' ),
			'FeaturedImage' => $featured_image,
		);

		return $organizer;
	}
}
