<?php


class Tribe__Events__Pro__Default_Values extends Tribe__Events__Default_Values {


	public function venue_id() {
		return Tribe__Settings_Manager::get_option( 'eventsDefaultVenueID', 0 );
	}

	public function organizer_id() {
		return Tribe__Settings_Manager::get_option( 'eventsDefaultOrganizerID', 0 );
	}

	public function address() {
		return Tribe__Settings_Manager::get_option( 'eventsDefaultAddress', '' );
	}

	public function city() {
		return Tribe__Settings_Manager::get_option( 'eventsDefaultCity', '' );
	}

	public function state() {
		return Tribe__Settings_Manager::get_option( 'eventsDefaultState', '' );
	}

	public function province() {
		return Tribe__Settings_Manager::get_option( 'eventsDefaultProvince', '' );
	}

	public function zip() {
		return Tribe__Settings_Manager::get_option( 'eventsDefaultZip', '' );
	}

	public function country() {
		$country = Tribe__Settings_Manager::get_option( 'defaultCountry', NULL );
		if ( ! $country || ! is_array( $country ) ) {
			$country = array( '', '' );
		}
		for ( $i = 0 ; $i < 2 ; $i++ ) {
			if ( ! isset( $country[ $i ] ) ) {
				$country[ $i ] = '';
			}
		}
		return $country;
	}

	public function phone() {
		return Tribe__Settings_Manager::get_option( 'eventsDefaultPhone', '' );
	}
}
