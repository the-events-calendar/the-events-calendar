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
			'origin'       => $post_data['origin'],
			'type'         => empty( $data['import_type'] )      ? 'manual' : $data['import_type'],
			'frequency'    => empty( $data['import_frequency'] ) ? null     : $data['import_frequency'],
			'file'         => empty( $data['file'] )             ? null     : $data['file'],
			'keywords'     => empty( $data['keywords'] )         ? null     : $data['keywords'],
			'location'     => empty( $data['location'] )         ? null     : $data['location'],
			'start'        => empty( $data['start'] )            ? null     : $data['start'],
			'radius'       => empty( $data['radius'] )           ? null     : $data['radius'],
			'source'       => empty( $data['source'] )           ? null     : $data['source'],
			'content_type' => empty( $data['content_type'] )     ? null     : $data['content_type'],
		);

		// make sure there's data
		if ( empty( $meta['file'] ) && empty( $meta['source'] ) ) {
			if ( 'csv' === $meta['origin'] || 'ics' === $meta['origin'] ) {
				wp_send_json_error( array(
					'message' => __( 'Please provide the file that you wish to import.', 'the-events-calendar' ),
				) );
			} else {
				wp_send_json_error( array(
					'message' => __( 'Please provide the URL that you wish to import.', 'the-events-calendar' ),
				) );
			}
		}

		// validate that the URLs are accurate for the relevant origin
		if ( 'facebook' === $meta['origin'] && ! preg_match( '!(https?://)?(www\.)?facebook\.com!', $meta['source'] ) ) {
			wp_send_json_error( array(
				'message' => __( 'Please provide a Facebook URL when importing from Facebook.', 'the-events-calendar' ),
			) );
		} elseif ( 'meetup' === $meta['origin'] && ! preg_match( '!(https?://)?(www\.)?meetup\.com!', $meta['source'] ) ) {
			wp_send_json_error( array(
				'message' => __( 'Please provide a Meetup URL when importing from Meetup.', 'the-events-calendar' ),
			) );
		}

		return array(
			'record' => $record,
			'post_data' => $post_data,
			'meta' => $meta,
		);
	}
}
