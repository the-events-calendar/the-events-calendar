<?php
// Don't load directly
defined( 'WPINC' ) or die;

class Tribe__Events__Aggregator__Record__Eventbrite extends Tribe__Events__Aggregator__Record__Abstract {
	public $origin = 'eventbrite';

	/**
	 * Queues the import on the Aggregator service
	 */
	public function queue_import( $args = array() ) {

		$defaults = array(
			'site' => urlencode( site_url() ),
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

		$api = $service->api();
		$key = $api->key;
		$key2 = null;

		if ( ! empty( $api->licenses['tribe-eventbrite'] ) ) {
			$eb_license = $api->licenses['tribe-eventbrite'];

			if ( empty( $key ) ) {
				$key = $eb_license;
			} else {
				$key2 = $eb_license;
			}
		}

		$url = $service->api()->domain . 'eventbrite/' . $key;
		$defaults = array(
			'referral' => urlencode( home_url() ),
			'admin_url' => urlencode( get_admin_url() ),
			'type' => 'new',
			'lang' => get_bloginfo( 'language' ),
		);

		if ( $key2 ) {
			$defaults['licenses'] = array(
				'tribe-eventbrite' => $key2,
			);
		}

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

	/**
	 * Add Site URL for Eventbrite Requets
	 *
	 * @since 4.6.18
	 *
	 * @param array $args EA REST arguments
	 * @param Tribe__Events__Aggregator__Record__Abstract $record Aggregator Import Record
	 *
	 * @return mixed
	 */
	public static function filter_add_site_get_import_data( $args, $record ) {
		if ( 'eventbrite' !== $record->origin ) {
			return $args;
		}

		$args['site'] = urlencode( site_url() );

		return $args;
	}
}
