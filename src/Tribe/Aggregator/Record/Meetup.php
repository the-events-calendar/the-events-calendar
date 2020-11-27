<?php
// Don't load directly
defined( 'WPINC' ) or die;

class Tribe__Events__Aggregator__Record__Meetup extends Tribe__Events__Aggregator__Record__Abstract {
	public $origin = 'meetup';

	/**
	 * Queues the import on the Aggregator service
	 */
	public function queue_import( $args = [] ) {
		$meetup_api_key    = tribe_get_option( 'meetup_api_key' );

		$defaults = array(
			'meetup_api_key' => $meetup_api_key,
		);

		$args = wp_parse_args( $args, $defaults );

		return parent::queue_import( $args );
	}

	/**
	 * Gets the Regular Expression string to match a source URL
	 *
	 * @since 4.6.18
	 *
	 * @return string
	 */
	public static function get_source_regexp() {
		return '^(https?:\/\/)?(www\.)?meetup\.com(\.[a-z]{2})?\/';
	}

	/**
	 * Public facing Label for this Origin
	 *
	 * @return string
	 */
	public function get_label() {
		return __( 'Meetup', 'the-events-calendar' );
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
		if ( 'meetup' !== $record->origin ) {
			return $event;
		}

		if ( ! empty( $event['EventURL'] ) ) {
			return $event;
		}

		$event['EventURL'] = $record->meta['source'];

		return $event;
	}

	/**
	 * Filters the event to ensure that fields are preserved that are not otherwise supported by Meetup
	 *
	 * @param array $event Event data
	 * @param Tribe__Events__Aggregator__Record__Abstract $record Aggregator Import Record
	 *
	 * @return array
	 */
	public static function filter_event_to_preserve_fields( $event, $record ) {
		if ( 'meetup' !== $record->origin ) {
			return $event;
		}

		return self::preserve_event_option_fields( $event );
	}

	/**
	 * Returns the Meetup authorization token generation URL.
	 *
	 * @since 4.9.6
	 *
	 * @param array $args
	 *
	 * @return string Either the URL to obtain Eventbrite authorization token or an empty string.
	 */
	public static function get_auth_url( $args = array() ) {
		$service = tribe( 'events-aggregator.service' );

		$api  = $service->api();
		if ( $api instanceof WP_Error ) {
			return '';
		}

		$key  = $api->key;
		$key2 = null;

		if ( ! empty( $api->licenses['tribe-meetup'] ) ) {
			$meetup_license = $api->licenses['tribe-meetup'];

			if ( empty( $key ) ) {
				$key = $meetup_license;
			} else {
				$key2 = $meetup_license;
			}
		}

		$url = $api->domain . 'meetup/' . $key;
		$defaults = array(
			'referral'  => urlencode( home_url() ),
			'admin_url' => urlencode( get_admin_url() ),
			'type'      => 'new',
			'lang'      => get_bloginfo( 'language' ),
		);

		if ( $key2 ) {
			$defaults['licenses'] = array(
				'tribe-meetup' => $key2,
			);
		}

		$args = wp_parse_args( $args, $defaults );

		$url = add_query_arg( $args, $url );

		return $url;
	}
}
