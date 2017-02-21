<?php
// Don't load directly
defined( 'WPINC' ) or die;

abstract class Tribe__Events__Aggregator__Tabs__Abstract extends Tribe__Tabbed_View__Tab {


	/**
	 * Creates a way to include the this tab HTML easily
	 *
	 * @return string Content of the tab
	 */
	public function render() {
		$data = array(
			'tab' => $this,
		);

		return Tribe__Events__Aggregator__Page::instance()->template( 'tabs/' . $this->get_slug(), $data );
	}

	/**
	 * The constructor for any new Tab on the Aggregator,
	 * If you need an action to be hook to any Tab, use this.
	 */
	public function __construct() {
	}

	/**
	 * Fetches the link to this tab
	 *
	 * @param array|string $args     Query String or Array with the arguments
	 * @param boolean      $relative Return a relative URL or absolute
	 *
	 * @return string
	 */
	public function get_url( $args = array(), $relative = false ) {
		$defaults = array(
			'tab' => $this->get_slug(),
		);

		// Allow the link to be "changed" on the fly
		$args = wp_parse_args( $args, $defaults );

		// Escape after the filter
		return Tribe__Events__Aggregator__Page::instance()->get_url( $args, $relative );
	}

	/**
	 * Determines if this Tab is currently displayed
	 *
	 * @return boolean
	 */
	public function is_active() {
		return Tribe__Events__Aggregator__Tabs::instance()->is_active( $this->get_slug() );
	}

	public function handle_submit() {
		$data = array(
			'message' => __( 'There was a problem processing your import. Please try again.', 'the-events-calendar' ),
		);

		if ( ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) && ! $this->is_active() ) {
			return;
		}

		if ( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
			return;
		}

		if ( empty( $_POST['aggregator'] ) ) {
			return;
		}

		// validate nonce
		if ( empty( $_POST['tribe_aggregator_nonce'] ) || ! wp_verify_nonce( $_POST['tribe_aggregator_nonce'], 'tribe-aggregator-save-import' ) ) {
			wp_send_json_error( $data );
		}

		$post_data = $_POST['aggregator'];

		if ( empty( $post_data['origin'] ) || empty( $post_data[ $post_data['origin'] ] ) ) {
			wp_send_json_error( $data );
		}

		$data = $post_data[ $post_data['origin'] ];

		$record = Tribe__Events__Aggregator__Records::instance()->get_by_origin( $post_data['origin'] );

		$meta = array(
			'origin'        => $post_data['origin'],
			'type'          => empty( $data['import_type'] )      ? 'manual' : $data['import_type'],
			'frequency'     => empty( $data['import_frequency'] ) ? null     : $data['import_frequency'],
			'file'          => empty( $data['file'] )             ? null     : $data['file'],
			'keywords'      => empty( $data['keywords'] )         ? null     : $data['keywords'],
			'location'      => empty( $data['location'] )         ? null     : $data['location'],
			'start'         => empty( $data['start'] )            ? null     : $data['start'],
			'radius'        => empty( $data['radius'] )           ? null     : $data['radius'],
			'source'        => empty( $data['source'] )           ? null     : $data['source'],
			'content_type'  => empty( $data['content_type'] )     ? null     : $data['content_type'],
			'schedule_day'  => empty( $data['schedule_day'] )     ? null     : $data['schedule_day'],
			'schedule_time' => empty( $data['schedule_time'] )    ? null     : $data['schedule_time'],
		);

		$meta = $this->validate_meta_by_origin( $meta['origin'], $meta );

		if ( is_wp_error( $meta ) ) {
			/** @var WP_Error $validated */
			wp_send_json_error( $meta->get_error_message() );
		}

		return array(
			'record' => $record,
			'post_data' => $post_data,
			'meta' => $meta,
		);
	}

	/**
	 * Validates the meta in relation to the origin.
	 *
	 *
	 * @param string $origin
	 * @param array  $meta
	 *
	 * @return array|WP_Error The updated/validated meta array or A `WP_Error` if the validation failed.
	 */
	protected function validate_meta_by_origin( $origin, $meta ) {
		$result = $meta;

		switch ( $origin ) {
			case 'csv':
			case 'ics':
				if ( empty( $meta['file'] ) ) {
					$result = new WP_Error( 'missing-file', __( 'Please provide the file that you wish to import.', 'the-events-calendar' ) );
				}
				break;
			case 'facebook':
				if ( empty( $meta['url'] ) || ! preg_match( '!(https?://)?(www\.)?facebook\.com!', $meta['source'] ) ) {
					$result = new WP_Error( 'not-facebook-url', __( 'Please provide a Facebook URL when importing from Facebook.', 'the-events-calendar' ) );
				}
				break;
			case 'meetup':
				if ( empty( $meta['url'] ) || ! preg_match( '!(https?://)?(www\.)?meetup\.com!', $meta['source'] ) ) {
					$result = new WP_Error( 'not-meetup-url', __( 'Please provide a Meetup URL when importing from Meetup.', 'the-events-calendar' ) );
				}
				break;
			case 'url':
				if ( ! empty( $meta['count'] ) ) {
					$count = $meta['count'];
					if ( ! filter_var( $count, FILTER_VALIDATE_INT ) || intval( $count ) < 1 ) {
						$result = new WP_Error( 'invalid-url-count', __( 'Please provide a positive integer number for the count of events to import.', 'the-events-calendar' ) );
					}
					break;
				} else {
					$count = tribe_get_option( 'tribe_aggregator_default_url_import_events_count', 20 );
					if ( ! filter_var( $count, FILTER_VALIDATE_INT ) || intval( $count ) < 1 ) {
						$result = new WP_Error( 'invalid-url-count', __( 'Weird: the import count number stored in the option is not a positive integer.', 'the-events-calendar' ) );
						break;
					}
					$result['count'] = intval( $count );
				}
				break;
			default:
				if ( empty( $meta['url'] ) ) {
					$result = new WP_Error( 'missing-url', __( 'Please provide the URL that you wish to import.', 'the-events-calendar' ) );
				}
				break;
		}

		return $result;
	}
}
