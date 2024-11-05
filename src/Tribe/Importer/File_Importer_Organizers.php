<?php

/**
 * Class Tribe__Events__Importer__File_Importer_Organizers
 */
class Tribe__Events__Importer__File_Importer_Organizers extends Tribe__Events__Importer__File_Importer {

	protected $required_fields = [ 'organizer_name' ];

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
		$post_status_setting = tribe( 'events-aggregator.settings' )->default_post_status( 'csv' );
		$organizer           = $this->build_organizer_array( false, $record );
		$id                  = Tribe__Events__API::createOrganizer( $organizer, $post_status_setting );

		if ( $this->is_aggregator && ! empty( $this->aggregator_record ) ) {
			$this->aggregator_record->meta['activity']->add( 'organizer', 'created', $id );
		}

		return $id;
	}

	/**
	 * Build an organizer array for creation/update of the current imported organizer.
	 *
	 * @since 3.2
	 * @since 5.1.6 Adjust to prevent overwriting values that aren't mapped.
	 * @since 6.7.2   Add code to allow importing custom data.
	 *
	 * @param int   $organizer_id The ID of the organizer we're currently importing.
	 * @param array $record       An event record from the import.
	 *
	 * @return array $organizer The array of organizer data for creation/update.
	 */
	private function build_organizer_array( int $organizer_id, array $record ) {
		$organizer = [];
		$columns   = [
			'Organizer'   => 'organizer_name',
			'Description' => 'organizer_description',
			'Email'       => 'organizer_email',
			'Phone'       => 'organizer_phone',
			'Website'     => 'organizer_website',
		];

		foreach ( $columns as $name => $key ) {
			// Reset.
			$value = '';

			// Don't set/overwrite unmapped columns.
			if ( ! $this->has_value_by_key( $record, $key ) ) {
				continue;
			}

			/**
			 * Allows filtering of main values before setting.
			 * Return boolean false to prevent importing that value.
			 *
			 * @since 5.1.6
			 *
			 * @param string $key       The key for the value we'll be importing.
			 * @param string $value     The value we'll be importing.
			 * @param array  $organizer The array of organizer data we're modifying.
			 * @param array  $record    The event record from the import.
			 */
			$value = apply_filters(
				"tribe_events_importer_organizer_{$key}_value",
				$this->get_value_by_key( $record, $key ),
				$key,
				$organizer,
				$record,
				$this
			);

			if ( false === $value ) {
				continue;
			}

			$organizer[ $name ] = $value;
		}

		// Handle the manual stuff.
		$organizer['FeaturedImage'] = $this->get_featured_image( $organizer, $record );

		/**
		 * A filter to allow saving custom data when importing organizers.
		 *
		 * @since 6.7.2
		 *
		 * @param array $additional_organizer_fields Array where the key is the meta_key to be used and the value is
		 *                                           the slug of the column that contains the value to be saved.
		 *
		 * @note During the import process the meta key will receive the "_Organizer" prefix.
		 *       For example, if the array key is "CustomData", then the meta key will be "_OrganizerCustomData".
		 */
		$additional_organizer_fields = apply_filters( 'tribe_events_csv_import_organizer_additional_fields', [] );

		if ( ! empty( $additional_organizer_fields ) ) {
			foreach ( $additional_organizer_fields as $key => $csv_column ) {
				// Get the value from the record based on the column slug.
				$value = $this->get_value_by_key( $record, $csv_column );
				/**
				 * This is needed if custom fields are being added to the post type.
				 * When saving, the metadata gets the "_Organizer" prefix automatically, so it should be removed
				 * before the import to avoid duplication.
				 */
				$key = preg_replace( '/' . preg_quote( '_Organizer', '/' ) . '/', '', $key, 1 );

				// Handle multiple values separated by a pipe (|).
				if ( strpos( $value, '|' ) > -1 ) {
					$organizer[ $key ] = explode( '|', $value );
				} else {
					$organizer[ $key ] = $value;
				}
			}
		}

		/**
		 * Allows filtering of record values before import.
		 *
		 * @since 5.1.6
		 *
		 * @param array $organizer    The array of organizer data we're modifying.
		 * @param array $record       The event record from the import.
		 * @param int   $organizer_id The ID of the organizer we're currently importing.
		 */
		$organizer = apply_filters(
			'tribe_events_importer_organizer_fields',
			$organizer,
			$record,
			$organizer_id,
			$this
		);

		return $organizer;
	}
}
