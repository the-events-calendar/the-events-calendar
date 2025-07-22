<?php
/**
 * Service Provider for Filter Bar upsell/settings.
 *
 * @since
 *
 * @package TEC\Events\Admin\Settings
 */

namespace TEC\Events\Admin\Settings;

use TEC\Common\Contracts\Service_Provider;
use Tribe__Settings_Tab;
use Tribe__Template;
use Tribe\Events\Admin\Settings;

/**
 * Class Upsell
 *
 * @since 6.7.0
 */
class Filter_Bar_Upsell extends Service_Provider {

	/**
	 * The slug of the upsell tab.
	 *
	 * @since 6.7.0
	 *
	 * @var string
	 */
	protected string $slug = 'filter-view';

	/**
	 * Stores the instance of the template engine that we will use for rendering the elements.
	 *
	 * @since 6.7.0
	 *
	 * @var Tribe__Template
	 */
	protected $template;

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 6.7.0
	 */
	public function register(): void {
		if ( tec_should_hide_upsell() ) {
			return;
		}

		// Bail if Filter Bar is already installed/registered.
		if ( has_action( 'tribe_common_loaded', 'tribe_register_filterbar' ) ) {
			return;
		}

		$this->add_actions();
		$this->add_filters();
	}

	/**
	 * Add actions.
	 *
	 * @since 6.7.0
	 */
	public function add_actions(): void {
		add_action( 'tribe_settings_do_tabs', [ $this, 'add_tab' ] );
	}

	/**
	 * Add filters.
	 *
	 * @since 6.7.0
	 */
	public function add_filters(): void {
		add_filter( 'tribe_settings_form_class', [ $this, 'filter_tribe_settings_form_classes' ], 10, 3 );
		add_filter( 'tribe_settings_no_save_tabs', [ $this, 'filter_tribe_settings_no_save_tabs' ] );
	}

	/**
	 * Filters the classes for the settings form.
	 *
	 * @since 6.7.0
	 *
	 * @param array               $classes    The classes for the settings form.
	 * @param string              $admin_page The current admin page.
	 * @param Tribe__Settings_Tab $tab_object The current tab object.
	 *
	 * @return array The modified classes for the settings form.
	 */
	public function filter_tribe_settings_form_classes( $classes, $admin_page, $tab_object ): array {
		if ( ! in_array( "tec-settings-form__{$this->slug}-tab--active", $classes ) ) {
			return $classes;
		}

		if ( ! $tab_object ) {
			return $classes;
		}

		if ( $tab_object->id !== $this->slug ) {
			return $classes;
		}

		$classes[] = 'tec-events-settings__upsell-form';

		return $classes;
	}

	/**
	 * Adds the Filter Bar Upsell to the tabs that should not be saved.
	 *
	 * @since 6.7.0
	 *
	 * @param array $tabs The tabs that should not use the save footer.
	 *
	 * @return array The modified tabs that should not use the save footer.
	 */
	public function filter_tribe_settings_no_save_tabs( $tabs ): array {
		$tabs[] = $this->slug;

		return $tabs;
	}

	/**
	 * Create a Filter Bar upsell tab.
	 *
	 * @since 6.7.0
	 *
	 * @param string $admin_page The current admin page.
	 */
	public function add_tab( $admin_page ): void {
		$tec_settings_page_id = tribe( Settings::class )::$settings_page_id;

		if ( ! empty( $admin_page ) && $tec_settings_page_id !== $admin_page ) {
			return;
		}

		$tec_events_filter_bar_upsell_tab = [
			'filter_bar-upsell-info-box' => [
				'type' => 'html',
				'html' => $this->get_upsell_html(),
			],
		];

		/**
		* Allows the fields displayed in the Filter Bar upsell tab to be modified.
		*
		* @since 6.7.0
		*
		* @param array $tec_events_filter_bar_upsell_tab Array of fields used to setup the Filter Bar upsell Tab.
		*/
		$tec_events_admin_filter_bar_upsell_fields = apply_filters(
			'tec_events_settings_filterbar_tab_content',
			$tec_events_filter_bar_upsell_tab
		);

		new Tribe__Settings_Tab(
			$this->slug,
			esc_html_x( 'Filters', 'Label for the Filters tab.', 'the-events-calendar' ),
			[
				'priority'      => 40,
				'fields'        => $tec_events_admin_filter_bar_upsell_fields,
				'network_admin' => is_network_admin(),
				'show_save'     => false,
			]
		);

		add_filter(
			'tec_events_settings_tabs_ids',
			function ( $tabs ) {
				$tabs[] = $this->slug;
				return $tabs;
			}
		);
	}

	/**
	 * Returns html of the Filter Bar upsell banner.
	 *
	 * @since 6.7.0
	 *
	 * @param array   $context Context of template.
	 * @param boolean $echo    Whether or not to output the HTML or just return it.
	 *
	 * @return string|false HTML of the Filter Bar upsell banner. False if the template is not found.
	 */
	public function get_upsell_html( $context = [], $echo = false ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.echoFound
		return $this->get_template()->template( $this->slug, wp_parse_args( $context ), $echo );
	}

	/**
	 * Gets the template instance used to setup the rendering html.
	 *
	 * @since 6.7.0
	 *
	 * @return Tribe__Template
	 */
	public function get_template(): Tribe__Template {
		if ( empty( $this->template ) ) {
			$this->template = new Tribe__Template();
			$this->template->set_template_origin( \Tribe__Events__Main::instance() );
			$this->template->set_template_folder( 'src/admin-views/settings/upsells/' );
			$this->template->set_template_context_extract( true );
			$this->template->set_template_folder_lookup( false );
		}

		return $this->template;
	}
}
