<?php

/**
 * Manage setting and expiring cached data
 *
 * Select actions can be used to force cached
 * data to expire. Implemented so far:
 *  - save_post
 *
 */
class Tribe__Events__Cache {
	const NO_EXPIRATION  = 0;
	const NON_PERSISTENT = - 1;

	public static function setup() {
		wp_cache_add_non_persistent_groups( array( 'tribe-events-non-persistent' ) );
	}

	/**
	 * @param string $id
	 * @param mixed  $value
	 * @param int    $expiration
	 * @param string $expiration_trigger
	 *
	 * @return bool
	 */
	public function set( $id, $value, $expiration = 0, $expiration_trigger = '' ) {
		if ( $expiration == self::NON_PERSISTENT ) {
			$group      = 'tribe-events-non-persistent';
			$expiration = 1;
		} else {
			$group = 'tribe-events';
		}

		return wp_cache_set( $this->get_id( $id, $expiration_trigger ), $value, $group, $expiration );
	}

	/**
	 * @param        $id
	 * @param        $value
	 * @param int    $expiration
	 * @param string $expiration_trigger
	 *
	 * @return bool
	 */
	public function set_transient( $id, $value, $expiration = 0, $expiration_trigger = '' ) {
		return set_transient( $this->get_id( $id, $expiration_trigger ), $value, $expiration );
	}

	/**
	 * @param string $id
	 * @param string $expiration_trigger
	 *
	 * @return mixed
	 */
	public function get( $id, $expiration_trigger = '' ) {
		return wp_cache_get( $this->get_id( $id, $expiration_trigger ), 'tribe-events' );
	}

	/**
	 * @param string $id
	 * @param string $expiration_trigger
	 *
	 * @return mixed
	 */
	public function get_transient( $id, $expiration_trigger = '' ) {
		return get_transient( $this->get_id( $id, $expiration_trigger ) );
	}

	/**
	 * @param string $id
	 * @param string $expiration_trigger
	 *
	 * @return bool
	 */
	public function delete( $id, $expiration_trigger = '' ) {
		return wp_cache_delete( $this->get_id( $id, $expiration_trigger ), 'tribe-events' );
	}

	/**
	 * @param string $id
	 * @param string $expiration_trigger
	 *
	 * @return bool
	 */
	public function delete_transient( $id, $expiration_trigger = '' ) {
		return delete_transient( $this->get_id( $id, $expiration_trigger ) );
	}

	/**
	 * @param string $key
	 * @param string $expiration_trigger
	 *
	 * @return string
	 */
	public function get_id( $key, $expiration_trigger = '' ) {
		$last = empty( $expiration_trigger ) ? '' : $this->get_last_occurrence( $expiration_trigger );
		$id   = $key . $last;
		if ( strlen( $id ) > 40 ) {
			$id = md5( $id );
		}

		return $id;
	}

	/**
	 * @param string $action
	 *
	 * @return int
	 */
	public function get_last_occurrence( $action ) {
		return (int) get_option( 'tribe_last_' . $action, time() );
	}

	/**
	 * @param string $action
	 * @param int    $timestamp
	 */
	public function set_last_occurrence( $action, $timestamp = 0 ) {
		if ( empty( $timestamp ) ) {
			$timestamp = time();
		}
		update_option( 'tribe_last_' . $action, (int) $timestamp );
	}
}

