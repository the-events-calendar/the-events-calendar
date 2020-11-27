<?php
/**
 * Class to initialize and add the required items in
 *
 * @since  4.5.12
 */
class Tribe__Events__Aggregator__Admin_Bar {

	/**
	 * Instance of this Class
	 *
	 * @since  4.5.12
	 *
	 * @var Tribe__Events__Admin__Bar__Admin_Bar
	 */
	protected static $instance;

	/**
	 * Plugin Constants
	 *
	 * @since  4.5.12
	 *
	 * @var Tribe__Events__Constants
	 */
	protected $constants;

	/**
	 * Singleton constructor for the class.
	 *
	 * @since  4.5.12
	 *
	 * @return Tribe__Events__Aggregator__Admin_Bar
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Tribe__Events__Aggregator__Admin_Bar constructor.
	 *
	 * @since  4.5.12
	 *
	 * @param  Tribe__Events__Constants  $constants A constants access proxy.
	 */
	public function __construct( Tribe__Events__Constants $constants = null ) {
		$this->constants = $constants ? $constants : new Tribe__Events__Constants();
	}

	/**
	 * Whether the Tribe Admin Bar is enabled or not.
	 *
	 * @since  4.5.12
	 *
	 * @return bool `false` if the `TRIBE_DISABLE_TOOLBAR_ITEMS` constant is `true` or the current screen is the network
	 *              admin one, `true` otherwise.
	 */
	public function is_enabled() {
		$disabled = isset( $this->constants['TRIBE_DISABLE_TOOLBAR_ITEMS'] ) && $this->constants['TRIBE_DISABLE_TOOLBAR_ITEMS'];

		return ( ! ( $disabled || is_network_admin() ) );
	}

	/**
	 * Adds menus, groups and nodes to the admin bar according the configuration.
	 *
	 * @since  4.5.12
	 *
	 * @param WP_Admin_Bar|null $wp_admin_bar
	 */
	public function init( WP_Admin_Bar $wp_admin_bar = null ) {
		if ( empty( $wp_admin_bar ) ) {
			global $wp_admin_bar;
		}

		if ( ! current_user_can( 'publish_tribe_events' ) ) {
			return;
		}

		$transient_key    = tribe( 'events-aggregator.main' )->KEY_CACHE_SERVICES;
		$service_response = get_transient( $transient_key );

		// Save HTTP request into a transient
		if ( false === $service_response ) {
			$service_response = Tribe__Events__Aggregator__Service::instance()->get_origins();
			set_transient( $transient_key, $service_response, DAY_IN_SECONDS );
		}

		$service_response = $this->remove_facebook_from_service_response( $service_response );

		$origins = [
			(object) [
				'id'   => 'csv',
				'name' => esc_attr__( 'CSV File', 'the-events-calendar' ),
			],
		];

		if ( ! is_array( $service_response ) || empty( $service_response['origin'] ) ) {
			return;
		}

		$origins = array_merge( $origins, $service_response['origin'] );

		foreach ( $origins as $origin ) {
			$url = Tribe__Events__Aggregator__Page::instance()->get_url( [ 'ea-origin' => $origin->id ] );

			$wp_admin_bar->add_menu(
				[
					'id'     => 'tribe-aggregator-import-' . $origin->id,
					'title'  => $origin->name,
					'href'   => esc_url( $url ),
					'parent' => 'tribe-events-import',
				]
			);
		}
	}

	/**
	 * Facebook is no longer supported in EA, so ensure that it doesn't show up in the list of available
	 * services.
	 *
	 * @since 4.6.24
	 *
	 * @param array $service_response The array of EA import origins.
	 * @return array The array of EA import origins with Facebook removed.
	 */
	protected function remove_facebook_from_service_response( $service_response ) {

		if ( ! is_array( $service_response ) || empty( $service_response['origin'] ) ) {
			return $service_response;
		}

		foreach ( $service_response['origin'] as $key => $origin ) {
			if ( 'facebook' === $origin->id ) {
				unset( $service_response['origin'][ $key ] );
			}
		}

		$service_response['origin'] = array_values( $service_response['origin'] );

		return $service_response;
	}
}
