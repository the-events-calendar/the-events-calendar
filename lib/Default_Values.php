<?php


class Tribe__Events__Pro__Default_Values extends Tribe__Events__Default_Values {


	public function venue_id() {
		return Tribe__Events__Events::instance()->getOption( 'eventsDefaultVenueID', 0 );
	}

	public function organizer_id() {
		return Tribe__Events__Events::instance()->getOption( 'eventsDefaultOrganizerID', 0 );
	}

	public function address() {
		return Tribe__Events__Events::instance()->getOption( 'eventsDefaultAddress', '' );
	}

	public function city() {
		return Tribe__Events__Events::instance()->getOption( 'eventsDefaultCity', '' );
	}

	public function state() {
		return Tribe__Events__Events::instance()->getOption( 'eventsDefaultState', '' );
	}

	public function province() {
		return Tribe__Events__Events::instance()->getOption( 'eventsDefaultProvince', '' );
	}

	public function zip() {
		return Tribe__Events__Events::instance()->getOption( 'eventsDefaultZip', '' );
	}

	public function country() {
		$country = Tribe__Events__Events::instance()->getOption( 'defaultCountry', NULL );
		if ( !$country || !is_array( $country ) ) {
			$country = array( '', '' );
		}
		for ( $i = 0 ; $i < 2 ; $i++ ) {
			if ( !isset( $country[$i] ) ) {
				$country[$i] = '';
			}
		}
		return $country;
	}

	public function phone() {
		return Tribe__Events__Events::instance()->getOption( 'eventsDefaultPhone', '' );
	}


}