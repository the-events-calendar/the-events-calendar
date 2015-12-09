<?php
class Tribe__Events__Pro__Default_Values extends Tribe__Events__Default_Values {

	public function venue_id() {
		return tribe_get_option( 'eventsDefaultVenueID', 0 );
	}

	public function organizer_id() {
		return tribe_get_option( 'eventsDefaultOrganizerID', 0 );
	}

	public function address() {
		return tribe_get_option( 'eventsDefaultAddress', '' );
	}

	public function city() {
		return tribe_get_option( 'eventsDefaultCity', '' );
	}

	public function state() {
		return tribe_get_option( 'eventsDefaultState', '' );
	}

	public function province() {
		return tribe_get_option( 'eventsDefaultProvince', '' );
	}

	public function zip() {
		return tribe_get_option( 'eventsDefaultZip', '' );
	}

	public function country() {
		$country = tribe_get_option( 'defaultCountry', null );
		if ( ! $country || ! is_array( $country ) ) {
			$country = array( '', '' );
		}
		for ( $i = 0; $i < 2; $i ++ ) {
			if ( ! isset( $country[ $i ] ) ) {
				$country[ $i ] = '';
			}
		}

		return $country;
	}

	public function phone() {
		return tribe_get_option( 'eventsDefaultPhone', '' );
	}
}