<?php
// Don't load directly
defined( 'WPINC' ) or die;

class Tribe__Events__Aggregator__Record__Eventbrite extends Tribe__Events__Aggregator__Record__Abstract {
	public $origin = 'eventbrite';

	/**
	 * Queues the import on the Aggregator service
	 */
	public function queue_import( $args = array() ) {
		$eb_token = tribe_get_option( 'eb_token' );

		$defaults = array(
			'eventbrite_token' => $eb_token,
		);

		$args = wp_parse_args( $args, $defaults );

		/**
		 * Allows filtering to add a PUE key to be passed to the EA service
		 *
		 * @since  TBD
		 *
		 * @param  bool|string $pue_key PUE key
		 * @param  array       $args    Arguments to queue the import
		 * @param  self        $record  Which record we are dealing with
		 */
		$pue_key = apply_filters( 'tribe_aggregator_eventbrite_record_queue_import_pue_key', false, $args, $this );

		// If we have a key we add that to the Arguments
		if ( ! empty( $pue_key ) ) {
			$args['licenses'] = [
				'tribe-eventbrite' => $pue_key,
			];
		}

		return parent::queue_import( $args );
	}

	/**
	 * Gets the Regular Expression string to match a source URL
	 *
	 * @since  TBD
	 *
	 * @return string
	 */
	public static function get_source_regexp() {
		return '^(https?:\/\/)?(www\.)?eventbrite\.com(\.[a-z]{2})?\/';
	}

	/**
	 * Returns the Eventbrite authorization token generation URL.
	 *
	 * @param array $args
	 *
	 * @return string Either the URL to obtain Eventbrite authorization token or an empty string.
	 */
	public static function get_auth_url( $args = array() ) {
		$service = tribe( 'events-aggregator.service' );

		if ( $service->api() instanceof WP_Error ) {
			return '';
		}

		$url = $service->api()->domain . 'eventbrite/' . $service->api()->key;
		$defaults = array(
			'referral' => urlencode( home_url() ),
			'admin_url' => urlencode( get_admin_url() ),
			'type' => 'new',
			'lang' => get_bloginfo( 'language' ),
		);

		$args = wp_parse_args( $args, $defaults );

		$url = add_query_arg( $args, $url );

		return $url;
	}

	/**
	 * Public facing Label for this Origin
	 *
	 * @return string
	 */
	public function get_label() {
		return __( 'Eventbrite', 'the-events-calendar' );
	}

	/**
	 * Filters the event to ensure that a proper URL is in the EventURL
	 *
	 * @param array $event Event data
	 * @param Tribe__Events__Aggregator__Record__Abstract $record Aggregator Import Record
	 *
	 * @return array
	 */
	public static function filter_event_to_force_url( $event, $record ) {
		if ( 'eventbrite' !== $record->origin ) {
			return $event;
		}

		if ( ! empty( $event['EventURL'] ) ) {
			return $event;
		}

		$event['EventURL'] = $record->meta['source'];

		return $event;
	}

	/**
	 * Filters the event to ensure that fields are preserved that are not otherwise supported by Eventbrite
	 *
	 * @param array $event Event data
	 * @param Tribe__Events__Aggregator__Record__Abstract $record Aggregator Import Record
	 *
	 * @return array
	 */
	public static function filter_event_to_preserve_fields( $event, $record ) {
		if ( 'eventbrite' !== $record->origin ) {
			return $event;
		}

		return self::preserve_event_option_fields( $event );
	}
}
