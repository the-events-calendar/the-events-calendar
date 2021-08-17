<?php

_deprecated_file( __FILE__, '4.6.23', 'Deprecated along with Event Aggregator support for Facebook in light of Facebook API changes.' );

// Don't load directly
defined( 'WPINC' ) or die;

class Tribe__Events__Aggregator__Record__Facebook extends Tribe__Events__Aggregator__Record__Abstract {
	public $origin = 'facebook';

	/**
	 * Queues the import on the Aggregator service
	 */
	public function queue_import( $args = [] ) {
		$fb_token = tribe_get_option( 'fb_token' );

		$defaults = [
			'facebook_token' => $fb_token,
		];

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
		return '^(https?:\/\/)?(www\.)?facebook\.com(\.[a-z]{2})?\/';
	}

	/**
	 * Returns the Facebook authorization token generation URL.
	 *
	 * @param array $args
	 *
	 * @return string Either the URL to obtain FB authorization token or an empty string.
	 */
	public static function get_auth_url( $args = [] ) {
		$service = tribe( 'events-aggregator.service' );

		if ( $service->api() instanceof WP_Error ) {
			return '';
		}

		$url = $service->api()->domain . 'facebook/' . $service->api()->key;
		$defaults = [
			'referral'  => urlencode( home_url() ),
			'admin_url' => urlencode( get_admin_url() ),
			'type'      => 'new',
			'lang'      => get_bloginfo( 'language' ),
		];

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
		return __( 'Facebook', 'the-events-calendar' );
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
		if ( 'facebook' !== $record->origin ) {
			return $event;
		}

		if ( ! empty( $event['EventURL'] ) ) {
			return $event;
		}

		$event['EventURL'] = $record->meta['source'];

		return $event;
	}

	/**
	 * Filters the event to ensure that fields are preserved that are not otherwise supported by Facebook
	 *
	 * @param array $event Event data
	 * @param Tribe__Events__Aggregator__Record__Abstract $record Aggregator Import Record
	 *
	 * @return array
	 */
	public static function filter_event_to_preserve_fields( $event, $record ) {
		if ( 'facebook' !== $record->origin ) {
			return $event;
		}

		return self::preserve_event_option_fields( $event );
	}
}
