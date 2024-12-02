<?php
/**
 * Handles the landing page of the onboarding wizard.
 *
 * @since 7.0.0
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */

namespace TEC\Events\Admin\Onboarding;

use TEC\Events\Telemetry\Telemetry;
use TEC\Common\StellarWP\Installer\Installer;
use TEC\Common\Admin\Abstract_Admin_Page;
use TEC\Common\Admin\Traits\Is_Events_Page;

/**
 * Class Landing_Page
 *
 * @since 7.0.0
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */
class Landing_Page extends Abstract_Admin_Page {
	use Is_Events_Page;

	/**
	 * The slug for the admin menu.
	 *
	 * @since 7.0.0
	 *
	 * @var string
	 */
	public static string $slug = 'first-time-setup';

	/**
	 * Whether the page has been dismissed.
	 *
	 * @since 7.0.0
	 *
	 * @var bool
	 */
	public static bool $is_dismissed = false;

	/**
	 * Whether the page has a sidebar.
	 *
	 * @since 7.0.0
	 *
	 * @var bool
	 */
	public static bool $has_sidebar = true;

	/**
	 * Whether the page has a footer.
	 *
	 * @since 7.0.0
	 *
	 * @var bool
	 */
	public static bool $has_footer = false;

	/**
	 * Get the admin page title.
	 *
	 * @since 7.0.0
	 *
	 * @return string The page title.
	 */
	public function get_the_page_title(): string {
		return esc_html__( 'TEC First Time Setup Page', 'the-events-calendar' );
	}

	/**
	 * Get the admin menu title.
	 *
	 * @since 7.0.0
	 *
	 * @return string The menu title.
	 */
	public function get_the_menu_title(): string {
		return esc_html__( 'First Time Setup', 'the-events-calendar' );
	}

	/**
	 * Add some wrapper classes to the admin page.
	 *
	 * @since 7.0.0
	 *
	 * @return string The class(es) string.
	 */
	public function content_wrapper_classes(): array {
		$classes   = parent::content_wrapper_classes();
		$classes[] = 'tec-events-admin__content';
		$classes[] = 'tec-events__landing-page-content';

		return $classes;
	}

	/**
	 * Render the landing page content.
	 *
	 * @since 7.0.0
	 */
	public function admin_page_main(): void {
		ob_start();
		?>
			<div class="tec-admin-page__content-section">
				<p>Main</p>
				<?php $this->tec_onboarding_wizard_button(); ?>
			</div>
			<div class="tec-admin-page__content-section">
				<p>Useful Resources</p>
			</div>
		<?php
		echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped,StellarWP.XSS.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Render the admin page sidebar.
	 *
	 * @since 7.0.0
	 */
	public function admin_page_sidebar(): void {
		ob_start();
		?>
			<p>Sidebar Content</p>
		<?php
		echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped,StellarWP.XSS.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Render the admin page footer.
	 *
	 * @since 7.0.0
	 */
	public function admin_page_footer(): void {
		// No op.
		return;
	}

	/**
	 * Get the initial data for the wizard.
	 *
	 * @since 7.0.0
	 *
	 * @return array<string, mixed> The initial data.
	 */
	public function get_initial_data(): array {
		$initial_data = [
			/* Wizard History */
			'begun'                 => (bool) get_transient( 'tec_onboarding_wizard_begun' ),
			'current_step'          => absint( get_transient( 'tec_onboarding_wizard_current_step' ) ),
			'finished'              => (bool) get_transient( 'tec_onboarding_wizard_finished' ),
			/* TEC settings */
			'tribeEnableViews'      => tribe_get_option( 'tribeEnableViews', [ 'list' ] ),
			'availableViews'        => tribe( Data::class)->get_available_views(),
			'defaultCurrencySymbol' => tribe_get_option( 'defaultCurrencySymbol', '$' ),
			'date_format'           => get_option( 'date_format', 'F j, Y' ),
			'opt-in-status'         => (bool) tribe( Telemetry::class )->get_reconciled_telemetry_opt_in(),
			/* WP Settings */
			'timezone_string'       => get_option( 'timezone_string', false ),
			'start_of_week'         => get_option( 'start_of_week', false ),
			/* ET install step */
			'event-tickets'         => Installer::get()->is_installed( 'event-tickets' ),
			/* nonces */
			'action_nonce'          => wp_create_nonce( API::NONCE_ACTION ),
			'_wpnonce'              => wp_create_nonce( 'wp_rest' ),
			/* Linked posts */
			'organizer'             => tribe( Data::class)->get_organizer_data(),
			'venue'                 => tribe( Data::class)->get_venue_data(),
			/* Data */
			'timezones'             => tribe( Data::class)->get_timezone_list(),
			'countries'             => tribe( Data::class)->get_country_list(),
			'currencies'            => tribe( Data::class)->get_currency_list(),
		];


		/**
		 * Filter the initial data.
		 *
		 * @since 7.0.0
		 *
		 * @param array    $initial_data The initial data.
		 * @param Controller $controller The controller object.
		 *
		 * @return array
		 */
		return (array) apply_filters( 'tribe_events_onboarding_wizard_initial_data', $initial_data, $this );
	}

	/**
	 * Render the onboarding wizard button.
	 *
	 * @since 7.0.0
	 */
	public function tec_onboarding_wizard_button(): void {
		$button = get_submit_button(
			esc_html__( 'Open Install Wizard (current)', 'the-events-calendar' ),
			'secondary tec-events-onboarding-wizard',
			'open',
			true,
			[
				'id'                     => 'tec-events-onboarding-wizard',
				'data-container-element' => 'tec-events-onboarding-wizard-target',
				'data-wizard-boot-data'  => wp_json_encode( $this->get_initial_data() ),
			]
		);

		$button .= sprintf(
			'<div class="wrap" id="tec-events-onboarding-wizard-target">%s</div>',
			esc_html__( 'Loading…', 'the-events-calendar' )
		);

		echo $button; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, StellarWP.XSS.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Register the assets for the landing page.
	 *
	 * @since 7.0.0
	 */
	public function register_assets() {
		$plugin     = tribe( 'tec.main' );
		$asset_file = $plugin->plugin_path . 'src/build/wizard/index.asset.php';

		// Danger, Will Robinson.
		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		$asset = include $asset_file;

		tribe_asset(
			$plugin,
			'tec-events-onboarding-wizard-script',
			plugins_url( 'src/build/wizard/index.js', $plugin->plugin_file ),
			$asset['dependencies'],
			'admin_enqueue_scripts',
			[
				'conditionals' => [ $this, 'is_on_page' ],
				'groups'       => [ 'tec-onboarding' ],
				'in_footer'    => true,
			]
		);

		tribe_asset(
			$plugin,
			'tec-events-onboarding-wizard-style',
			plugins_url( 'src/build/wizard/index.css', $plugin->plugin_file ),
			[ 'wp-components' ],
			'admin_enqueue_scripts',
			[
				'conditionals' => [ $this, 'is_on_page' ],
				'groups'       => [ 'tec-onboarding' ],
			]
		);
	}
}
