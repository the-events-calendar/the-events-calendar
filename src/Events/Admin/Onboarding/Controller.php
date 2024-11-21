<?php
/**
 * Controller for interfacing with TEC\Common\Onboarding.
 *
 * @since 7.0.0
 */

namespace TEC\Events\Admin\Onboarding;

use TEC\Common\Contracts\Provider\Controller as Controller_Contract;
use TEC\Events\Admin\Onboarding\Wizard;
use TEC\Events\Admin\Onboarding\Steps\Step;
use TEC\Events\Admin\Onboarding\Data;
/**
 * Class Controller
 *
 * @since 7.0.0
 * @package TEC\Events\Admin\Onboarding
 */
class Controller extends Controller_Contract {

	/**
	 * The slug for the admin menu.
	 *
	 * @since 7.0.0
	 *
	 * @var string
	 */
	public static string $slug = 'onboarding-wizard';

	/**
	 * The slug for the parent page.
	 *
	 * @since 7.0.0
	 */
	public static string $parent_slug = 'tribe_events_page_';

	/**
	 * The slug for the admin page
	 *
	 * @since 7.0.0
	 *
	 * @var string
	 */
	public static string $page_slug = 'tribe_events_page_onboarding-wizard';

	/**
	 * The step handlers.
	 *
	 * @since 7.0.0
	 *
	 * @var array
	 */
	protected $step_0;
	protected $step_1;
	protected $step_2;
	protected $step_3;
	protected $step_4;
	protected $step_5;

	/**
	 * Register the provider.
	 *
	 * @since 7.0.0
	 */
	public function do_register(): void {
		$this->create_steps();
		$this->add_filters();
		$this->add_actions();

		// Register the service provider itself on the container.
		$this->container->singleton( static::class, $this );
	}

	/**
	 * Unhooks actions and filters.
	 *
	 * @since 7.0.0
	 */
	public function unregister(): void {
		$this->remove_filters();
		$this->remove_actions();
	}

	/**
	 * Should only be active if we are in the admin.
	 *
	 * @since 7.0.0
	 */
	public function is_active(): bool {
		return true;
	}

	/**
	 * Defines wether the current page is the correct page.
	 *
	 * @since 7.0.0
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
	 * @since 7.0.0
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
	 * @since 7.0.0
	 */
	public function add_filters(): void {
		// no op
	}

	/**
	 * Add the action hooks.
	 *
	 * @since 7.0.0
	 */
	public function add_actions(): void {
		add_action( 'admin_menu', [ $this, 'settings_page' ] );
		add_action( 'admin_init', [ $this, 'enqueue_assets' ] );
		add_action( 'rest_api_init', [ $this, 'register_rest_endpoints' ] );
	}

	/**
	 * Remove the filter hooks.
	 *
	 * @since 7.0.0
	 */
	public function remove_filters(): void {
		// Remove the step handlers.
		remove_all_filters( 'tec_events_onboarding_wizard_handle' );
	}

	/**
	 * Remove the action hooks.
	 *
	 * @since 7.0.0
	 */
	public function remove_actions(): void {
		remove_action( 'admin_menu', [ $this, 'settings_page' ] );
		remove_action( 'admin_init', [ $this, 'enqueue_scripts' ] );
		remove_action( 'rest_api_init', [ $this, 'register_rest_endpoints' ] );
	}

	/**
	 * Create the step objects.
	 *
	 * @since 7.0.0
	 */
	public function create_steps() {
		$this->step_0 = Step::create( 0 );
		$this->step_1 = Step::create( 1 );
		$this->step_2 = Step::create( 2 );
		$this->step_3 = Step::create( 3 );
		$this->step_4 = Step::create( 4 );
		$this->step_5 = Step::create( 5 );
	}

	public function get_button_data() {
		$data = [
			'action_nonce'          => wp_create_nonce( Wizard::NONCE_ACTION ),
			'_wpnonce'              => wp_create_nonce( 'wp_rest' ),
			'timezones'             => Data::get_timezone_list(),
			'countries'             => Data::get_country_list(),
		];

		$data = apply_filters( 'tribe_events_onboarding_wizard_initial_data', $data, $this );

		return $data;
	}

	/**
	 * Settings page callback.
	 *
	 * @since 7.0.0
	 */
	public function settings_page(): void {
		add_submenu_page(
			'edit.php?post_type=tribe_events',
			__( 'TEC Onboarding Wizard', 'the-events-calendar' ),
			__( 'Onboarding Wizard', 'the-events-calendar' ),
			'manage_options',
			$this->get_page_slug(),
			[ $this, 'tec_onboarding_wizard_button' ]
		);
	}

	/**
	 * Enqueue scripts for the onboarding wizard.
	 *
	 * @since 7.0.0
	 */
	public function enqueue_assets(): void {
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
			[ 'wp-components' ],
			$asset['version']
		);
	}

	/**
	 * Registers the REST endpoints that will be used to return the Views HTML.
	 *
	 * @since 7.0.0
	 */
	public function register_rest_endpoints(): void {
		$this->container->make( Wizard::class )->register();
	}

	/**
	 * Render the onboarding wizard button.
	 *
	 * @since 7.0.0
	 */
	public function tec_onboarding_wizard_button(): void {
		// phpcs:disable

		$first_boot_data = $this->get_button_data();

		$button = get_submit_button(
			esc_html__( 'Open Install Wizard (current)', 'the-events-calendar' ),
			'secondary tec-events-onboarding-wizard',
			'open',
			true,
			[
				'id'                     => 'tec-events-onboarding-wizard',
				'data-container-element' => 'tec-events-onboarding-wizard-target',
				'data-wizard-boot-data'  => wp_json_encode( $first_boot_data ),
			]
		);

		$button .= sprintf(
			'<div class="wrap" id="tec-events-onboarding-wizard-target">%s</div>',
			esc_html__( 'Loading…', 'the-events-calendar' )
		);

		echo $button;

		// phpcs:enable
	}

	/**
	 * Get the organizer data.
	 * Looks for a single existing organizer and returns the data.
	 *
	 * @since 7.0.0
	 */
	public function get_organizer_data(): array {
		$organizer_id = tribe( 'events.organizer-repository' )->per_page( - 1 )->fields( 'ids' )->first();

		if ( empty( $organizer_id ) ) {
			return [];
		}

		return [
			'id'      => $organizer_id,
			'name'    => get_the_title( $organizer_id ),
			'email'   => get_post_meta( $organizer_id, '_OrganizerEmail', true ),
			'phone'   => get_post_meta( $organizer_id, '_OrganizerPhone', true ),
			'website' => get_post_meta( $organizer_id, '_OrganizerWebsite', true ),
		];
	}

	/**
	 * Get the venue data.
	 * Looks for a single existing venue and returns the data.
	 *
	 * @since 7.0.0
	 */
	public function get_venue_data(): array {
		$venue_id = tribe( 'events.venue-repository' )->per_page( - 1 )->fields( 'ids' )->first();

		if ( empty( $venue_id ) ) {
			return [];
		}

		return [
			'id'      => $venue_id,
			'name'    => get_the_title( $venue_id ),
			'address' => get_post_meta( $venue_id, '_VenueAddress', true ),
			'city'    => get_post_meta( $venue_id, '_VenueCity', true ),
			'country' => get_post_meta( $venue_id, '_VenueCountry', true ),
			'phone'   => get_post_meta( $venue_id, '_VenuePhone', true ),
			'state'   => get_post_meta( $venue_id, '_VenueState', true ),
			'website' => get_post_meta( $venue_id, '_VenueWebsite', true ),
			'zip'     => get_post_meta( $venue_id, '_VenueZip', true ),
		];
	}

	/**
	 * Get the available views.
	 *
	 * @since 7.0.0
	 */
	public function get_available_views(): array {
		$view_manager    = tribe( \Tribe\Events\Views\V2\Manager::class );
		$available_views = array_keys( $view_manager->get_registered_views() );
		$remove          = [
			'all',
			'latest-past',
			'organizer',
			'reflector',
			'venue',
			'widget-countdown',
			'widget-events-list',
			'widget-featured-venue',
			'widget-week',
		];

		$cleaned_views = array_flip( array_diff_key( array_flip( $available_views ), array_flip( $remove ) ) );

		return array_values( $cleaned_views );
	}
}
