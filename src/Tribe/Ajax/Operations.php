<?php


class Tribe__Events__Ajax__Operations {

	public function verify_or_exit( $nonce, $action, $exit_data = array() ) {
		if ( ! wp_verify_nonce( $nonce, $action ) ) {
			exit( $exit_data );
		}

		return true;
	}

	public function exit_data( $data = array() ) {
		exit( $data );
	}
}