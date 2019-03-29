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
	}

	/**
	 * Filter the content for the Freemius Popup
	 *
	 * @since  TBD
	 *
	 * @param  string $message
	 * @param  string $user_first_name
	 * @param  string $product_title
	 * @param  string $user_login
	 * @param  string $site_link
	 * @param  string $freemius_link
	 *
	 * @return string
	 */
	public function filter_connect_message_on_update(
		$message,
		$user_first_name,
		$product_title,
		$user_login,
		$site_link,
		$freemius_link
	) {
		$plugin_name = 'The Events Calendar';
		$title = '<h3>' . sprintf( __( 'We hope you love %1$s', 'the-events-calendar' ), $plugin_name ) . '</h3>';
		$html = '';

		$html .= '<p>';
		$html .= sprintf(
			__( 'Hi, %1$s! This is an invitation to help %2$s community. If you opt-in, some data about your usage of %2$s will be shared with our teams (so they can work their butts off to improve). We will also share some helpful info on events management. WordPress, and our products from time to time.', 'the-events-calendar' ),
			$user_first_name,
			$plugin_name
		);
		$html .= '</p>';

		$html .= '<p>';
		$html .= sprintf(
			__( 'And if you skip this, that\'s okay! %1$s will still work just fine.', 'the-events-calendar' ),
			$plugin_name
		);
		$html .= '</p>';

		return $title . $html;
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
