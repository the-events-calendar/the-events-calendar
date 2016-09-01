<?php
// Don't load directly
defined( 'WPINC' ) or die;

class Tribe__Events__Aggregator__Record__Facebook extends Tribe__Events__Aggregator__Record__Abstract {
	public $origin = 'facebook';

	/**
	 * Queues the import on the Aggregator service
	 */
	public function queue_import( $args = array() ) {
		$fb_token = tribe_get_option( 'fb_token' );

		$defaults = array(
			'facebook_token' => $fb_token,
		);

		$args = wp_parse_args( $args, $defaults );

		return parent::queue_import( $args );
	}

	public static function get_auth_url( $args = array() ) {
		$service = Tribe__Events__Aggregator__Service::instance();
		$url = $service->api()->domain . 'facebook/' . $service->api()->key;
		$defaults = array(
			'referral' => urlencode( home_url() ),
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
}
