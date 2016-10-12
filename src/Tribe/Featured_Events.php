<?php
class Tribe__Events__Featured_Events {
	const FEATURED_EVENT_KEY = '_tribe_featured';

	protected $query_helper;

	/**
	 * @internal
	 *
	 * @return Tribe__Events__Featured_Events__Query_Helper
	 */
	public function query_helper() {
		if ( empty( $this->query_helper ) ) {
			$this->query_helper = new Tribe__Events__Featured_Events__Query_Helper;
		}

		return $this->query_helper;
	}

	/**
	 * Marks an event as featured.
	 *
	 * @param int|WP_Post $event
	 *
	 * @return bool
	 */
	public function feature( $event = null ) {
		$event_id = Tribe__Main::post_id_helper( $event );

		if ( ! $event_id ) {
			return false;
		}

		return (bool) update_post_meta( $event_id, self::FEATURED_EVENT_KEY, true );
	}

	/**
	 * Clears the featured status of an event.
	 *
	 * @param int|WP_Post $event
	 *
	 * @return bool
	 */
	public function unfeature( $event= null ) {
		$event_id = Tribe__Main::post_id_helper( $event );

		if ( ! $event_id ) {
			return false;
		}

		return (bool) delete_post_meta( $event_id, self::FEATURED_EVENT_KEY );
	}

	/**
	 * Confirms if an event is featured.
	 * @param int|WP_Post $event
	 *
	 * @return bool
	 */
	public function is_featured( $event = null ) {
		$event_id = Tribe__Main::post_id_helper( $event );

		if ( ! $event_id ) {
			return false;
		}

		return (bool) get_post_meta( $event_id, self::FEATURED_EVENT_KEY, true );
	}
}