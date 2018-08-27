<?php


class Tribe__Events__Default_Values {

	public function __call( $method, $args ) {
		if ( method_exists( $this, strtolower( $method ) ) ) {
			return call_user_func_array( array( $this, strtolower( $method ) ), $args );
		}
		return '';
	}

	public function venue() {
		return $this->venue_id();
	}

	public function venue_id() {
		return null;
	}

	public function organizer() {
		return $this->organizer_id();
	}

	public function organizer_id() {
		return 0;
	}

	public function address() {
		return '';
	}

	public function city() {
		return '';
	}

	public function state() {
		return '';
	}

	public function province() {
		return '';
	}

	public function zip() {
		return '';
	}

	public function country() {
		return array( '', '' );
	}

	public function phone() {
		return '';
	}


}
