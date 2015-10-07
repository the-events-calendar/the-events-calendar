<?php


class Tribe__Events__Pro__Default_Values extends Tribe__Events__Default_Values {


	public function venue_id() {
		return tribe_get_option( 'eventsDefaultVenueID', 0 );
	}

	public function organizer_id() {
		return tribe_get_option( 'eventsDefaultOrganizerID', 0 );
	}

	public function address() {
		if ( $this->is_new_posttype( Tribe__Events__Main::VENUE_POST_TYPE ) ) {
			return tribe_get_option( 'eventsDefaultAddress', '' );
		}

		return '';
	}

	public function city() {
		if ( $this->is_new_posttype( Tribe__Events__Main::VENUE_POST_TYPE ) ) {
			return tribe_get_option( 'eventsDefaultCity', '' );
		}

		return '';
	}

	public function state() {
		if ( $this->is_new_posttype( Tribe__Events__Main::VENUE_POST_TYPE ) ) {
			return tribe_get_option( 'eventsDefaultState', '' );
		}

		return '';
	}

	public function province() {
		if ( $this->is_new_posttype( Tribe__Events__Main::VENUE_POST_TYPE ) ) {
			return tribe_get_option( 'eventsDefaultProvince', '' );
		}

		return '';
	}

	public function zip() {
		if ( $this->is_new_posttype( Tribe__Events__Main::VENUE_POST_TYPE ) ) {
			return tribe_get_option( 'eventsDefaultZip', '' );
		}

		return '';
	}

	public function country() {
		if ( $this->is_new_posttype( Tribe__Events__Main::VENUE_POST_TYPE ) ) {
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

		return '';
	}

	public function phone() {
		if ( $this->is_new_posttype( Tribe__Events__Main::VENUE_POST_TYPE ) ) {
			return tribe_get_option( 'eventsDefaultPhone', '' );
		}

		return '';
	}

	private function is_new_posttype( $type ) {
		global $typenow;

		return $typenow === $type;
	}
}
