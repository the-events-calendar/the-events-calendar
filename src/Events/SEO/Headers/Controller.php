<?php
/**
 * Manages the legacy view removal and messaging.
 *
 * @since   TBD
 *
 * @package TEC\Events\SEO
 */

namespace TEC\Events\SEO\Headers;

use TEC\Common\Contracts\Provider\Controller as Controller_Contract;
use Tribe__Settings_Manager;

/**
 * Class Headers Controller
 *
 * @since   TBD
 * @package TEC\Events\SEO
 */
class Controller extends Controller_Contract {

	/**
	 * Register actions.
	 *
	 * @since TBD
	 */
	public function do_register(): void {
		$this->container->singleton( static::class, $this );
		add_action( 'send_headers', [ $this, 'filter_headers' ] );
	}

	/**
	 * Unregister actions.
	 *
	 * @since TBD
	 */
	public function unregister(): void {
		remove_action( 'send_headers', [ $this, 'filter_headers' ] );
	}

	/**
	 * Filter the headers based on the query.
	 *
	 * @since TBD
	 */
	public function filter_headers() {
		global $wp_query;

		if ( ! isset( $wp_query->query['post_type'] ) || $wp_query->query['post_type'] !== 'tribe_events' || ! isset( $wp_query->query['eventDisplay'] ) || ! isset( $wp_query->query['eventDate'] ) ) {
			return;
		}

		$raw_options   = Tribe__Settings_Manager::get_options();
		$event_display = $wp_query->query['eventDisplay'];

		if ( 'day' === $event_display ) {
			$this->check_day_view( $wp_query, $raw_options );
		}
	}


	/**
	 * Check the conditions for the day view.
	 *
	 * @since TBD
	 *
	 * @param object $wp_query    The global WP_Query object.
	 * @param array  $raw_options The raw options from Tribe__Settings_Manager.
	 */
	private function check_day_view( object $wp_query, array $raw_options ) {
		if ( ! in_array( 'day', $raw_options['tribeEnableViews'] ) ) {
			$wp_query->set_404();

			return;
		}

		if ( strtotime( tribe_events_earliest_date( 'Y-m-d' ) ) > strtotime( $wp_query->query['eventDate'] ) ) {
			$wp_query->set_404();

			return;
		}

		if ( strtotime( tribe_events_latest_date( 'Y-m-d' ) ) < strtotime( $wp_query->query['eventDate'] ) ) {
			$wp_query->set_404();

			return;
		}
	}
}
