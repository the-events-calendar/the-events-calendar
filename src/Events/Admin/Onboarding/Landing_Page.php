<?php
/**
 * Handles the landing page of the onboarding wizard.
 *
 * @since 6.8.4
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */

namespace TEC\Events\Admin\Onboarding;

use TEC\Events\Telemetry\Telemetry;
use TEC\Common\StellarWP\Installer\Installer;
use TEC\Common\Admin\Abstract_Admin_Page;
use TEC\Common\Admin\Traits\Is_Events_Page;
use TEC\Common\Asset;
use Tribe__Events__Main as TEC;

/**
 * Class Landing_Page
 *
 * @since 6.8.4
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */
class Landing_Page extends Abstract_Admin_Page {
	use Is_Events_Page;

	/**
	 * The action to dismiss the onboarding page.
	 *
	 * @since 6.8.4
	 *
	 * @var string
	 */
	const DISMISS_PAGE_ACTION = 'tec_dismiss_onboarding_page';

	/**
	 * The option to dismiss the onboarding page.
	 *
	 * @since 6.8.4
	 *
	 * @var string
	 */
	const DISMISS_PAGE_OPTION = 'tec_events_onboarding_page_dismissed';

	/**
	 * The slug for the admin menu.
	 *
	 * @since 6.8.4
	 *
	 * @var string
	 */
	public static string $slug = 'first-time-setup';

	/**
	 * Whether the page has been dismissed.
	 *
	 * @since 6.8.4
	 *
	 * @var bool
	 */
	public static bool $is_dismissed = false;

	/**
	 * Whether the page has a header.
	 *
	 * @since 6.8.4
	 *
	 * @var bool
	 */
	public static bool $has_header = true;

	/**
	 * Whether the page has a sidebar.
	 *
	 * @since 6.8.4
	 *
	 * @var bool
	 */
	public static bool $has_sidebar = true;

	/**
	 * Whether the page has a footer.
	 *
	 * @since 6.8.4
	 *
	 * @var bool
	 */
	public static bool $has_footer = false;

	/**
	 * The position of the submenu in the menu.
	 *
	 * @since 6.8.4
	 * @since 6.11.0 Changed menu position.
	 *
	 * @var int
	 */
	public int $menu_position = 21;

	/**
	 * Get the admin page title.
	 *
	 * @since 6.8.4
	 * @since 6.11.0 Changed page title.
	 *
	 * @return string The page title.
	 */
	public function get_the_page_title(): string {
		return esc_html__( 'TEC Setup Guide', 'the-events-calendar' );
	}

	/**
	 * Get the admin menu title.
	 *
	 * @since 6.8.4
	 * @since 6.11.0 Changed menu title.
	 *
	 * @return string The menu title.
	 */
	public function get_the_menu_title(): string {
		return esc_html__( 'Setup Guide', 'the-events-calendar' );
	}

	/**
	 * Add some wrapper classes to the admin page.
	 *
	 * @since 6.8.4
	 *
	 * @return array The class(es) array.
	 */
	public function content_wrapper_classes(): array {
		$classes   = parent::content_classes();
		$classes[] = 'tec-events-admin__content';
		$classes[] = 'tec-events__landing-page-content';

		return $classes;
	}

	/**
	 * Handle the dismissal of the onboarding page.
	 *
	 * @since 6.8.4
	 *
	 * @return void
	 */
	public function handle_onboarding_page_dismiss(): void {
		if ( ! current_user_can( $this->required_capability() ) ) {
			return;
		}

		tribe_update_option( self::DISMISS_PAGE_OPTION, true );

		wp_safe_redirect( add_query_arg( [ 'post_type' => TEC::POSTTYPE ], admin_url( 'edit.php' ) ) );
		exit;
	}

	/**
	 * Render the landing page content.
	 *
	 * @since 6.8.4
	 *
	 * @return void
	 */
	public function admin_page_main_content(): void {
		$this->admin_content_checklist_section();

		$this->admin_content_resources_section();


		// Only show the wizard if we're doing a new installation.
		$this->tec_onboarding_wizard_target();

		// Stop redirecting if the user has visited the Guided Setup page.
		tribe_update_option( 'tec_onboarding_wizard_visited_guided_setup', true );
	}

	/**
	 * Render the checklist section.
	 *
	 * @since 6.8.4
	 * @since 6.11.1 Fixed a typo.
	 *
	 * @return void
	 */
	public function admin_content_checklist_section(): void {
		$settings_url   = 'edit.php?page=tec-events-settings&post_type=tribe_events';
		$action_url     = add_query_arg( [ 'action' => self::DISMISS_PAGE_ACTION ], admin_url( '/admin-post.php' ) );
		$data           = tribe( Data::class );
		$completed_tabs = array_flip( (array) $data->get_wizard_setting( 'completed_tabs', [] ) );
		$et_installed   = Installer::get()->is_installed( 'event-tickets' );
		$et_activated   = Installer::get()->is_active( 'event-tickets' );
		$organizer_data = $data->get_organizer_data();
		$venue_data     = $data->get_venue_data();
		$has_event      = $data->has_events();
		$condition      = [
			isset( $completed_tabs[1] ) || ! empty( tribe_get_option( 'tribeEnableViews' ) ),
			isset( $completed_tabs[2] ) || ! empty( tribe_get_option( 'defaultCurrencyCode' ) ),
			isset( $completed_tabs[3] ) || ! empty( tribe_get_option( 'dateWithYearFormat' ) ),
			isset( $completed_tabs[4] ) || ! empty( $organizer_data ),
			isset( $completed_tabs[5] ) || ! empty( $venue_data ),
		];
		$count_complete = count( array_filter( $condition ) );
		?>
			<div class="tec-admin-page__content-section">
				<div class="tec-admin-page__content-section-header">
					<h2 class="tec-admin-page__content-header"><?php esc_html_e( 'First-time setup', 'the-events-calendar' ); ?></h2>
					<a class="tec-dismiss-admin-page" href="<?php echo esc_url( $action_url ); ?>"><?php esc_html_e( 'Dismiss this screen', 'the-events-calendar' ); ?></a>
				</div>
				<div class="tec-admin-page__content-section-subheader"><?php echo esc_html( $count_complete ) . '/' . esc_html( count( $condition ) ) . ' ' . esc_html__( 'steps completed', 'the-events-calendar' ); ?></div>
				<ul class="tec-admin-page__content-step-list">
					<li
						id="tec-events-onboarding-wizard-views-item"
						<?php
						tec_classes(
							[
								'step-list__item' => true,
								'tec-events-onboarding-step-1' => true,
								'tec-admin-page__onboarding-step--completed' => $condition[0],
							]
						);
						?>
					>
						<div class="step-list__item-left">
							<span class="step-list__item-icon" role="presentation"></span>
								<?php esc_html_e( 'Calendar Views', 'the-events-calendar' ); ?>
						</div>
						<div class="step-list__item-right">
							<a href="<?php echo esc_url( admin_url( "{$settings_url}&tab=display#tribe-field-tribeEnableViews" ) ); ?>" class="tec-admin-page__link">
								<?php esc_html_e( 'Edit your calendar views', 'the-events-calendar' ); ?>
							</a>
						</div>
					</li>
					<li
						id="tec-events-onboarding-wizard-currency-item"
						<?php
						tec_classes(
							[
								'step-list__item' => true,
								'tec-events-onboarding-step-2' => true,
								'tec-admin-page__onboarding-step--completed' => $condition[1],
							]
						);
						?>
					>
						<div class="step-list__item-left">
							<span class="step-list__item-icon" role="presentation"></span>
							<?php esc_html_e( 'Currency', 'the-events-calendar' ); ?>
						</div>
						<div class="step-list__item-right">
							<a href="<?php echo esc_url( admin_url( "{$settings_url}&tab=display-currency-tab" ) ); ?>" class="tec-admin-page__link">
								<?php esc_html_e( 'Edit currency', 'the-events-calendar' ); ?>
							</a>
						</div>
					</li>
					<li
						id="tec-events-onboarding-wizard-date-item"
						<?php
						tec_classes(
							[
								'step-list__item' => true,
								'tec-events-onboarding-step-2' => true,
								'tec-admin-page__onboarding-step--completed' => $condition[2],
							]
						);
						?>
					>
						<div class="step-list__item-left">
							<span class="step-list__item-icon" role="presentation"></span>
							<?php esc_html_e( 'Date format', 'the-events-calendar' ); ?>
						</div>
						<div class="step-list__item-right">
							<a href="<?php echo esc_url( admin_url( "{$settings_url}&tab=display-date-time-tab" ) ); ?>" class="tec-admin-page__link">
								<?php esc_html_e( 'Edit date format', 'the-events-calendar' ); ?>
							</a>
						</div>
					</li>
					<li
						id="tec-events-onboarding-wizard-organizer-item"
						<?php
						tec_classes(
							[
								'step-list__item' => true,
								'tec-events-onboarding-step-3' => true,
								'tec-admin-page__onboarding-step--completed' => $condition[3],
							]
						);
						?>
					>
						<div class="step-list__item-left">
							<span class="step-list__item-icon" role="presentation"></span>
							<?php esc_html_e( 'Event Organizer', 'the-events-calendar' ); ?>
						</div>
						<div class="step-list__item-right">
							<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=tribe_organizer' ) ); ?>" class="tec-admin-page__link">
								<?php esc_html_e( 'Add Organizer', 'the-events-calendar' ); ?>
							</a>
						</div>
					</li>
					<li
						id="tec-events-onboarding-wizard-venue-item"
						<?php
						tec_classes(
							[
								'step-list__item' => true,
								'tec-events-onboarding-step-4' => true,
								'tec-admin-page__onboarding-step--completed' => $condition[4],
							]
						);
						?>
					>
						<div class="step-list__item-left">
							<span class="step-list__item-icon" role="presentation"></span>
							<?php esc_html_e( 'Event Venue', 'the-events-calendar' ); ?>
						</div>
						<div class="step-list__item-right">
							<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=tribe_venue' ) ); ?>" class="tec-admin-page__link">
								<?php esc_html_e( 'Add Venue', 'the-events-calendar' ); ?>
							</a>
						</div>
					</li>
				</ul>
				<div class="tec-admin-page__content-section-mid">
					<h2 class="tec-admin-page__content-header">
						<?php esc_html_e( 'Create an event', 'the-events-calendar' ); ?>
					</h2>
					<ul class="tec-admin-page__content-step-list">
						<li
							id="tec-events-onboarding-wizard-event-item"
							<?php
							tec_classes(
								[
									'step-list__item' => true,
									'tec-admin-page__onboarding-step--completed' => $has_event,
								]
							);
							?>
						>
							<div class="step-list__item-left">
								<span class="step-list__item-icon" role="presentation"></span>
								<?php esc_html_e( 'Ready to publish your first event?', 'the-events-calendar' ); ?>
						</div>
							<div class="step-list__item-right">
								<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=tribe_events' ) ); ?>" class="tec-admin-page__link">
									<?php esc_html_e( 'Add new event', 'the-events-calendar' ); ?>
								</a>
							</div>
						</li>
						<li id="tec-events-onboarding-wizard-import-item" class="step-list__item">
							<div class="step-list__item-left">
								<?php esc_html_e( 'Do you already have events you want to import?', 'the-events-calendar' ); ?>
							</div>
							<div class="step-list__item-right">
								<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=tribe_events&page=aggregator' ) ); ?>" class="tec-admin-page__link">
									<?php esc_html_e( 'Import events', 'the-events-calendar' ); ?>
								</a>
							</div>
						</li>
					</ul>
				</div>
				<div id="tec-events-onboarding-wizard-tickets">
					<h2 class="tec-admin-page__content-header">
						<?php esc_html_e( 'Event Tickets', 'the-events-calendar' ); ?>
					</h2>
					<h3 class="tec-admin-page__content-subheader">
						<?php esc_html_e( 'Are you planning to sell tickets to your events?', 'the-events-calendar' ); ?>
					</h3>
					<ul class="tec-admin-page__content-step-list">
						<li
							id="tec-events-onboarding-wizard-tickets-item"
							<?php
							tec_classes(
								[
									'step-list__item' => true,
									'tec-events-onboarding-step-5' => true,
									'tec-admin-page__onboarding-step--completed' => ( isset( $completed_tabs[5] ) || ( $et_installed && $et_activated ) ),
								]
							);
							?>
						>
							<div class="step-list__item-left">
								<span class="step-list__item-icon" role="presentation"></span>
								<?php esc_html_e( 'Install Event Tickets', 'the-events-calendar' ); ?>
							</div>
							<?php if ( ! $et_installed || ! $et_activated ) : ?>
							<div class="step-list__item-right">
								<?php
								Installer::get()->render_plugin_button(
									'event-tickets',
									$et_installed ? 'activate' : 'install',
									$et_installed ? __( 'Activate Event Tickets', 'the-events-calendar' ) : __( 'Install Event Tickets', 'the-events-calendar' ),
									admin_url( 'edit.php?post_type=tribe_events&page=first-time-setup' )
								);
								?>
							</div>
							<?php endif; ?>
						</li>
					</ul>
				</div>
			</div>
		<?php
	}

	/**
	 * Render the resources section.
	 *
	 * @since 6.9.0
	 *
	 * @return void
	 */
	public function admin_content_resources_section(): void {
		$chatbot_link   = admin_url( 'edit.php?post_type=tribe_events&page=tec-events-help-hub' );
		$guide_link     = 'https://theeventscalendar.com/knowledgebase/guide/the-events-calendar/';
		$customize_link = 'https://theeventscalendar.com/knowledgebase/guide/customization/';
		?>
		<div class="tec-admin-page__content-section">
			<h2 class="tec-admin-page__content-header">
				<?php esc_html_e( 'Useful Resources', 'the-events-calendar' ); ?>
			</h2>
			<ul>
				<li>
					<span class="tec-admin-page__icon tec-admin-page__icon--stars" role="presentation"></span>
					<a href="<?php echo esc_url( $chatbot_link ); ?>" class="tec-admin-page__link">
						<?php esc_html_e( 'Ask our AI Chatbot anything', 'the-events-calendar' ); ?>
					</a>
				</li>
				<li>
					<span class="tec-admin-page__icon tec-admin-page__icon--book" role="presentation"></span>
					<span class="tec-admin-page__link--external">
						<a href="<?php echo esc_url( $guide_link ); ?>" class="tec-admin-page__link" target="_blank" rel="nofollow noopener">
							<?php esc_html_e( 'The Events Calendar guide', 'the-events-calendar' ); ?>
						</a>
					</span>
				</li>
				<li>
					<span class="tec-admin-page__icon tec-admin-page__icon--customize" role="presentation"></span>
					<span class="tec-admin-page__link--external">
						<a href="<?php echo esc_url( $customize_link ); ?>" class="tec-admin-page__link" target="_blank" rel="nofollow noopener">
							<?php esc_html_e( 'Customize styles and templates', 'the-events-calendar' ); ?>
						</a>
					</span>
				</li>
			</ul>
		</div>
		<?php
	}

	/**
	 * Render the admin page sidebar.
	 *
	 * @since 6.8.4
	 *
	 * @return void
	 */
	public function admin_page_sidebar_content(): void {
		?>
			<section class="tec-admin-page__sidebar-section has-icon">
				<span class="tec-admin-page__icon tec-admin-page__sidebar-icon tec-admin-page__icon--stars" role="presentation"></span>
				<div>
					<h3 class="tec-admin-page__sidebar-header"><?php esc_html_e( 'Our AI Chatbot is here to help you', 'the-events-calendar' ); ?></h3>
					<p><?php esc_html_e( 'You have questions? The TEC Chatbot has the answers.', 'the-events-calendar' ); ?></p>
					<p><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=tribe_events&page=tec-events-help-hub' ) ); ?>" class="tec-admin-page__link"><?php esc_html_e( 'Talk to TEC Chatbot', 'the-events-calendar' ); ?></a></p>
				</div>
			</section>
			<section class="tec-admin-page__sidebar-section has-icon">
				<span class="tec-admin-page__icon tec-admin-page__sidebar-icon tec-admin-page__icon--chat" role="presentation"></span>
				<div>
					<h2 class="tec-admin-page__sidebar-header"><?php esc_html_e( 'Get priority live support', 'the-events-calendar' ); ?></h2>
					<p><?php esc_html_e( 'You can get live support from The Events Calendar team if you have an active license for one of our products.', 'the-events-calendar' ); ?></p>
					<p><span class="tec-admin-page__link--external"><a href="https://theeventscalendar.com/knowledgebase/priority-support-through-the-tec-support-hub" target="_blank" rel="nofollow noopener" class="tec-admin-page__link"><?php esc_html_e( 'Learn how to get an active license', 'the-events-calendar' ); ?></a></span></p>
				</div>
			</section>
		<?php
	}

	/**
	 * Render the admin page footer.
	 *
	 * @since 6.8.4
	 *
	 * @return void
	 */
	public function admin_page_footer_content(): void {
		// no op.
	}

	/**
	 * Get the initial data for the wizard.
	 *
	 * @since 6.8.4
	 *
	 * @return array<string, mixed> The initial data.
	 */
	public function get_initial_data(): array {
		$data         = tribe( Data::class );
		$initial_data = [
			/* Wizard History */
			'begun'                   => (bool) $data->get_wizard_setting( 'begun', false ),
			'currentTab'              => absint( $data->get_wizard_setting( 'current_tab', 0 ) ),
			'finished'                => (bool) $data->get_wizard_setting( 'finished', false ),
			'completedTabs'           => (array) $data->get_wizard_setting( 'completed_tabs', [] ),
			'skippedTabs'             => (array) $data->get_wizard_setting( 'skipped_tabs', [] ),
			/* TEC settings */
			'tribeEnableViews'        => tribe_get_option( 'tribeEnableViews', [ 'list' ] ),
			'availableViews'          => tribe( Data::class )->get_available_views(),
			'currency'                => strtolower( tribe_get_option( 'defaultCurrencyCode', 'usd' ) ),
			'date_format'             => get_option( 'date_format', 'F j, Y' ),
			'optin'                   => (bool) tribe( Telemetry::class )->get_reconciled_telemetry_opt_in(),
			/* WP Settings */
			'timezone_string'         => get_option( 'timezone_string', false ),
			'start_of_week'           => get_option( 'start_of_week', false ),
			/* ET install step */
			'event-tickets-installed' => Installer::get()->is_installed( 'event-tickets' ),
			'event-tickets-active'    => Installer::get()->is_active( 'event-tickets' ),
			/* nonces */
			'action_nonce'            => wp_create_nonce( API::NONCE_ACTION ),
			'_wpnonce'                => wp_create_nonce( 'wp_rest' ),
			/* Linked posts */
			'organizer'               => tribe( Data::class )->get_organizer_data(),
			'venue'                   => tribe( Data::class )->get_venue_data(),
			/* Data */
			'timezones'               => tribe( Data::class )->get_timezone_list(),
			'countries'               => tribe( Data::class )->get_country_list(),
			'currencies'              => tribe( Data::class )->get_currency_list(),
		];


		/**
		 * Filter the initial data.
		 *
		 * @since 6.8.4
		 *
		 * @param array      $initial_data The initial data.
		 * @param Controller $controller   The controller object.
		 *
		 * @return array
		 */
		return (array) apply_filters( 'tribe_events_onboarding_wizard_initial_data', $initial_data, $this );
	}

	/**
	 * Render the onboarding wizard trigger.
	 * To show a button, use code similar to below.
	 *
	 * $button = get_submit_button(
	 *     esc_html__( 'Open Install Wizard (current)', 'the-events-calendar' ),
	 *     'secondary tec-events-onboarding-wizard',
	 *     'open',
	 *     true,
	 *     [
	 *         'id'                     => 'tec-events-onboarding-wizard',
	 *         'data-container-element' => ,
	 *         'data-wizard-boot-data'  => wp_json_encode( $this->get_initial_data() ),
	 *     ]
	 * );
	 *
	 * @since 6.8.4
	 *
	 * @return void
	 */
	public function tec_onboarding_wizard_target(): void {
		if ( ! $this->should_show_wizard() ) {
			return;
		}
		?>
		<span
			id="tec-events-onboarding-wizard"
			data-container-element="tec-events-onboarding-wizard-target"
			data-wizard-boot-data="<?php echo esc_attr( wp_json_encode( $this->get_initial_data() ) ); ?>"
		></span>
		<div class="wrap" id="tec-events-onboarding-wizard-target"></div>
		<?php
	}

	/**
	 * Check if the wizard should be displayed.
	 *
	 * @since 6.13.0
	 *
	 * @return bool
	 */
	protected function should_show_wizard(): bool {
		/**
		 * Allow users to force-ignore the checks and display the wizard.
		 *
		 * @since 6.13.0
		 *
		 * @param bool $force Whether to force the wizard to display.
		 *
		 * @return bool
		 */
		$force = apply_filters( 'tec_events_onboarding_wizard_force_display', false );

		if ( $force ) {
			return true;
		}

		$tec_versions = (array) tribe_get_option( 'previous_ecp_versions', [] );
		// If there is more than one previous version, don't show the wizard.
		if ( count( $tec_versions ) > 1 ) {
			return false;
		}

		$data = tribe( Data::class );
		// Don't display if we've finished the wizard.
		if ( $data->get_wizard_setting( 'finished', false ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Register the assets for the landing page.
	 *
	 * @since 6.8.4
	 *
	 * @return void
	 */
	public function register_assets(): void {
		Asset::add(
			'tec-events-onboarding-wizard-script',
			'wizard.js'
		)
			->add_to_group_path( TEC::class . '-packages' )
			->add_to_group( 'tec-onboarding' )
			->enqueue_on( 'admin_enqueue_scripts' )
			->set_condition( [ __CLASS__, 'is_on_page' ] )
			->in_footer()
			->register();

		Asset::add(
			'tec-events-onboarding-wizard-style',
			'wizard.css'
		)
			->add_to_group_path( TEC::class . '-packages' )
			->add_to_group( 'tec-onboarding' )
			->enqueue_on( 'admin_enqueue_scripts' )
			->set_condition( [ __CLASS__, 'is_on_page' ] )
			->set_dependencies( 'wp-components' )
			->use_asset_file( false ) // Do not use the asset file: it would use the JS file one.
			->register();

		// Set the webpack public path for dynamic asset loading.
		// This ensures that webpack can correctly resolve asset URLs (images, fonts, etc.)
		// regardless of the WordPress install location or folder structure.
		$public_url    = trailingslashit( plugins_url( 'build/', TEC::instance()->plugin_file ) );
		$inline_script = sprintf( 'window.tecWebpackPublicPath = %s;', wp_json_encode( $public_url ) );
		wp_add_inline_script( 'tec-events-onboarding-wizard-script', $inline_script, 'before' );
	}
}
