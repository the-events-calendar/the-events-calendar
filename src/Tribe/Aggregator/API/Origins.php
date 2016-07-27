<?php

class Tribe__Events__Aggregator__API__Origins extends Tribe__Events__Aggregator__API__Abstract {
	/**
	 * Get event-aggregator origins
	 *
	 * @return array
	 */
	public function get() {
		$origins = get_transient( "{$this->cache_group}_origins" );

		if ( ! $origins ) {
			$origins = $this->service->get_origins();

			if ( is_wp_error( $origins ) ) {
				return $origins;
			}

			set_transient( "{$this->cache_group}_origins", $origins, 6 * HOUR_IN_SECONDS );
		}

		// Let's build out the translated text based on the names that come back from the EA service
		foreach ( $origins as &$origin ) {
			$origin->text = __( $origin->name, 'the-events-calendar' );
		}

		return apply_filters( 'tribe_aggregator_origins', $origins );
	}
}
