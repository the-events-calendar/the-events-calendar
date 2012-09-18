<?php
/*-------------------------------------------------------------------------------------
* File description: Main class for Geo Location functionality
*
*
* Created by:  Daniel Dvorkin
* For:         Modern Tribe Inc . ( http://tri.be/)
*
* Date: 		9 / 18 / 12 12:31 PM
*-------------------------------------------------------------------------------------*/

class TribeEventsGeoLoc {

	public function load() {
		add_action( 'save_post', array($this, 'save_venue_geodata') );
	}


	function save_venue_geodata( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

		if ( !isset( $_POST['geoloc_noncename'] ) ) return;

		if ( !wp_verify_nonce( $_POST['geoloc_noncename'], plugin_basename( __FILE__ ) ) ) return;

		$lat = $lng = false;

		$address = isset( $_POST["address"] ) ? $_POST["address"] : NULL;

		if ( $address ) {
			$data = wp_remote_get( "http://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode( $address ) . "&sensor=false" );
			if ( !is_wp_error( $data ) && isset( $data["body"] ) ) {
				$data_arr = json_decode( $data["body"] );
				$lat      = $data_arr->results[0]->geometry->location->lat;
				$lng      = $data_arr->results[0]->geometry->location->lng;
			}

			if ( $lat ) update_post_meta( $post_id, '_lat', $lat );
			if ( $lng ) update_post_meta( $post_id, '_lng', $lng );

			update_post_meta( $post_id, '_address', $address );
		}

	}

}
