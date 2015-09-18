<?php

/**
 * Class Tribe__Events__Pro__Post_Meta_Copier
 */
class Tribe__Events__Pro__Post_Meta_Copier {
	/**
	 * @param int $original_post
	 * @param int $destination_post
	 *
	 * @return void
	 */
	public function copy_meta( $original_post, $destination_post ) {
		$this->clear_meta( $destination_post );
		$post_meta_keys = get_post_custom_keys( $original_post );
		if ( empty( $post_meta_keys ) ) {
			return;
		}
		$meta_blacklist = $this->get_meta_key_blacklist( $original_post );
		$meta_keys      = array_diff( $post_meta_keys, $meta_blacklist );

		foreach ( $meta_keys as $meta_key ) {
			$meta_values = get_post_custom_values( $meta_key, $original_post );
			foreach ( $meta_values as $meta_value ) {
				$meta_value = maybe_unserialize( $meta_value );
				add_post_meta( $destination_post, $meta_key, $meta_value );
			}
		}
	}

	private function clear_meta( $post_id ) {
		$post_meta_keys = get_post_custom_keys( $post_id );
		$blacklist      = $this->get_meta_key_blacklist( $post_id );
		$post_meta_keys = array_diff( $post_meta_keys, $blacklist );
		foreach ( $post_meta_keys as $key ) {
			delete_post_meta( $post_id, $key );
		}
	}

	private function get_meta_key_blacklist( $post_id ) {
		$list = array(
			'_edit_lock',
			'_edit_last',
			'_EventStartDate',
			'_EventEndDate',
			'_EventStartDateUTC',
			'_EventEndDateUTC',
			'_EventDuration',
		);

		return apply_filters( 'tribe_events_meta_copier_blacklist', $list );
	}
}

