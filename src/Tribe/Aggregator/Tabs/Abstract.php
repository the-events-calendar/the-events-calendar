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

		// If we are dealing with Other URL made
		if ( 'url' === $post_data['origin'] ) {
			$new_origin = tribe( 'events-aggregator.settings' )->match_source_origin( $data['source'] );

			// If we found a valid new origin we overwrite
			if ( false !== $new_origin ) {
				$post_data['origin'] = $new_origin;
			}
		}

		$record = Tribe__Events__Aggregator__Records::instance()->get_by_origin( $post_data['origin'] );

		$meta = array(
			'origin'        => $post_data['origin'],
			'type'          => empty( $data['import_type'] ) ? 'manual' : $data['import_type'],
			'frequency'     => empty( $data['import_frequency'] ) ? null : $data['import_frequency'],
			'file'          => empty( $data['file'] ) ? null : $data['file'],
			'keywords'      => ! isset( $data['keywords'] ) ? null : trim( $data['keywords'] ),
			'location'      => ! isset( $data['location'] ) ? null : trim( $data['location'] ),
			'start'         => ! isset( $data['start'] ) ? null : trim( $data['start'] ),
			'end'           => ! isset( $data['end'] ) ? null : trim( $data['end'] ),
			'radius'        => empty( $data['radius'] ) ? null : $data['radius'],
			'source'        => empty( $data['source'] ) ? null : $data['source'],
			'source_type'   => empty( $data['source_type'] ) ? null : $data['source_type'],
			'content_type'  => empty( $data['content_type'] ) ? null : $data['content_type'],
			'schedule_day'  => empty( $data['schedule_day'] ) ? null : $data['schedule_day'],
			'schedule_time' => empty( $data['schedule_time'] ) ? null : $data['schedule_time'],
		);

		// Special source types can override source (Eventbrite current profile URL)
		if ( ! empty( $meta['source_type'] ) ) {
			$meta['source'] = $meta['source_type'];
		}

		// Only apply this verification when dealing with Creating new items
		if ( ! empty( $post_data['action'] ) && 'new' === $post_data['action'] ) {
			$hash = array_filter( $meta );

			// remove non-needed data from the Hash of the Record
			unset( $hash['schedule_day'], $hash['schedule_time'] );
			ksort( $hash );
			$hash = maybe_serialize( $hash );
			$hash = md5( $hash );

			$matches = Tribe__Events__Aggregator__Records::instance()->query( array(
				'post_status' => Tribe__Events__Aggregator__Records::instance()->get_status( 'schedule' )->name,
				'meta_query' => array(
					Tribe__Events__Aggregator__Records::instance()->prefix_meta( 'source' ) => $meta['source'],
				),
				'fields' => 'ids',
			) );

			foreach ( $matches->posts as $post_id ) {
				$matching_hash = Tribe__Events__Aggregator__Records::instance()->get_by_post_id( $post_id )->get_data_hash();

				if ( $matching_hash == $hash ) {
					$url = get_edit_post_link( $post_id );
					$anchor = '<a href="' . esc_url( $url ) . '">' . esc_attr__( 'click here to edit it', 'the-events-calendar' ) .  '</a>';
					$message = sprintf( __( 'A record already exists with these settings, %1$s.', 'the-events-calendar' ), $anchor );
					wp_send_json_error( array( 'message' => $message ) );
				}
			}
		}

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
			case 'eventbrite':
				if ( empty( $meta['source'] ) || ! preg_match( '/' . Tribe__Events__Aggregator__Record__Eventbrite::get_source_regexp() . '/', $meta['source'] ) ) {
					$result = new WP_Error( 'not-eventbrite-url', __( 'Please provide a Eventbrite URL when importing from Eventbrite.', 'the-events-calendar' ) );
				}
				break;
			case 'meetup':
				if ( empty( $meta['source'] ) || ! preg_match( '/' . Tribe__Events__Aggregator__Record__Meetup::get_source_regexp() . '/', $meta['source'] ) ) {
					$result = new WP_Error( 'not-meetup-url', __( 'Please provide a Meetup URL when importing from Meetup.', 'the-events-calendar' ) );
				}
				break;
			case 'url':
				$now = time();
				$range = tribe_get_option( 'tribe_aggregator_default_url_import_range', 30 * DAY_IN_SECONDS );
				$start = ! empty( $meta['start'] ) ? $this->to_timestamp( $meta['start'], $now ) : $now;
				$end = ! empty( $meta['end'] ) ? $this->to_timestamp( $meta['end'], $start + $range ) : $start + $range;

				/**
				 * Filters the URL import range cap.
				 *
				 * @param int   $max_range The duration in seconds of the cap.
				 * @param array $meta      The meta for this import request.
				 */
				$max_range = apply_filters( 'tribe_aggregator_url_import_range_cap', 3 * 30 * DAY_IN_SECONDS, $meta );

				// but soft-cap the range to start + cap at the most
				$end = min( $end, $start + $max_range );

				/**
				 * Filters the URL import range start date after the cap has been applied.
				 *
				 * @param int   $start The start date UNIX timestamp.
				 * @param int   $end   The end date UNIX timestamp.
				 * @param array $meta  The meta for this import request.
				 */
				$start = apply_filters( 'tribe_aggregator_url_import_range_start', $start, $end, $meta );

				/**
				 * Filters the URL import range end date after the cap has been applied.
				 *
				 * @param int   $end   The end date UNIX timestamp.
				 * @param int   $start The start date UNIX timestamp.
				 * @param array $meta  The meta for this import request.
				 */
				$end = apply_filters( 'tribe_aggregator_url_import_range_end', $end, $start, $meta );

				$result['start'] = $start;
				$result['end'] = $end;

				break;
			default:
				if ( empty( $meta['source'] ) ) {
					$result = new WP_Error( 'missing-url', __( 'Please provide the URL that you wish to import.', 'the-events-calendar' ) );
				}
				break;
		}

		return $result;
	}

	/**
	 * Casts a string or int to a timestamp.
	 *
	 * @param int|string $time
	 * @param int        $default The default time that should be used if the conversion of `$time` fails
	 *
	 * @return int
	 */
	protected function to_timestamp( $time, $default = '' ) {
		$time = Tribe__Date_Utils::is_timestamp( $time ) ? $time : strtotime( $time );

		return false !== $time ? $time : $default;
	}
}
