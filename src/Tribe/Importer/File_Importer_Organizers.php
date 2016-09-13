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
		if ( $this->is_aggregator && ! empty( $this->aggregator_record ) ) {
			$this->aggregator_record->meta['activity']->add( 'organizer', 'updated', $post_id );
		}
	}

	protected function create_post( array $record ) {
		$post_status_setting = Tribe__Events__Aggregator__Settings::instance()->default_post_status( 'csv' );
		$organizer = $this->build_organizer_array( false, $record );
		$id        = Tribe__Events__API::createOrganizer( $organizer, $post_status_setting );
		if ( $this->is_aggregator && ! empty( $this->aggregator_record ) ) {
			$this->aggregator_record->meta['activity']->add( 'organizer', 'created', $id );
		}

		return $id;
	}

	private function build_organizer_array( $organizer_id, array $record ) {
		$organizer              = array(
			'Organizer'     => $this->get_value_by_key( $record, 'organizer_name' ),
			'Description'   => $this->get_value_by_key( $record, 'organizer_description' ),
			'Email'         => $this->get_value_by_key( $record, 'organizer_email' ),
			'Phone'         => $this->get_value_by_key( $record, 'organizer_phone' ),
			'Website'       => $this->get_value_by_key( $record, 'organizer_website' ),
			'FeaturedImage' => $this->get_featured_image( $organizer_id, $record ),
		);

		/**
		 * Provides an opportunity to modify organizer details during CSV imports.
		 *
		 * @param array $organizer
		 * @param array $record
		 */
		return apply_filters( 'tribe_events_csv_import_organizer_fields', $organizer, $record );
	}
}
