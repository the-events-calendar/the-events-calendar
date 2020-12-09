<?php
/**
 * Class Tribe__Events__Importer__File_Importer_Venues
 */
class Tribe__Events__Importer__File_Importer_Venues extends Tribe__Events__Importer__File_Importer {

	protected $required_fields = [ 'venue_name' ];

	protected function match_existing_post( array $record ) {
		$name = $this->get_value_by_key( $record, 'venue_name' );
		$id   = $this->find_matching_post_id( $name, Tribe__Events__Main::VENUE_POST_TYPE );

		return $id;
	}

	protected function update_post( $post_id, array $record ) {
		$venue = $this->build_venue_array( $post_id, $record );

		Tribe__Events__API::updateVenue( $post_id, $venue );

		if ( $this->is_aggregator && ! empty( $this->aggregator_record ) ) {
			$this->aggregator_record->meta['activity']->add( 'venue', 'updated', $post_id );
		}
	}

	protected function create_post( array $record ) {
		$post_status_setting = tribe( 'events-aggregator.settings' )->default_post_status( 'csv' );
		$venue               = $this->build_venue_array( false, $record );
		$id                  = Tribe__Events__API::createVenue( $venue, $post_status_setting );

		if ( $this->is_aggregator && ! empty( $this->aggregator_record ) ) {
			$this->aggregator_record->meta['activity']->add( 'venue', 'created', $id );
		}

		return $id;
	}

	/**
	 * Build a venue array for creation/update of the current imported venue.
	 *
	 * @since 3.2
	 * @since 5.1.6 Adjust to prevent overwriting values that aren't mapped.
	 *
	 * @param int                   $venue_id The ID of the venue we're currently importing.
	 * @param array <string,string> $record   The event record from the import file. Only contains mapped values.
	 *                                        Useful if value and key above don't appear to match what's expected.
	 *                                        In the format [ mapped_key => value ].
	 *
	 * @return array $venue The array of venue data for creation/update.
	 */
	private function build_venue_array( $venue_id, array $record ) {
		$venue   = [];
		$columns = [
			'Venue'       => 'venue_name',
			'Address'     => 'venue_address',
			'City'        => 'venue_city',
			'Country'     => 'venue_country',
			'Description' => 'venue_description',
			'Phone'       => 'venue_phone',
			'Province'    => 'venue_state',
			'State'       => 'venue_state',
			'URL'         => 'venue_url',
			'Zip'         => 'venue_zip',
		];

		foreach ( $columns as $name => $key ) {
			// Don't set/overwrite unmapped columns.
			if ( ! $this->has_value_by_key( $record, $key ) ) {
				continue;
			}

			// Reset.
			$value = '';

			// Address is a concatenation of two possible values.
			if ( 'venue_address' === $key ) {
				$address_1 = trim( $this->get_value_by_key( $record, 'venue_address' ) );
				$address_2 = trim( $this->get_value_by_key( $record, 'venue_address2' ) );
				$value     = ( empty( $address_2 ) ) ? $address_1 : $address_1 . ' ' . $address_2;
			} else {
				$value = $this->get_value_by_key( $record, $key );
			}

			/**
			 * Allows filtering of individual main values before setting.
			 * Return boolean false to prevent importing that value.

			 * @since 5.1.6
			 *
			 * @param string                $key    The mapped key for the value we'll be importing.
			 *                                      From the $columns array above, this would be 'venue_name', for example.
			 * @param string                $value  The mapped value we'll be importing.
			 * @param array                 $venue  The entire array of venue data we're modifying.
			 * @param array <string,string> $record The event record from the import file. Only contains mapped values.
			 *                                      Useful if value and key above don't appear to match what's expected.
			 *                                      In the format [ mapped_key => value ].
			 * @param object                $this   The current instance of Tribe__Events__Importer__File_Importer_Venues.
			 */
			$value = apply_filters(
				"tribe_events_importer_venue_{$key}_value",
				$value,
				$key,
				$venue,
				$record,
				$this
			);

			if ( false === $value ) {
				continue;
			}

			$venue[ $name ] = $value;
		}

		// Handle the manual stuff.
		$venue['FeaturedImage'] = $this->get_featured_image( $venue_id, $record );
		$show_map_setting       = tribe( 'events-aggregator.settings' )->default_map( 'csv' );
		$venue['ShowMap']       = $venue_id ? get_post_meta( $venue_id, '_VenueShowMap', true ) : $show_map_setting;
		$venue['ShowMapLink']   = $venue_id ? get_post_meta( $venue_id, '_VenueShowMapLink', true ) : $show_map_setting;

		/**
		 * Allows triggering using the default values set in the admin for imported venues.
		 *
		 * @since 5.1.6
		 * @param int                   $venue_id The ID of the venue we're currently importing.
		 * @param array                 $venue    The array of venue data we're modifying.
		 * @param array <string,string> $record   The event record from the import file. Only contains mapped values.
		 *                                        Useful if value and key above don't appear to match what's expected.
		 *                                        In the format [ mapped_key => value ].
		 */
		$set_defaults = apply_filters(
			'tribe_events_importer_set_default_venue_import_values',
			false,
			$venue_id,
			$venue,
			$record
		);

		if ( $set_defaults ) {
			$venue = $this->set_defaults( $venue, $record );
		}

		/**
		 * Allows filtering of values before import.
		 *
		 * @since 4.2
		 *
		 * @param array                 $venue    The array of venue data we're modifying.
		 * @param array <string,string> $record   The event record from the import file. Only contains mapped values.
		 *                                        Useful if value and key above don't appear to match what's expected.
		 *                                        In the format [ mapped_key => value ].
		 * @param int                   $venue_id The ID of the venue we're currently importing.
		 * @param object                $this     The current instance of Tribe__Events__Importer__File_Importer_Venues.
		 *
			$record,
		 */
		$venue = apply_filters(
			'tribe_events_importer_venue_array',
			$venue,
			$record,
			$venue_id,
			$this
		);

		return $venue;
	}

	/**
	 * Set default venue values.
	 * Note this will only set a value if it has been mapped, and it is empty.
	 * If you are using the importer to erase values, you should NOT be triggering this!
	 *
	 * @since 5.1.6
	 *
	 * @param array                 $venue  The array of venue data we're modifying.
	 * @param array <string,string> $record The event record from the import file. Only contains mapped values.
	 *                                      Useful if value and key above don't appear to match what's expected.
	 *                                      In the format [ mapped_key => value ].
	 *
	 * @return array The modified venue data.
	 */
	public function set_defaults( $venue, $record ) {
		$columns = [
			'Address'  => 'address',
			'City'     => 'city',
			'Country'  => 'country',
			'Phone'    => 'phone',
			'Province' => 'state',
			'State'    => 'state',
			'URL'      => 'url',
			'Zip'      => 'zip',
		];

		foreach ( $columns as $name => $key ) {
			// Only fill in empty columns that we're importing.
			if ( ! isset( $venue[ $name ] ) || ! empty( $venue[ $name ] ) ) {
				continue;
			}

			/**
			 * Allows filtering of default value before setting.
			 * Also allows setting a value (specifically for imports) by filter
			 * that is not set in the admin for manually-created venues.
			 *
			 * @since 5.1.6
			 *
			 * @param string                $value  The default value as set in the admin "defaults" settings.
			 * @param string                $key    The mapped key for the value we'll be importing.
			 *                                      From the $columns array above,
			 *                                      this would be 'state' (the array "value"), for example.
			 * @param string                $name   The name for the value.
			 *                                      From the $columns array above,
			 *                                      this would be 'Province' (the array "key"), for example.
			 * @param array                 $venue  The entire array of venue data we're modifying.
			 *                                      In the format [ $key => $value ].
			 * @param array <string,string> $record The event record from the import file. Only contains mapped values.
			 *                                      Useful if value and key above don't appear to match what's expected.
			 *                                      In the format [ mapped_key => value ].
			 */
			$default_value = apply_filters(
				"tribe_events_importer_venue_default_{$key}_value",
				tribe_get_default_value( $key ),
				$key,
				$name,
				$venue,
				$record
			);

			/*
			 * Country comes through as an array: [ 'US', 'United States' ]
			 * We could handle this with a filter elsewhere, but let's deal with it here
			 * so we don't break Geolocation functions.
			 */
			if (
				'country' === $key
				&& is_array( $default_value )
			) {
				$default_value = array_pop( $default_value );
			}

			// Let's not set values that haven't been set in the admin!
			if ( empty( $default_value ) ) {
				continue;
			}

			$venue[ $name ] = $default_value;
		}

		return $venue;
	}
}
