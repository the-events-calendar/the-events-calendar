<?php
/**
 * Controller for interfacing with TEC\Common\Onboarding.
 *
 * @since   TBD
 */

namespace TEC\Events\Admin\Onboarding;

use TEC\Common\Contracts\Provider\Controller as Controller_Contract;

/**
 * Class Controller
 *
 * @since   TBD
 * @package TEC\Events\Admin\Onboarding
 */
class Controller extends Controller_Contract {

	/**
	 * The slug for the admin menu.
	 *
	 * @since TBD
	 * @var string
	 */
	public static string $slug = 'onboarding-wizard';

	/**
	 * The slug for the parent page.
	 *
	 * @since TBD
	 */
	public static string $parent_slug = 'tribe_events_page_';

	/**
	 * The slug for the admin page
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public static string $page_slug = 'tribe_events_page_onboarding-wizard';

	/**
	 * Register the provider.
	 *
	 * @since   TBD
	 */
	public function do_register(): void {
		$this->add_filters();
		$this->add_actions();

		// Register the service provider itself on the container.
		$this->container->singleton( static::class, $this );
	}

	/**
	 * Unhooks actions and filters.
	 *
	 * @since   TBD
	 */
	public function unregister(): void {
		$this->remove_filters();
		$this->remove_actions();
	}

	/**
	 * Should only be active if we are in the admin.
	 *
	 * @since   TBD
	 * @return bool Only active during FS theme.
	 */
	public function is_active(): bool {
		return is_admin();
	}

	/**
	 * Defines wether the current page is the correct page.
	 *
	 * @since TBD
	 *
	 * @return boolean
	 */
	public function is_on_page(): bool {
		$admin_pages = tribe( 'admin.pages' );
		$admin_page  = $admin_pages->get_current_page();
		$page_slug   = $this->get_page_slug();

		return ! empty( $admin_page ) && $admin_page === $page_slug;
	}

	/**
	 * Get the page slug.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_page_slug(): string {
		if ( ! empty( static::$page_slug ) ) {
			return static::$page_slug;
		}

		static::$page_slug = static::$parent_slug . static::$slug;

		return static::$page_slug;
	}

	/**
	 * Add the filter hooks.
	 *
	 * @since   TBD
	 */
	public function add_filters() {}

	/**
	 * Add the action hooks.
	 *
	 * @since   TBD
	 */
	public function add_actions() {
		add_action( 'admin_menu', [ $this, 'settings_page' ] );
		add_action( 'admin_init', [ $this, 'enqueue_assets' ] );
	}

	/**
	 * Remove the filter hooks.
	 *
	 * @since   TBD
	 */
	public function remove_filters() {}

	/**
	 * Remove the action hooks.
	 *
	 * @since   TBD
	 */
	public function remove_actions() {
		remove_action( 'admin_menu', [ $this, 'settings_page' ] );
		remove_action( 'admin_init', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Settings page callback.
	 *
	 * @since   TBD
	 */
	public function settings_page() {
		add_submenu_page(
			'edit.php?post_type=tribe_events',
			__( 'TEC Onboarding Wizard', 'the-events-calendar' ),
			__( 'Onboarding Wizard', 'the-events-calendar' ),
			'manage_options',
			$this->get_page_slug(),
			[ $this, 'tec_onboarding_wizard_html' ]
		);
	}

	/**
	 * Render the onboarding wizard HTML.
	 *
	 * @since   TBD
	 */
	public function tec_onboarding_wizard_html() {
		printf(
			'<div class="wrap" id="tec-events-onboarding-wizard-target">%s</div>',
			esc_html__( 'Loading…', 'tec-events-onboarding-wizard' )
		);
	}

	/**
	 * Enqueue scripts for the onboarding wizard.
	 *
	 * @since   TBD
	 */
	public function enqueue_assets() {
		if ( ! $this->is_on_page() ) {
			return;
		}

		$plugin     = tribe( 'tec.main' );
		$asset_file = $plugin->plugin_path . 'src/build/wizard/index.asset.php';
		$asset      = include $asset_file;

		wp_enqueue_script(
			'tec-events-onboarding-wizard-script',
			plugins_url( 'src/build/wizard/index.js', dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ),
			$asset['dependencies'],
			$asset['version'],
			[
				'in_footer' => true,
			]
		);

		wp_enqueue_style(
			'tec-events-onboarding-wizard-style',
			plugins_url( 'src/build/wizard/index.css', dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ),
			[],
			$asset['version']
		);
	}
}
