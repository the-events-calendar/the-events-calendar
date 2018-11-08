<?php

/**
 * Initialize Gutenberg Event Meta fields
 *
 * @since TBD
 */
class Tribe__Events__Editor__Meta extends Tribe__Editor__Meta {
	/**
	 * Register the required Meta fields for good Gutenberg saving
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function register() {
		register_meta( 'post', '_EventAllDay', $this->boolean() );
		register_meta( 'post', '_EventTimezone', $this->text() );
		register_meta( 'post', '_EventStartDate', $this->text() );
		register_meta( 'post', '_EventEndDate', $this->text() );
		register_meta( 'post', '_EventShowMap', $this->boolean() );
		register_meta( 'post', '_EventShowMapLink', $this->boolean() );
		register_meta( 'post', '_EventURL', $this->text() );
		register_meta( 'post', '_EventCost', $this->text() );
		register_meta( 'post', '_EventCostDescription', $this->text() );
		register_meta( 'post', '_EventCurrencySymbol', $this->text() );
		register_meta( 'post', '_EventCurrencyPosition', $this->text() );
		
		// Use sanitize_textarea_field to allow whitespaces
		register_meta(
			'post',
			'_EventDateTimeSeparator',
			array_merge(
				$this->text(),
				array(
					'sanitize_callback' => array( $this, 'sanitize_separator' ),
				)
			)
		);
		register_meta(
			'post',
			'_EventTimeRangeSeparator',
			array_merge(
				$this->text(),
				array(
					'sanitize_callback' => array( $this, 'sanitize_separator' ),
				)
			)
		);
		register_meta(
			'post',
			'_EventOrganizerID',
			array_merge(
				$this->numeric_array(),
				array(
					'description' => __( 'Event Organizers', 'the-events-calendar' ),
				)
			)
		);
		
		register_meta(
			'post',
			'_EventVenueID',
			array(
				'description'       => __( 'Event Organizers', 'the-events-calendar' ),
				'auth_callback'     => array( $this, 'auth_callback' ),
				'sanitize_callback' => 'absint',
				'type'              => 'integer',
				'single'            => true,
				'show_in_rest'      => true,
			)
		);
		
		// Organizers Meta
		register_meta( 'post', '_OrganizerEmail', $this->text() );
		register_meta( 'post', '_OrganizerPhone', $this->text() );
		register_meta( 'post', '_OrganizerWebsite', $this->text() );
		
		// Venue Meta
		register_meta( 'post', '_VenueAddress', $this->text() );
		register_meta( 'post', '_VenueCity', $this->text() );
		register_meta( 'post', '_VenueCountry', $this->text() );
		register_meta( 'post', '_VenueProvince', $this->text() );
		register_meta( 'post', '_VenueZip', $this->text() );
		register_meta( 'post', '_VenuePhone', $this->text() );
		register_meta( 'post', '_VenueURL', $this->text() );
		register_meta( 'post', '_VenueStateProvince', $this->text() );
		register_meta( 'post', '_VenueLat', $this->text() );
		register_meta( 'post', '_VenueLng', $this->text() );
	}
}
