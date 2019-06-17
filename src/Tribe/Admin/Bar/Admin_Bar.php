<?php


class Tribe__Events__Admin__Bar__Admin_Bar {

	/**
	 * @var Tribe__Events__Admin__Bar__Admin_Bar
	 */
	protected static $instance;
	/**
	 * @var Tribe__Events__Constants
	 */
	protected $constants;
	/**
	 * @var Tribe__Events__Admin__Bar__Configurator_Interface
	 */
	protected $config;

	/**
	 * Singleton constructor for the class.
	 *
	 * @return Tribe__Events__Admin__Bar__Admin_Bar
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Tribe__Events__Admin__Bar__Admin_Bar constructor.
	 *
	 * @param Tribe__Events__Admin__Bar__Configurator_Interface $config    An admin bar configurator.
	 * @param Tribe__Events__Constants                          $constants A constants access proxy.
	 */
	public function __construct( Tribe__Events__Admin__Bar__Configurator_Interface $config = null, Tribe__Events__Constants $constants = null ) {
		$this->config    = $config ? $config : new Tribe__Events__Admin__Bar__Default_Configurator();
		$this->constants = $constants ? $constants : new Tribe__Events__Constants();
	}

	/**
	 * Whether the Tribe Admin Bar is enabled or not.
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
	 * @param WP_Admin_Bar|null $wp_admin_bar
	 */
	public function init( WP_Admin_Bar $wp_admin_bar = null ) {
		if ( empty( $wp_admin_bar ) ) {
			global /** @var WP_Admin_Bar $wp_admin_bar */
			$wp_admin_bar;
		}

		$this->config->configure( $wp_admin_bar );
	}
}
