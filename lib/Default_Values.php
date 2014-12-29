<?php


class Tribe__Events__Pro__Default_Values extends Tribe__Events__Default_Values {


	public function venue_id() {
		return TribeEvents::instance()->getOption( 'eventsDefaultVenueID', 0 );
	}

	public function organizer_id() {
		return TribeEvents::instance()->getOption( 'eventsDefaultOrganizerID', 0 );
	}

	public function address() {
		return TribeEvents::instance()->getOption( 'eventsDefaultAddress', '' );
	}

	public function city() {
		return TribeEvents::instance()->getOption( 'eventsDefaultCity', '' );
	}

	public function state() {
		return TribeEvents::instance()->getOption( 'eventsDefaultState', '' );
	}

	public function province() {
		return TribeEvents::instance()->getOption( 'eventsDefaultProvince', '' );
	}

	public function zip() {
		return TribeEvents::instance()->getOption( 'eventsDefaultZip', '' );
	}

	public function country() {
		$country = TribeEvents::instance()->getOption( 'defaultCountry', NULL );
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
		return TribeEvents::instance()->getOption( 'eventsDefaultPhone', '' );
	}


}