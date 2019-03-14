<?php
/**
 * Facilitates smoother integration with the Freemius.
 *
 * @since TBD
 */
class Tribe__Events__Integrations__Freemius {
	/**
	 * Stores the instance for the Freemius
	 *
	 * @since  TBD
	 *
	 * @var Freemius
	 */
	private $instance;

	/**
	 * Stores the ID for the Freemius application
	 *
	 * @since  TBD
	 *
	 * @var string
	 */
	private $freemius_id = '3069';

	/**
	 * Performs setup for the Freemius integration singleton.
	 *
	 * @since  TBD
	 *
	 * @return void
	 */
	public function __construct() {
		$page = tribe_get_request_var( 'page' );
		$valid_page = [
			Tribe__Settings::$parent_slug,
			Tribe__App_Shop::MENU_SLUG,
			Tribe__Events__Aggregator__Page::$slug,
			'tribe-help',
		];

		if ( ! in_array( $page, $valid_page ) ) {
			return;
		}

		/**
		 * Allows third-party disabling of The Events Calendar integration
		 *
		 * @since  TBD
		 *
		 * @param  bool  $should_load
		 */
		$should_load = apply_filters( 'tribe_events_integrations_should_load_freemius', true );

		if ( ! $should_load ) {
			return;
		}

		$slug = 'the-events-calendar';

		$this->instance = tribe( 'freemius' )->initialize(
			$slug,
			$this->freemius_id,
			'pk_e32061abc28cfedf231f3e5c4e626',
			[
				'menu' => [
					'slug' => $page,
					'account' => true,
					'support' => false,
				],
				'is_premium' => false,
				'has_addons' => false,
				'has_paid_plans' => false,
			]
		);

		tribe_asset( Tribe__Events__Main::instance(), 'tribe-events-freemius', 'freemius.css', [], 'admin_enqueue_scripts' );

		$this->instance->add_filter( 'connect_message_on_update', [ $this, 'filter_connect_message_on_update' ], 10, 6 );
		$this->instance->add_filter( 'connect_message', [ $this, 'filter_connect_message' ], 10, 6 );
	}

	public function filter_connect_message_on_update(
		$message,
		$user_first_name,
		$product_title,
		$user_login,
		$site_link,
		$freemius_link
	) {
		return sprintf(
			__( 'Hey %1$s', 'my-text-domain' ) . ',<br>' .
			__( 'Please help us improve %2$s! If you opt-in, some data about your usage of %2$s will be sent to %5$s. If you skip this, that\'s okay! %2$s will still work just fine.', 'my-text-domain' ),
			$user_first_name,
			'<b>' . $product_title . '</b>',
			'<b>' . $user_login . '</b>',
			$site_link,
			$freemius_link
		);
	}

	/**
	 * Returns The Events Calendar instance of Freemius plugin
	 *
	 * @since  TBD
	 *
	 * @return Freemius
	 */
	public function get() {
		return $this->instance;
	}
}
